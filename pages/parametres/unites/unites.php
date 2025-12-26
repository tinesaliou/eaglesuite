<?php
require_once __DIR__ . "/../../../config/db.php";

// Charger toutes les unités
$stmt = $conn->query("SELECT * FROM unites ORDER BY id DESC");
$unites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fa fa-tags"></i> Liste des unités</span>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterUnite">
      <i class="fa fa-plus"></i> Ajouter
    </button>
  </div>
  <div class="card-body">
    <table id="tableUnites" class="table table-bordered table-striped datatable">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Créé le</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($unites as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= $u['created_at'] ?></td>
          <td>
            <button 
              class="btn btn-sm btn-warning btnEditUnite"
              data-id="<?= $u['id'] ?>"
              data-nom="<?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?>"
              data-bs-toggle="modal"
              data-bs-target="#modalModifierUnite"
            >
              <i class="fa fa-edit"></i>
            </button>
           
              <button 
              class="btn btn-danger btn-sm btnSupprimerUnite"
              data-id="<?= $u['id'] ?>"
              data-nom="<?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?>"
              data-bs-toggle="modal"
              data-bs-target="#modalDeleteUnite">
              <i class="fa fa-trash"></i> 
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . "/modal_ajouter_unite.php"; ?>
<?php include __DIR__ . "/modal_modifier_unite.php"; ?>
 <?php include __DIR__ . "/modal_supprimer_unite.php"; ?>

<script>
$(document).ready(function() {
  $('#tableUnites').DataTable({
    responsive: true,
    language: { url: "/eaglesuite/public/js/fr-FR.json" }
  });
});
</script>
<script>
$(document).on("click", ".btnSupprimerUnite", function () {
    $("#supprimer_id").val($(this).data("id"));
    $("#supprimer_nom").text($(this).data("nom"));
});
</script>
