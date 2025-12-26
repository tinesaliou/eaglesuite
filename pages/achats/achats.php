
<?php

require_once __DIR__ . "/../../config/db.php";

// Récupérer les achats (sans joindre achats_details)
$achats = $conn->query("
    SELECT a.*, f.nom AS fournisseur_nom, d.symbole,
    (a.reste_a_payer / a.taux_change) AS reste_a_payer_devise,
    (a.montant_verse / a.taux_change) AS montant_verse_devise,
    (a.taxe / a.taux_change) AS taxe_devise,
    (a.remise / a.taux_change) AS remise_devise
    FROM achats a
    JOIN devises d ON a.devise_id = d.id
    JOIN fournisseurs f ON a.fournisseur_id = f.id
    ORDER BY a.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$title = "Achats";
//include __DIR__ . '/../../includes/layout.php';
?>

<div class="container-fluid px-2">
    <h1 class="mt-4">Achats</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Achats</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-shopping-cart"></i> Liste des achats</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ajouterAchatModal">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        </div>

        <div class="card-body">
            <div class="mb-3">
            <label for="filtreStatut" class="form-label">Filtrer par statut :</label>
            <select id="filtreStatut" class="form-select w-auto d-inline-block">
                <option value="">-- Tous --</option>
                <option value="Payé">Payé</option>
                <option value="Impayé">Impayé</option>
                <option value="Annulée">Annulée</option>
            </select>
            </div>
            <table id="achatsTable" class="table table-bordered table-striped datatable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fournisseur</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($achats as $achat): ?>
                        <tr>
                            <td><?= $achat['id'] ?></td>
                            <td><?= htmlspecialchars($achat['fournisseur_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $achat['date_achat'] ?></td>
                            <td><?= number_format($achat['montant_devise'], 2, ',', ' ') ?> <?= $achat['symbole'] ?></td>
                            <td>
                                <?php if ($achat['annule'] == 1): ?>
                                    <span class="badge bg-danger">Annulée</span>
                                <?php elseif ($achat['reste_a_payer'] > 0): ?>
                                    <span class="badge bg-warning text-dark">Crédit</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Payée</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                 <?php if ($achat['annule'] !== 1): ?>
                                 <button class="btn btn-danger btn-sm btnAnnulerAchat"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAnnulerAchat"
                                        data-id="<?= $achat['id'] ?>">
                                     <i class="fa fa-ban"></i>
                                </button>
                                <button class="btn btn-info btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#visualiserAchatModal<?= $achat['id'] ?>">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <a href="/eaglesuite/api/achats/imprimer.php?id=<?= $achat['id'] ?>" 
                                   target="_blank" 
                                   class="btn btn-success btn-sm">
                                    <i class="fa fa-print"></i>
                                </a>
                                <!--?php else: ?-->
                                <!-- <span class="badge bg-secondary">Annulée</span> -->
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div> 
    </div> 
</div> 

<?php include __DIR__ . "/modal_ajouter_achat.php"; ?>
<?php foreach ($achats as $achat): ?>
    <?php
    // Charger les détails de l’achat (produits, dépôts, etc.)
    $details = $conn->query("
    SELECT 
    ad.id,
    p.nom AS produit,
    d.nom AS depot,
    ad.quantite,
    ad.prix_unitaire AS prix_cfa,
    (ad.prix_unitaire / a.taux_change) AS prix_devise,
    (ad.quantite * ad.prix_unitaire) AS montant_cfa,
    ((ad.quantite * ad.prix_unitaire) / a.taux_change) AS total_devise,
    (a.totalHT / a.taux_change) AS totalHT_devise,
    (a.totalTTC / a.taux_change) AS totalTTC_devise,
    (a.taxe / a.taux_change) AS taxe_devise,
    a.devise_id,
    a.taux_change
FROM achats_details ad
JOIN produits p ON p.id = ad.produit_id
JOIN depots d ON d.id = ad.depot_id
JOIN achats a ON a.id = ad.achat_id
WHERE ad.achat_id = " . intval($achat['id'])
    )->fetchAll(PDO::FETCH_ASSOC);
    ?>
<?php include __DIR__ . "/modal_visualiser_achat.php"; ?>
<?php include __DIR__ . "/modal_annuler_achat.php"; ?>
<?php endforeach; ?>


<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function() {
    var table = $('#achatsTable').DataTable({
        responsive: true,
        language: {
            url: "/eaglesuite/public/js/fr-FR.json"
        }
    });

    // Filtre statut
    $('#filtreStatut').on('change', function() {
        var value = $(this).val();
        if (value) {
            table.column(4).search('^' + value + '$', true, false).draw(); 
            // ⚠️ Ici "4" = index de la colonne Statut (à ajuster selon ton tableau)
        } else {
            table.column(4).search('').draw();
        }
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const produitSelect = document.getElementById("produit_id");
    const depotSelect = document.getElementById("depot_id");
    const stockLabel = document.getElementById("stockActuel");

    function chargerStock() {
        let produit_id = produitSelect.value;
        let depot_id = depotSelect.value;

        if (produit_id && depot_id) {
            fetch(`api/get_stock.php?produit_id=${produit_id}&depot_id=${depot_id}`)
                .then(res => res.json())
                .then(data => {
                    stockLabel.textContent = data.message; // "Stock actuel : X"
                })
                .catch(() => {
                    stockLabel.textContent = "Erreur de récupération du stock";
                });
        } else {
            stockLabel.textContent = "Stock actuel : -";
        }
    }

    produitSelect.addEventListener("change", chargerStock);
    depotSelect.addEventListener("change", chargerStock);
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const annulerButtons = document.querySelectorAll(".btnAnnulerAchat");
    const inputId = document.getElementById("annulerAchatId");

    annulerButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            inputId.value = this.dataset.id;
        });
    });
});
</script>
