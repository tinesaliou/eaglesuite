<?php
require_once __DIR__ . "/../../config/db.php";

// Récupérer les ventes (sans joindre ventes_details)
$creances = $conn->query("
    SELECT cc.*,c.nom AS client_nom,
	(cc.montant_total / v.taux_change) AS montant_total_devise,
	(cc.montant_paye / v.taux_change) AS montant_paye_devise,
	(cc.reste_a_payer / v.taux_change) AS reste_a_payer_devise,
	v.devise_id,
	d.symbole AS symbole,
	v.taux_change
    FROM creances_clients cc
    JOIN ventes v ON v.id = cc.vente_id
    JOIN devises d ON v.devise_id = d.id
    JOIN clients c ON cc.client_id = c.idClient
    ORDER BY cc.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$title = "Créances Clients";
//include __DIR__ . '/../../includes/layout.php';
?>

<div class="container-fluid ">
    <h1 class="mt-4">Créances Clients</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Créances Clients</li>
    </ol>

  <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-hand-holding-usd"></i> Liste des créances clients</span>
        </div>

      <div class="card-body">

            <table id="creanceTable" class="table table-bordered table-striped datatable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Montant Total</th>
                        <th>Montant Restant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($creances as $creance): ?>
                         <?php if ($creance['statut'] !== 'Soldé'): ?>
                    <tr>
                        <td><?= $creance['id'] ?></td>
                        <td><?= htmlspecialchars($creance['client_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $creance['date_creation'] ?></td>
                        <td><?= number_format($creance['montant_total_devise'], 2, ',', ' ') ?> <?= $creance['symbole'] ?></td>
                        <td><?= number_format($creance['reste_a_payer_devise'], 2, ',', ' ') ?> <?= $creance['symbole'] ?></td>
                        <td><?= $creance['statut'] ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#view<?= $creance['id'] ?>">
                                <i class="fa fa-eye"></i>
                            </button>

                                <button class="btn btn-success btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#solder<?= $creance['id'] ?>">
                                    <i class="fa fa-cash-register"></i>
                                </button>
                        </td>
                       
                    </tr>
                      <?php endif; ?>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div> 
    </div> 
</div> 

<?php include __DIR__ . "/modal_visualiser_creance.php"; ?>
<?php include __DIR__ . "/modal_solder_creance.php"; ?>


<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function() {
    var table = $('#creanceTable').DataTable({
        responsive: true,
        language: {
            url: "/eaglesuite/public/js/fr-FR.json"
        }
    });
});


$("#formSolderCreance").submit(function(e) {
    e.preventDefault();
    $.post("/eaglesuite/api/creances/solder_creance.php", $(this).serialize(), function(res) {
        let data = JSON.parse(res);
        if (data.success) {
            alert(" Créance mise à jour avec succès !");
            location.reload();
        } else {
            alert("❌ " + data.message);
        }
    });
});

</script>

