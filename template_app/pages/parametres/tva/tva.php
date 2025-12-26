<?php
require_once __DIR__ . "/../../../config/db.php";

// Charger toutes les unités
$stmt = $conn->query("SELECT * FROM tva ORDER BY id DESC");
$tva = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fa fa-tags"></i> Liste des TVA</span>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterTva">
      <i class="fa fa-plus"></i> Ajouter
    </button>
  </div>
  <div class="card-body">
    <table id="tableTva" class="table table-bordered table-striped datatable">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Taux</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tva as $t): ?>
        <tr>
          <td><?= $t['id'] ?></td>
          <td><?= htmlspecialchars($t['nom'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= $t['taux'] ?></td>
          <td>
            <button 
              class="btn btn-sm btn-warning btnEditTva"
              data-id="<?= $u['id'] ?>"
              data-nom="<?= htmlspecialchars($t['nom'], ENT_QUOTES, 'UTF-8') ?>"
              data-taux="<?= $t['taux'] ?>"
              data-bs-toggle="modal"
              data-bs-target="#modalModifierTva"
            >
              <i class="fa fa-edit"></i>
            </button>
           
            <button 
            class="btn btn-danger btn-sm btnSupprimerTva"
            data-id="<?= $t['id'] ?>"
            data-nom="<?= htmlspecialchars($t['nom'], ENT_QUOTES, 'UTF-8') ?>"
            data-bs-toggle="modal"
            data-bs-target="#modalDeleteTva">
            <i class="fa fa-trash"></i> 
          </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . "/modal_ajouter_tva.php"; ?>
<?php include __DIR__ . "/modal_modifier_tva.php"; ?>
 <?php include __DIR__ . "/modal_supprimer_tva.php"; ?>

<script>
$(document).ready(function() {
  $('#tableTva').DataTable({
    responsive: true,
    language: { url: "/eagle_client_redenshop/public/js/fr-FR.json" }
  });
});
</script>
<script>
$(document).on("click", ".btnSupprimerTva", function () {
    $("#supprimer_id").val($(this).data("id"));
    $("#supprimer_nom").text($(this).data("nom"));
});
</script>
