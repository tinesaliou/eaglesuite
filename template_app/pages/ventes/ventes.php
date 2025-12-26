<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';
/* require_once __DIR__ . "/../../includes/check_auth.php";

requirePermission("ventes.view"); */

// Récupérer les ventes (sans joindre ventes_details)
$ventes = $conn->query("
    SELECT v.*, c.nom AS client_nom, d.symbole,
    (v.reste_a_payer / v.taux_change) AS reste_a_payer_devise,
    (v.montant_verse / v.taux_change) AS montant_verse_devise,
    (v.taxe / v.taux_change) AS taxe_devise,
    (v.remise / v.taux_change) AS remise_devise
    FROM ventes v
    JOIN devises d ON v.devise_id = d.id
    JOIN clients c ON v.client_id = c.idClient
    ORDER BY v.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$title = "Ventes";
//include __DIR__ . '/../../includes/layout.php';
?>

<div class="container-fluid ">
    <h1 class="mt-4">Ventes</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/{{TENANT_DIR}}/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Ventes</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-shopping-cart"></i> Liste des ventes</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ajouterVenteModal">
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
            <table id="ventesTable" class="table table-bordered table-striped datatable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventes as $vente): ?>
                        <tr>
                            <td><?= $vente['id'] ?></td>
                            <td><?= htmlspecialchars($vente['client_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $vente['date_vente'] ?></td>
                            <td><?= number_format($vente['montant_devise'], 2, ',', ' ') ?> <?= $vente['symbole'] ?></td>
                            <td>
                                <?php if ($vente['annule'] == 1): ?>
                                    <span class="badge bg-danger">Annulée</span>
                                <?php elseif ($vente['reste_a_payer'] > 0): ?>
                                    <span class="badge bg-warning text-dark">Crédit</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Payée</span>
                                <?php endif; ?>
                            </td>
                            <td>
                               <?php if ($vente['annule'] !== 1): ?>
                                <button class="btn btn-danger btn-sm btnAnnulerVente"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAnnulerVente"
                                        data-id="<?= $vente['id'] ?>">
                                    <i class="fa fa-ban"></i>
                                </button>
                                <button class="btn btn-info btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#visualiserVenteModal<?= $vente['id'] ?>">
                                    <i class="fa fa-eye"></i>
                                </button>

                            <div class="dropdown d-inline">
                                <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-print"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item printA4" href="#" data-id="<?= $vente['id'] ?>">
                                            🧾 Facture
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item printTicket" href="#" data-id="<?= $vente['id'] ?>">
                                            🎟️ Ticket
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div> 
    </div> 
</div> 

<?php include __DIR__ . "/modal_ajouter_vente.php"; ?>
<?php foreach ($ventes as $vente): ?>
    <?php
    // Charger les détails de la vente (produits, dépôts, etc.)
    $details = $conn->query("
        SELECT 
    vd.id,
    p.nom AS produit,
    d.nom AS depot,
    vd.quantite,
    vd.prix_unitaire AS prix_cfa,
    (vd.prix_unitaire / v.taux_change) AS prix_devise,
    (vd.quantite * vd.prix_unitaire) AS montant_cfa,
    ((vd.quantite * vd.prix_unitaire) / v.taux_change) AS total_devise,
    (v.totalHT / v.taux_change) AS totalHT_devise,
    (v.totalTTC / v.taux_change) AS totalTTC_devise,
    (v.taxe / v.taux_change) AS taxe_devise,
    v.devise_id,
    v.taux_change
FROM ventes_details vd
JOIN produits p ON p.id = vd.produit_id
JOIN depots d ON d.id = vd.depot_id
JOIN ventes v ON v.id = vd.vente_id
WHERE vd.vente_id = " . intval($vente['id'])
    )->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <?php include __DIR__ . "/modal_visualiser_vente.php"; ?>
      <?php include __DIR__ . "/modal_annuler_vente.php"; ?>
<?php endforeach; ?>


<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function() {
    var table = $('#ventesTable').DataTable({
        responsive: true,
        language: {
            url: "/{{TENANT_DIR}}/public/js/fr-FR.json"
        }
    });

    //  Filtre statut
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
document.addEventListener("DOMContentLoaded", function() {
    const annulerButtons = document.querySelectorAll(".btnAnnulerVente");
    const inputId = document.getElementById("annulerVenteId");

    annulerButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            inputId.value = this.dataset.id;
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  
  // Attacher un écouteur d'événement à tous les boutons avec la classe 'printA4'
  document.querySelectorAll('.printA4').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const venteId = this.getAttribute('data-id');
      const url = `pages/rapports/utils/impression.php?cat=ventes&type=facture_client&ticket=a4&id=${venteId}`;
      window.open(url, '_blank');
    });
  });
  
  // Attacher un écouteur d'événement à tous les boutons avec la classe 'printTicket'
  document.querySelectorAll('.printTicket').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const venteId = this.getAttribute('data-id');
      const url = `pages/rapports/utils/impression.php?cat=ventes&type=facture_client&ticket=ticket&id=${venteId}`;

      const iframe = document.createElement('iframe');
      iframe.style.display = 'none';
      iframe.src = url;
      document.body.appendChild(iframe);

      iframe.onload = function() {
        setTimeout(() => {
          iframe.contentWindow.focus();
          iframe.contentWindow.print();
        }, 100);
      };
    });
  });
});
</script>


