<?php
include_once('../config/db.php');
require_once __DIR__ . '/../config/check_access.php';

$fournisseur_id = $_GET['id'];

$stmtF = $conn->prepare("SELECT * FROM fournisseurs WHERE id = ?");
$stmtF->execute([$fournisseur_id]);
$fournisseur = $stmtF->fetch();

$stmtAchats = $conn->prepare("SELECT * FROM achats WHERE fournisseur_id = ? ORDER BY date_achat DESC");
$stmtAchats->execute([$fournisseur_id]);
$achats = $stmtAchats->fetchAll();
?>

<h3>Historique des achats - <?= htmlspecialchars($fournisseur['nom'], ENT_QUOTES, 'UTF-8') ?></h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Date</th>
            <th>Total</th>
            <th>Mode de paiement</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($achats as $achat): ?>
        <tr>
            <td><?= $achat['date_achat'] ?></td>
            <td><?= number_format($achat['total'], 0, ',', ' ') ?> F CFA</td>
            <td><?= $achat['mode_paiement'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
