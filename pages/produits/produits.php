<?php
// Connexion DB
require_once __DIR__ . '/../../config/db.php';

require_once __DIR__ . "/../../includes/check_auth.php";
requirePermission("produits.view");

$stmt = $conn->query("
    SELECT p.*,
           c.nom AS categorie,c.id AS categorie_id, d.nom AS depot, d.id AS depot_id, u.nom AS unite, u.id AS unite_id,
           COALESCE(SUM(sd.quantite), 0) AS quantite
    FROM produits p
    LEFT JOIN stock_depot sd ON p.id= sd.produit_id
    LEFT JOIN categories c ON p.categorie_id = c.id
    LEFT JOIN depots d ON p.depot_id = d.id
    LEFT JOIN unites u ON p.unite_id = u.id
    GROUP BY 
        p.id, p.nom, p.reference, p.prix_achat, p.prix_vente, p.stock_total,
        p.seuil_alerte, p.image, p.created_at, c.nom, u.nom
    ORDER BY p.id DESC 
    
");
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Produits";
?>

<div class="container-fluid px-2">
    <h1 class="mt-4">Produits</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Produits</li>
    </ol>

    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-boxes"></i> Liste des produits</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterProduit">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        </div>
        <div class="card-body">
            <table id="tableProduits" class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Image</th>
                        <th>Nom / Réf</th>
                        <th>Catégorie</th>
                        <th>Prix Achat</th>
                        <th>Prix Vente</th>
                        <th>Stock</th>
                        <th>Unité</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $p): ?>
                        <tr>
                            <td class="text-center">
                                <?php if ($p['image']): ?>
                                    <img src="/eaglesuite/public/uploads/produits/<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>" 
                                         alt="<?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?>" 
                                         class="img-thumbnail" style="width:50px; height:50px; object-fit:cover;">
                                <?php else: ?>
                                    <span class="badge bg-secondary">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <small class="text-muted">Réf: <?= htmlspecialchars($p['reference'], ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><?= htmlspecialchars($p['categorie'], ENT_QUOTES, 'UTF-8' ?? 'Non défini') ?></td>
                            <td><i class="fas fa-arrow-down text-success"></i> <?= number_format($p['prix_achat'], 0, ',', ' ') ?> CFA</td>
                            <td><i class="fas fa-arrow-up text-danger"></i> <?= number_format($p['prix_vente'], 0, ',', ' ') ?> CFA</td>
                            <td>
                                <?php if ($p['quantite'] <= $p['seuil_alerte']): ?>
                                    <span class="badge bg-danger"><?= (int)$p['quantite'] ?> (Alerte)</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><?= (int)$p['quantite'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['unite'], ENT_QUOTES, 'UTF-8' ?? '-') ?></td>
                            <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                            <td class="text-center">
                                <button class="btn btn-info btn-sm btnDetailProduit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetailProduit"
                                        data-id="<?= $p['id'] ?>"
                                        data-nom="<?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-reference="<?= htmlspecialchars($p['reference'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-prixachat="<?= $p['prix_achat'] ?>"
                                        data-prixvente="<?= $p['prix_vente'] ?>"
                                        data-stocktotal="<?= $p['quantite'] ?>"
                                        data-categorie="<?= $p['categorie'] ?>"
                                        data-depot="<?= $p['depot'] ?>"
                                        data-unite="<?= $p['unite'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <button class="btn btn-warning btn-sm btnEditProduit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalModifierProduit"
                                    data-id="<?= $p['id'] ?>"
                                    data-nom="<?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-reference="<?= htmlspecialchars($p['reference'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-description="<?= htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8' ?? '') ?>"
                                    data-prixachat="<?= $p['prix_achat'] ?>"
                                    data-prixvente="<?= $p['prix_vente'] ?>"
                                    data-stocktotal="<?= $p['quantite'] ?>"
                                    data-seuilalerte="<?= $p['seuil_alerte'] ?>"
                                    data-categorieid="<?= $p['categorie_id'] ?>"
                                    data-depotid="<?= $p['depot_id'] ?>"
                                    data-uniteid="<?= $p['unite_id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                               <button class="btn btn-danger btn-sm btnSupprimerProduit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalSupprimerProduit"
                                        data-id="<?= $p['id'] ?>"
                                        data-nom="<?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="fas fa-trash"></i>
                                </button>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . "/modal_ajouter_produit.php"; ?>
<?php include __DIR__ . "/modal_modifier_produit.php"; ?>
<?php include __DIR__ . "/modal_supprimer_produit.php"; ?>
<?php include __DIR__ . "/modal_details_produit.php"; ?>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>

<script>
$(document).ready(function() {
    $('#tableProduits').DataTable({
        responsive: true,
        language: { url: "/eaglesuite/public/js/fr-FR.json" }
    });
});

$(document).on("click", ".btnDetailProduit", function () {
    $("#detail_nom").text($(this).data("nom"));
    $("#detail_reference").text($(this).data("reference"));
    $("#detail_prix_achat").text($(this).data("prixachat"));
    $("#detail_prix_vente").text($(this).data("prixvente"));
    $("#detail_stock_total").text($(this).data("stocktotal"));
    $("#detail_categorie").text($(this).data("categorie"));
    $("#detail_depot").text($(this).data("depot"));
    $("#detail_unite").text($(this).data("unite"));
});


$(document).on("click", ".btnSupprimerProduit", function () {
    $("#supprimer_id").val($(this).data("id"));
    $("#supprimer_nom").text($(this).data("nom"));
});


</script>
