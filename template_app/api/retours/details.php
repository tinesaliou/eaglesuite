<?php
require_once __DIR__ . "/../../config/db.php";

if (!isset($_GET['id'])) {
    die("ID retour manquant");
}

$id = (int) $_GET['id'];

// 🔹 Retour principal (client OU fournisseur)
$stmt = $conn->prepare("
    SELECT r.*, 
           c.nom AS client_nom, 
           f.nom AS fournisseur_nom, 
           d.nom AS depot_nom, 
           u.nom AS utilisateur_nom
    FROM retours r
    LEFT JOIN clients c ON r.client_id = c.idClient
    LEFT JOIN fournisseurs f ON r.fournisseur_id = f.id
    LEFT JOIN depots d ON r.depot_id = d.id
    LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$retour = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$retour) {
    die("Retour introuvable");
}

// 🔹 Détails produits
$stmt = $conn->prepare("
    SELECT rd.*, p.nom AS produit_nom, p.reference
    FROM retours_details rd
    LEFT JOIN produits p ON rd.produit_id = p.id
    WHERE rd.retour_id = ?
");
$stmt->execute([$id]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fa fa-undo"></i> Détails du retour #<?= $retour['id'] ?></h4>
        <a href="/eaglesuite/index.php?page=retours" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <!-- Infos principales -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <?php if (!empty($retour['client_nom'])): ?>
                <p><strong>Client :</strong> <?= htmlspecialchars($retour['client_nom']) ?></p>
            <?php elseif (!empty($retour['fournisseur_nom'])): ?>
                <p><strong>Fournisseur :</strong> <?= htmlspecialchars($retour['fournisseur_nom']) ?></p>
            <?php endif; ?>

            <p><strong>Dépôt :</strong> <?= htmlspecialchars($retour['depot_nom']) ?></p>
            <p><strong>Date retour :</strong> <?= date("d/m/Y H:i", strtotime($retour['date_retour'])) ?></p>
            <p><strong>Utilisateur :</strong> <?= htmlspecialchars($retour['utilisateur_nom'] ?? '-') ?></p>
            <p><strong>Raison :</strong> <?= nl2br(htmlspecialchars($retour['raison'])) ?></p>
        </div>
    </div>

    <!-- Détails produits -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-4">Produits retournés</h5>
            <table id="detailsTable" class="table table-bordered table-striped">
               <thead class="table-dark">
                    <tr>
                        <th>Produit</th>
                        <th>Référence</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['produit_nom']) ?></td>
                            <td><?= htmlspecialchars($d['reference']) ?></td>
                            <td><?= (int)$d['quantite'] ?></td>
                            <td><?= number_format($d['prix_unitaire'], 2, ',', ' ') ?> FCFA</td>
                            <td><?= number_format($d['quantite'] * $d['prix_unitaire'], 2, ',', ' ') ?> FCFA</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total</th>
                        <th>
                            <?= number_format(array_sum(array_map(fn($d) => $d['quantite'] * $d['prix_unitaire'], $details)), 2, ',', ' ') ?> FCFA
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
$('#modalDetails').on('hidden.bs.modal', function(){
    if ($.fn.DataTable.isDataTable('#detailsTable')) {
        $('#detailsTable').DataTable().destroy();
    }
    $("#detailsContent").html(""); // reset contenu
});
</script>
