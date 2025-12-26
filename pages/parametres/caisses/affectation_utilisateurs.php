<?php
require_once __DIR__ . "/../../../config/db.php";

/* ==========================
   1. Sécurité & validation
   ========================== */

$caisse_id = isset($_GET['caisse_id']) ? (int)$_GET['caisse_id'] : 0;
if ($caisse_id <= 0) {
    die("Caisse invalide");
}

/* ==========================
   2. Infos caisse
   ========================== */

$stmt = $conn->prepare("SELECT id, nom FROM caisses WHERE id = ?");
$stmt->execute([$caisse_id]);
$caisse = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$caisse) {
    die("Caisse introuvable");
}

/* ==========================
   3. Enregistrement
   ========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $utilisateurs = $_POST['utilisateurs'] ?? [];

    try {
        $conn->beginTransaction();

        // Suppression anciennes affectations
        $stmt = $conn->prepare("DELETE FROM utilisateurs_caisses WHERE caisse_id = ?");
        $stmt->execute([$caisse_id]);

        // Insertion nouvelles
        if (!empty($utilisateurs)) {
            $stmt = $conn->prepare("
                INSERT INTO utilisateurs_caisses (utilisateur_id, caisse_id)
                VALUES (?, ?)
            ");

            foreach ($utilisateurs as $user_id) {
                $stmt->execute([(int)$user_id, $caisse_id]);
            }
        }

        $conn->commit();
        $success = "Affectations mises à jour avec succès";

    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Erreur lors de l'enregistrement";
    }
}

/* ==========================
   4. Liste utilisateurs
   ========================== */

$stmt = $conn->prepare("
    SELECT 
        u.id,
        u.nom,
        u.email,
        u.actif,
        EXISTS (
            SELECT 1 FROM utilisateurs_caisses uc
            WHERE uc.utilisateur_id = u.id
              AND uc.caisse_id = ?
        ) AS affecte
    FROM utilisateurs u
    ORDER BY u.nom
");
$stmt->execute([$caisse_id]);
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>👤 Affectation utilisateurs – Caisse : <?= htmlspecialchars($caisse['nom'], ENT_QUOTES, 'UTF-8') ?></h4>
        <a href="index.php?page=caisses" class="btn btn-secondary btn-sm">
            ← Retour
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="card shadow-sm">
        <div class="card-body">

            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                <tr>
                    <th style="width:50px"></th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Statut</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($utilisateurs as $u): ?>
                    <tr>
                        <td class="text-center">
                            <input type="checkbox"
                                   name="utilisateurs[]"
                                   value="<?= $u['id'] ?>"
                                   <?= $u['affecte'] ? 'checked' : '' ?>
                                   <?= !$u['actif'] ? 'disabled' : '' ?>>
                        </td>
                        <td><?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= $u['actif']
                                ? '<span class="badge bg-success">Actif</span>'
                                : '<span class="badge bg-secondary">Inactif</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>

        </div>

        <div class="card-footer text-end">
            <button class="btn btn-primary">
                💾 Enregistrer les affectations
            </button>
        </div>
    </form>

</div>
