<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';

$title = "Fournisseurs";

// Récupération fournisseurs
$fournisseurs = $conn->query("SELECT * FROM fournisseurs ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h1 class="mb-4">Fournisseurs</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/{{TENANT_DIR}}/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Fournisseurs</li>
    </ol>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-truck"></i> Liste des fournisseurs</span>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajouterFournisseurModal">
                <i class="fa fa-plus"></i> Ajouter
            </button>
        </div>
        <div class="card-body">
            <table id="fournisseursTable" class="table table-striped table-bordered">
                 <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Adresse</th>
                        <th>Taxe</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($fournisseurs as $f): ?>
                    <tr>
                        <td><?= $f['id'] ?></td>
                        <td><?= htmlspecialchars($f['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($f['telephone'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($f['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($f['adresse'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?php if ($f['exonere']): ?>
                                <span class="badge bg-warning text-dark">Exonéré</span>
                            <?php else: ?>
                                <span class="badge bg-success">Soumis</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button 
                            class="btn btn-sm btn-warning btn-edit" 
                            data-id="<?= $f['id'] ?>"
                            data-nom="<?= htmlspecialchars($f['nom'], ENT_QUOTES, 'UTF-8') ?>"
                            data-telephone="<?= htmlspecialchars($f['telephone'], ENT_QUOTES, 'UTF-8') ?>"
                            data-email="<?= htmlspecialchars($f['email'], ENT_QUOTES, 'UTF-8') ?>"
                            data-adresse="<?= htmlspecialchars($f['adresse'], ENT_QUOTES, 'UTF-8') ?>"
                            data-exonere="<?= $f['exonere'] ?>"
                            >
                            <i class="fa fa-edit"></i>
                            </button>
                            <button 
                                class="btn btn-sm btn-danger btn-delete"
                                data-id="<?= $f['id'] ?>"
                                data-nom="<?= htmlspecialchars($f['nom'], ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fa fa-trash"></i> 
                            </button>
                        </td>
                    </tr>
                    <?php include __DIR__ . "/modal_modifier_fournisseur.php"; ?>
                    <?php include __DIR__ . "/modal_supprimer_fournisseur.php"; ?>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . "/modal_ajouter_fournisseur.php"; ?>
<?php include __DIR__ . '/../../includes/layout_end.php'; ?>

<script>
$(document).ready(function() {
    $('#fournisseursTable').DataTable({
        responsive: true,
        language: { url: "/{{TENANT_DIR}}/public/js/fr-FR.json" }
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const editButtons = document.querySelectorAll(".btn-edit");
  editButtons.forEach(btn => {
    btn.addEventListener("click", function () {
      document.getElementById("edit-id").value = this.dataset.id;
      document.getElementById("edit-nom").value = this.dataset.nom;
      document.getElementById("edit-adresse").value = this.dataset.adresse;
      document.getElementById("edit-telephone").value = this.dataset.telephone;
      document.getElementById("edit-email").value = this.dataset.email;
      document.getElementById("edit-exonere").checked = this.dataset.exonere == "1";

      new bootstrap.Modal(document.getElementById("modalEditFournisseur")).show();
    });
  });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".btn-delete").forEach(btn => {
    btn.addEventListener("click", function () {
      document.getElementById("delete-id").value = this.dataset.id;
      document.getElementById("delete-fournisseur-name").textContent = this.dataset.nom;

      new bootstrap.Modal(document.getElementById("modalDeleteFournisseur")).show();
    });
  });
});
</script>
