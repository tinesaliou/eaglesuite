<?php
$stmt = $conn->query("
    SELECT 
        c.id,
        c.nom,
        c.actif,
        c.date_creation,
        COALESCE(SUM(ct.solde_actuel),0) AS solde_total
    FROM caisses c
    LEFT JOIN caisse_types ct ON ct.caisse_id = c.id
    GROUP BY c.id
    ORDER BY c.id DESC
");
$caisses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fa fa-cash-register"></i> Caisses</span>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterCaisse">
      <i class="fa fa-plus"></i> Ajouter
    </button>
  </div>

  <div class="card-body">
    <table class="table table-bordered table-striped datatable">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nom  Caisse</th>
          <th>Solde total</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($caisses as $c): ?>
        <tr>
          <td><?= $c['id'] ?></td>
          <td><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= number_format($c['solde_total'],2,'.',' ') ?> FCFA</td>
          <td>
            <?= $c['actif'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?>
          </td>
          <td>
            <a href="?page=caisse_types&caisse_id=<?= $c['id'] ?>"
              class="btn btn-sm btn-info">Types</a>

            <a href="?page=affectation_utilisateurs&caisse_id=<?= $c['id'] ?>"
              class="btn btn-sm btn-secondary">Utilisateurs</a>

            <button class="btn btn-sm btn-warning btnEditCaisse"
              data-id="<?= $c['id'] ?>"
              data-nom="<?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?>"
              data-bs-toggle="modal"
              data-bs-target="#modalModifierCaisse">
              <i class="fa fa-edit"></i>
            </button>

            <button class="btn btn-sm btn-danger btnDeleteCaisse"
              data-id="<?= $c['id'] ?>"
              data-nom="<?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?>"
              data-bs-toggle="modal"
              data-bs-target="#modalDeleteCaisse">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
include "modal_ajouter_caisse.php";
include "modal_modifier_caisse.php";
include "modal_supprimer_caisse.php";
?>

<script>
$(document).ready(function () {
  $('.datatable').DataTable({
    responsive: true,
    language: { url: "/eaglesuite/public/js/fr-FR.json" }
  });
});
</script>

<script>
$(document).on("click", ".btnEditCaisse", function () {
  $("#edit_id").val($(this).data("id"));
  $("#edit_code").val($(this).data("code"));
  $("#edit_nom").val($(this).data("nom"));
  $("#edit_type").val($(this).data("type"));
  $("#edit_solde").val($(this).data("solde"));
});

$(document).on("click", ".btnDeleteCaisse", function () {
  $("#delete_id").val($(this).data("id"));
  $("#delete_nom").text($(this).data("nom"));
});
</script>
