<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/check_auth.php";

requirePermission("crm.interactions.view");

$interactions = $conn->query("
    SELECT i.*, c.nom AS client_nom, u.nom AS user_nom
    FROM crm_interactions i
    JOIN clients c ON c.idClient = i.client_id
    LEFT JOIN utilisateurs u ON u.id = i.utilisateur_id
    ORDER BY i.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$title = "Interactions CRM";
?>

<div class="container-fluid">
    <h1 class="mt-4">Interactions CRM</h1>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=crm_dashboard">CRM</a></li>
        <li class="breadcrumb-item active">Interactions</li>
    </ol>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between">
            <span><i class="fa fa-comments"></i> Historique des interactions</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fa fa-plus"></i> Ajouter
            </button>
        </div>

        <div class="card-body table-responsive">
            <table id="interactionsTable" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Sujet</th>
                        <th>Utilisateur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interactions as $i): ?>
                    <tr>
                        <td><?= date("d/m/Y H:i", strtotime($i['created_at'])) ?></td>
                        <td><?= htmlspecialchars($i['client_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($i['type'], ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars($i['sujet'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($i['user_nom'], ENT_QUOTES, 'UTF-8' ?? "-") ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm btnEdit"
                                data-id="<?= $i['id'] ?>"
                                data-sujet="<?= htmlspecialchars($i['sujet'], ENT_QUOTES, 'UTF-8') ?>"
                                data-message="<?= htmlspecialchars($i['message'], ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fa fa-edit"></i>
                            </button>

                            <button class="btn btn-danger btn-sm btnDelete" data-id="<?= $i['id'] ?>">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL AJOUT -->
<div class="modal fade" id="addModal">
  <div class="modal-dialog">
    <form class="modal-content" id="formAdd">
        <div class="modal-header"><h5 class="modal-title">Nouvelle Interaction</h5></div>
        <div class="modal-body">
            <label>Client :</label>
            <select class="form-select" name="client_id" required>
                <option value="">-- Choisir --</option>
                <?php
                $clients = $conn->query("SELECT idClient, nom FROM clients ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($clients as $c): ?>
                    <option value="<?= $c['idClient'] ?>"><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>

            <label class="mt-2">Type :</label>
            <select class="form-select" name="type">
                <option value="appel">Appel</option>
                <option value="email">Email</option>
                <option value="rdv">Rendez-vous</option>
                <option value="note">Note</option>
            </select>

            <label class="mt-2">Sujet :</label>
            <input type="text" class="form-control" name="sujet" required>

            <label class="mt-2">Message :</label>
            <textarea class="form-control" name="message" rows="3"></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="editModal">
  <div class="modal-dialog">
    <form class="modal-content" id="formEdit">
        <div class="modal-header"><h5 class="modal-title">Modifier Interaction</h5></div>
        <div class="modal-body">
            <input type="hidden" name="id" id="editId">

            <label>Sujet :</label>
            <input type="text" class="form-control" name="sujet" id="editSujet">

            <label class="mt-2">Message :</label>
            <textarea class="form-control" name="message" id="editMessage"></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-primary">Mettre à jour</button>
        </div>
    </form>
  </div>
</div>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function() {
    $('#interactionsTable').DataTable({
        language: { url: "/eaglesuite/public/js/fr-FR.json" }
    });

    // ADD
    $("#formAdd").submit(function(e){
        e.preventDefault();
        $.post("pages/crm/actions.php", $(this).serialize() + "&action=add_interaction", function(res){
            if(res.success) location.reload();
            else alert(res.error);
        }, "json");
    });

    // EDIT
    $(".btnEdit").click(function(){
        $("#editId").val($(this).data("id"));
        $("#editSujet").val($(this).data("sujet"));
        $("#editMessage").val($(this).data("message"));
        $("#editModal").modal("show");
    });

    $("#formEdit").submit(function(e){
        e.preventDefault();
        $.post("pages/crm/actions.php", $(this).serialize() + "&action=update_interaction", function(res){
            if(res.success) location.reload();
            else alert(res.error);
        }, "json");
    });

    // DELETE
    $(".btnDelete").click(function(){
        if(!confirm("Supprimer cette interaction ?")) return;
        $.post("pages/crm/actions.php", {id:$(this).data("id"),action:"delete_interaction"}, function(res){
            if(res.success) location.reload();
            else alert(res.error);
        }, "json");
    });

});
</script>
