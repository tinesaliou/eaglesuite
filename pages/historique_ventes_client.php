<?php
include_once('../config/db.php');
$client_id = $_GET['id'];

$stmtClient = $conn->prepare("SELECT * FROM clients WHERE id = ?");
$stmtClient->execute([$client_id]);
$client = $stmtClient->fetch();

$stmtVentes = $conn->prepare("SELECT * FROM ventes WHERE client_id = ? ORDER BY date_vente DESC");
$stmtVentes->execute([$client_id]);
$ventes = $stmtVentes->fetchAll();
?>

<h3>Historique des ventes - <?= htmlspecialchars($client['nom'], ENT_QUOTES, 'UTF-8') ?></h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Date</th>
            <th>Total</th>
            <th>Type</th>
            <th>Mode de paiement</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ventes as $vente): ?>
        <tr>
            <td><?= $vente['date_vente'] ?></td>
            <td><?= number_format($vente['total'], 0, ',', ' ') ?> F CFA</td>
            <td><?= $vente['type_vente'] ?></td>
            <td><?= $vente['mode_paiement'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
