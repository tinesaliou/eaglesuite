<?php
require_once __DIR__ . "/../../config/db.php";
$title = "Retours";
//include __DIR__ . "/../../includes/layout.php";

$sql = "
    SELECT r.id, r.type, r.date_retour, r.raison, 
       c.nom AS client_nom, f.nom AS fournisseur_nom, 
       d.nom AS depot_nom, u.nom AS utilisateur_nom,
       COUNT(rd.id) AS nb_produits
FROM retours r
LEFT JOIN clients c ON r.client_id = c.idClient
LEFT JOIN fournisseurs f ON r.fournisseur_id = f.id
LEFT JOIN depots d ON r.depot_id = d.id
LEFT JOIN utilisateurs u ON r.utilisateur_id = u.id
LEFT JOIN retours_details rd ON r.id = rd.retour_id
GROUP BY r.id
ORDER BY r.date_retour DESC
";
$retours = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h1 class="mb-4">Retours</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Retours</li>
    </ol>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-undo"></i> Liste des retours</span>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajouterRetourModal">
                <i class="fa fa-plus"></i> Ajouter
            </button>
        </div>
        <div class="card-body">
            <table id="retoursTable" class="table table-bordered table-striped datatable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Dépôt</th>
                        
                        <th>Produits</th>
                        <th>Raison</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($retours as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($r['date_retour'])) ?></td>
                        <td>
                        <?php if($r['type'] === 'client'): ?>
                            Client: <?= htmlspecialchars($r['client_nom'], ENT_QUOTES, 'UTF-8') ?>
                        <?php else: ?>
                            Fournisseur: <?= htmlspecialchars($r['fournisseur_nom'], ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($r['depot_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                       
                        <td><span class="badge bg-info"><?= $r['nb_produits'] ?> produits</span></td>
                        <td><?= htmlspecialchars($r['raison'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalDetails"
                                    data-id="<?= $r['id'] ?>">
                                <i class="fa fa-eye"></i>
                            </button>
                            <button class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalModifier"
                                    data-id="<?= $r['id'] ?>">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalSupprimer"
                                    data-id="<?= $r['id'] ?>">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . "/modal_ajouter_retour.php"; ?>
<?php include __DIR__ . "/modal_modifier_retour.php"; ?>
<?php include __DIR__ . "/modal_supprimer_retour.php"; ?>
<?php include __DIR__ . "/modal_details_retour.php"; ?>



<?php include __DIR__ . "/../../includes/layout_end.php"; ?>
<script>
$(document).ready(function() {
    $('#retoursTable').DataTable({
        responsive: true,
        language: { url: "/eaglesuite/public/js/fr-FR.json" }
    });

    // TODO: remplir dynamiquement modal Modifier et Détails via AJAX
});

$(document).ready(function(){
    // Remplir modal Supprimer
    $('#modalSupprimer').on('show.bs.modal', function(e){
        let id = $(e.relatedTarget).data('id');
        $('#supprimer_id').val(id);
    });

    // Charger détails
    $('#modalDetails').on('show.bs.modal', function(e){
        let id = $(e.relatedTarget).data('id');
        $("#detailsContent").html("<p class='text-muted'>Chargement...</p>");
        $.get("/eaglesuite/api/retours/details.php", {id:id}, function(data){
            $("#detailsContent").html(data);
        });
    });

// Charger données dans modal Modifier
$('#modalModifier').on('show.bs.modal', function(e){
    let id = $(e.relatedTarget).data('id');

    $.getJSON("/eaglesuite/api/retours/get_retour.php", {id:id}, function(data){
        if (data.error) {
            alert("Erreur : " + data.error);
            return;
        }

        // Champs principaux
        $("#modifier_id").val(data.id);
        $("#modifier_type").val(data.type).trigger("change");
        $("#modifier_depot").val(data.depot_id);
        $("#modifier_date").val(data.date_retour.replace(' ','T'));
        $("#modifier_raison").val(data.raison);

        // Sélectionner client ou fournisseur
        if (data.type === "client" && data.client_id) {
            $("#modifier_acteur").val("client-" + data.client_id);
        } else if (data.type === "fournisseur" && data.fournisseur_id) {
            $("#modifier_acteur").val("fournisseur-" + data.fournisseur_id);
        }

        // Produits
        let rows = "";
        data.produits.forEach(p => {
            rows += `
              <tr>
                <td>${p.nom}<input type="hidden" name="produits[][id]" value="${p.id}"></td>
                <td><input type="number" name="produits[][quantite]" value="${p.quantite}" class="form-control form-control-sm"></td>
                <td><input type="number" step="0.01" name="produits[][prix_unitaire]" value="${p.prix_unitaire}" class="form-control form-control-sm"></td>
                <td><button type="button" class="btn btn-sm btn-danger btnRemoveRow">&times;</button></td>
              </tr>
            `;
        });
        $("#tableModifierProduits tbody").html(rows);
    }).fail(function(){
        alert("Impossible de charger les données du retour.");
    });
});


    // Suppression de ligne dans tableau
    $(document).on('click', '.btnRemoveRow', function(){
        $(this).closest("tr").remove();
    });
});

</script>