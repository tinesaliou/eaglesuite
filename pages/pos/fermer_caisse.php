<?php
require_once __DIR__ . "/../../config/db.php";
//session_start();

/* Session ouverte */
$stmt = $conn->prepare("
    SELECT sc.id
    FROM sessions_caisse sc
    WHERE sc.utilisateur_id = ? AND sc.statut = 'ouverte'
");
$stmt->execute([$_SESSION['user_id']]);
$session = $stmt->fetch();

if (!$session) {
    die("<div class='alert alert-danger'>❌ Aucune caisse ouverte</div>");
}

/* Soldes théoriques */
$types = $conn->query("
    SELECT 
        ct.id,
        tc.libelle,
        ct.solde_actuel
    FROM caisse_types ct
    JOIN types_caisse tc ON tc.id = ct.type_caisse_id
")->fetchAll(PDO::FETCH_ASSOC);

if (!empty($_SESSION['cart'])) {
    die("Impossible de clôturer avec un panier actif");
}

?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"> Clôture de caisse</h5>
        </div>

        <form method="POST" action="/eaglesuite/pages/pos/ajax/cloturer_caisse.php">
            <div class="card-body">

                <table class="table table-bordered align-middle" id="tableCloture">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Type</th>
                            <th>Solde théorique</th>
                            <th>Solde réel</th>
                            <th>Écart</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($types as $t): ?>
                        <tr data-id="<?= $t['id'] ?>">
                            <td class="fw-bold"><?= htmlspecialchars($t['libelle'], ENT_QUOTES, 'UTF-8') ?></td>

                            <td class="text-end theorique"
                                data-value="<?= $t['solde_actuel'] ?>">
                               <?= number_format($t['solde_actuel'], 2, ',', ' ') ?> FCFA
                            </td>

                            <td>
                                <input type="number"
                                    step="0.01"
                                    class="form-control text-end solde-reel"
                                    name="reel[<?= $t['id'] ?>]"
                                    required>
                            </td>

                            <td class="text-end ecart fw-bold">
                                0 FCFA
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>

                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total</td>
                            <td class="text-end" id="totalTheorique">0 FCFA</td>
                            <td class="text-end" id="totalReel">0 FCFA</td>
                            <td class="text-end" id="totalEcart">0 FCFA</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="mt-3">
                    <label class="fw-bold">Commentaire de clôture</label>
                    <textarea name="commentaire"
                            class="form-control"
                            rows="3"
                            placeholder="Ex : Manque de caisse dû à erreur de rendu monnaie"></textarea>
                </div>

            </div>

            <div class="card-footer text-end">
                <button class="btn btn-danger">
                     Clôturer la caisse
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function formatFCFA(n) {
    return new Intl.NumberFormat('fr-FR').format(n) + ' FCFA';
}

function recalculer() {
    let totalTheorique = 0;
    let totalReel = 0;
    let totalEcart = 0;

    document.querySelectorAll('#tableCloture tbody tr').forEach(row => {
        const theorique = parseFloat(
            row.querySelector('.theorique').dataset.value
        );

        const inputReel = row.querySelector('.solde-reel');
        const reel = parseFloat(inputReel.value || 0);

        const ecart = reel - theorique;

        row.querySelector('.ecart').textContent = formatFCFA(ecart);

        row.classList.remove('table-danger', 'table-success');

        if (ecart !== 0) {
            row.classList.add('table-danger');
        } else {
            row.classList.add('table-success');
        }

        totalTheorique += theorique;
        totalReel += reel;
        totalEcart += ecart;
    });

    document.getElementById('totalTheorique').textContent = formatFCFA(totalTheorique);
    document.getElementById('totalReel').textContent = formatFCFA(totalReel);
    document.getElementById('totalEcart').textContent = formatFCFA(totalEcart);
}

document.querySelectorAll('.solde-reel').forEach(input => {
    input.addEventListener('input', recalculer);
});

recalculer();
</script>
