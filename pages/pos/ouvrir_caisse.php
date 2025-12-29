<?php
require_once __DIR__ . "/../../config/db.php";
//session_start();

/* Vérifier qu’aucune caisse n’est ouverte */
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM sessions_caisse 
    WHERE utilisateur_id = ? AND statut = 'ouverte'
");
$stmt->execute([$_SESSION['user']['id']]);
if ($stmt->fetchColumn() > 0) {
    die("<div class='alert alert-warning'>⚠️ Une caisse est déjà ouverte</div>");
}

/* Caisses */
$caisses = $conn->query("
    SELECT id, nom 
    FROM caisses 
    WHERE actif = 1
")->fetchAll(PDO::FETCH_ASSOC);

/* Types de caisse par caisse */
$types = $conn->query("
    SELECT 
        ct.id,
        tc.libelle,
        c.nom AS caisse
    FROM caisse_types ct
    JOIN types_caisse tc ON tc.id = ct.type_caisse_id
    JOIN caisses c ON c.id = ct.caisse_id
    WHERE c.actif = 1
    ORDER BY c.nom
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">🟢 Ouverture de caisse</h5>
        </div>

        <form method="POST" action="/eaglesuite/pages/caisse/ouvrir_caisse.php">
            <div class="card-body">

                <div class="mb-3">
                    <label class="fw-bold">Caisse</label>
                    <select name="caisse_id" class="form-select" required>
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($caisses as $c): ?>
                            <option value="<?= $c['id'] ?>">
                                <?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <hr>

                <h6 class="fw-bold mb-3">Soldes initiaux par type</h6>

                <?php foreach ($types as $t): ?>
                    <div class="mb-2">
                        <label>
                            <?= htmlspecialchars($t['caisse'], ENT_QUOTES, 'UTF-8') ?> – 
                            <?= htmlspecialchars($t['libelle'], ENT_QUOTES, 'UTF-8') ?>
                        </label>
                        <input type="number"
                               step="0.01"
                               name="solde[<?= $t['id'] ?>]"
                               class="form-control"
                               value="0"
                               required>
                    </div>
                <?php endforeach; ?>

            </div>

            <div class="card-footer text-end">
                <button class="btn btn-success">
                    Ouvrir la caisse
                </button>
            </div>
        </form>
    </div>
</div>
