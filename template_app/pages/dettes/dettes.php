<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';

// Récupérer les ventes (sans joindre ventes_details)
$dettes = $conn->query("
    SELECT df.*,f.nom AS fournisseur_nom,
    (df.montant_total / a.taux_change) AS montant_total_devise,
	(df.montant_paye / a.taux_change) AS montant_paye_devise,
	(df.reste_a_payer / a.taux_change) AS reste_a_payer_devise,
	a.devise_id,
	d.symbole,
	a.taux_change
    FROM dettes_fournisseurs df
    JOIN achats a ON a.id = df.achat_id
    JOIN devises d ON a.devise_id = d.id
    JOIN fournisseurs f ON df.fournisseur_id = f.id
    ORDER BY df.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$title = "Dettes Fournisseurs";
//include __DIR__ . '/../../includes/layout.php';
?>

<div class="container-fluid ">
    <h1 class="mt-4">Dettes  Fournisseurs</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/{{TENANT_DIR}}/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Dettes Fournisseurs</li>
    </ol>

  <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-file-invoice-dollar"></i> Liste des dettes fournisseurs</span>
        </div>

      <div class="card-body">

            <table id="detteTable" class="table table-bordered table-striped datatable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fournisseur</th>
                        <th>Date</th>
                        <th>Montant Total</th>
                        <th>Montant Restant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dettes as $dette): ?>
                        <?php if ($dette['statut'] !== 'Soldé'): ?>
                    <tr>
                        <td><?= $dette['id'] ?></td>
                        <td><?= htmlspecialchars($dette['fournisseur_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $dette['date_creation'] ?></td>
                        <td><?= number_format($dette['montant_total_devise'], 2, ',', ' ') ?> <?= $dette['symbole'] ?></td>
                        <td><?= number_format($dette['reste_a_payer_devise'], 2, ',', ' ') ?> <?= $dette['symbole'] ?></td>
                        <td><?= $dette['statut'] ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#view<?= $dette['id'] ?>">
                                <i class="fa fa-eye"></i>
                            </button>


                           
                                <button class="btn btn-success btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#solderDette<?= $dette['id'] ?>">
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

<?php include __DIR__ . "/modal_visualiser_dette.php"; ?>
<?php include __DIR__ . "/modal_solder_dette.php"; ?>


<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function() {
    var table = $('#creanceTable').DataTable({
        responsive: true,
        language: {
            url: "/{{TENANT_DIR}}/public/js/fr-FR.json"
        }
    });
});


$("#formSolderCreance").submit(function(e) {
    e.preventDefault();
    $.post("/{{TENANT_DIR}}/api/dettes/solder_dette.php", $(this).serialize(), function(res) {
        let data = JSON.parse(res);
        if (data.success) {
            alert(" Dette mise à jour avec succès !");
            location.reload();
        } else {
            alert("❌ " + data.message);
        }
    });
});

</script>

