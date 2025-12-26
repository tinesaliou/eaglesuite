<?php
require_once __DIR__ . "/../../config/db.php";

require_once __DIR__ . "/pos_guard.php";
//session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: /{{TENANT_DIR}}/login.php");
    exit;
}

$utilisateur_id = $_SESSION['user_id'];
$caisse_id = (int)($_POST['caisse_id'] ?? 0);

if (!$caisse_id) {
    die("Caisse invalide");
}

/* Vérifier qu'il n'existe pas déjà une session ouverte sur cette caisse */
$stmt = $conn->prepare("
    SELECT id
    FROM sessions_caisse
    WHERE caisse_id = ? AND statut = 'ouverte'
");
$stmt->execute([$caisse_id]);

if ($stmt->fetch()) {
    die("Cette caisse est déjà ouverte");
}

/* Charger le PIN utilisateur */
$stmt = $conn->prepare("
    SELECT pos_pin, pos_pin_tentatives, pos_pin_blocage
    FROM utilisateurs
    WHERE id = ?
");
$stmt->execute([$utilisateur_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin'])) {

    /* Blocage sécurité */
    if ($user['pos_pin_blocage'] && strtotime($user['pos_pin_blocage']) > time()) {
        $error = "PIN bloqué temporairement";
    }
    elseif (!$user['pos_pin'] || !password_verify($_POST['pin'], $user['pos_pin'])) {

        $tentatives = $user['pos_pin_tentatives'] + 1;
        $blocage = null;

        if ($tentatives >= 3) {
            $blocage = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $tentatives = 0;
        }

        $stmt = $conn->prepare("
            UPDATE utilisateurs
            SET pos_pin_tentatives = ?, pos_pin_blocage = ?
            WHERE id = ?
        ");
        $stmt->execute([$tentatives, $blocage, $utilisateur_id]);

        $error = "Code PIN incorrect";
    }
    else {
        /* PIN OK → autorisation ouverture caisse */
        $_SESSION['pos_caisse_pending'] = $caisse_id;

        header("Location: /{{TENANT_DIR}}/index.php?page=pos-open");
        exit;
    }
}

?>


<div class="container mt-5">
    <div class="card shadow mx-auto" style="max-width:350px">
        <div class="card-header bg-dark text-white text-center">
            🔐 Code PIN
        </div>

        <form method="POST">
            <input type="hidden" name="caisse_id" value="<?= $caisse_id ?>">

            <div class="card-body text-center">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <input type="password"
                        name="pin"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        class="form-control text-center fs-3"
                        placeholder="••••"
                        maxlength="6"
                        required>
            </div>

            <div class="card-footer">
                <button class="btn btn-success w-100">
                    Valider
                </button>
            </div>
        </form>
    </div>
</div>
