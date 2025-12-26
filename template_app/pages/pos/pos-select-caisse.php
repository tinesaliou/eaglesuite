<?php
require_once __DIR__ . "/../../config/db.php";

$caisses = $conn->query("
    SELECT id, nom
    FROM caisses
    WHERE actif = 1
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <div class="card shadow mx-auto" style="max-width:400px">
        <div class="card-header bg-primary text-white text-center">
            <h5>Sélection de caisse</h5>
        </div>

        <form method="POST" action="/{{TENANT_DIR}}/index.php?page=pos-pin">
            <div class="card-body">

                <select name="caisse_id" class="form-select" required>
                    <option value="">— Choisir une caisse —</option>
                    <?php foreach ($caisses as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </div>

            <div class="card-footer text-end">
                <button class="btn btn-primary w-100">
                    Continuer →
                </button>
            </div>
        </form>
    </div>
</div>
