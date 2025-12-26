<?php
require_once __DIR__ . "/../../../config/db.php";

// Charger les infos de l’entreprise (on suppose qu’il n’y a qu’un seul enregistrement avec id=1)
$stmt = $conn->prepare("SELECT * FROM entreprise WHERE id = 1 LIMIT 1");
$stmt->execute();
$entreprise = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fa fa-building"></i> Informations de l'entreprise</span>

    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterEntreprise">
      <i class="fa fa-plus"></i> Ajouter
    </button>
  </div>
  <div class="card-body">
    <table id="tableEntreprise" class="table table-bordered table-striped datatable">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Adresse</th>
          <th>Telephones</th>
          <th>Email</th>
         <!--  <th>Site web</th>
          <th>Ninea</th>
          <th>RCCM</th> -->
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
         <?php if ($entreprise): ?>

        <tr>
          <td><?= $entreprise['id'] ?></td>
          <td><?= htmlspecialchars($entreprise['nom'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($entreprise['adresse'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($entreprise['telephone'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($entreprise['email'], ENT_QUOTES, 'UTF-8') ?></td>
          <!-- <td><!?= htmlspecialchars($entreprise['site_web']) ?></td> -->
        <!--   <td><!?= htmlspecialchars($entreprise['ninea']) ?></td>
          <td><!?= htmlspecialchars($entreprise['rccm']) ?></td> -->
          <td>
            <button 
              class="btn btn-sm btn-warning btnEditEntreprise"
              data-id="<?= $entreprise['id'] ?>"
              data-nom="<?= htmlspecialchars($entreprise['nom'], ENT_QUOTES, 'UTF-8') ?>"
              data-adresse="<?= htmlspecialchars($entreprise['adresse'], ENT_QUOTES, 'UTF-8') ?>"
              data-telephone="<?= htmlspecialchars($entreprise['telephone'], ENT_QUOTES, 'UTF-8') ?>"
              data-email="<?= htmlspecialchars($entreprise['email'], ENT_QUOTES, 'UTF-8') ?>"
              data-site_web="<?= htmlspecialchars($entreprise['site_web'], ENT_QUOTES, 'UTF-8') ?>"
              data-ninea="<?= htmlspecialchars($entreprise['ninea'], ENT_QUOTES, 'UTF-8') ?>"
              data-rccm="<?= htmlspecialchars($entreprise['rccm'], ENT_QUOTES, 'UTF-8') ?>"
              data-logo="<?= htmlspecialchars($entreprise['logo'], ENT_QUOTES, 'UTF-8') ?>"
              data-bs-toggle="modal"
              data-bs-target="#modalModifierEntreprise"
            >
              <i class="fa fa-edit"></i>
            </button>


            <button 
            class="btn btn-danger btn-sm btnSupprimerEntreprise"
            data-id="<?= $entreprise['id'] ?>"
            data-nom="<?= htmlspecialchars($entreprise['nom'], ENT_QUOTES, 'UTF-8') ?>"
            data-bs-toggle="modal"
            data-bs-target="#modalDeleteEntreprise">
            <i class="fa fa-trash"></i> 
          </button>

          </td>
        </tr>
        <?php else: ?>
        <tr>
          <td colspan="9" class="text-center text-muted">Aucune entreprise enregistrée</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . "/modal_ajouter_entreprise.php"; ?>
<?php include __DIR__ . "/modal_modifier_entreprise.php"; ?>
 <?php include __DIR__ . "/modal_supprimer_entreprise.php"; ?>


 <script>
$(document).on("click", ".btnSupprimerEntreprise", function () {
    $("#supprimer_id").val($(this).data("id"));
    $("#supprimer_nom").text($(this).data("nom"));
});
</script>
