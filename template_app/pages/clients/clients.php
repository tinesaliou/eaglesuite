<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';

// Récupération des clients
$clients = $conn->query("SELECT * FROM clients ORDER BY idClient DESC")->fetchAll(PDO::FETCH_ASSOC);

$title = "Clients";
//include __DIR__ . '/../../includes/layout.php';
?>

<div class="container-fluid px-2">
    <h1 class="mt-4">Clients</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/{{TENANT_DIR}}/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Clients</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-users"></i> Liste des clients</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ajouterClientModal">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        </div>

        <div class="card-body">
            <table id="clientsTable" class="table table-bordered table-striped datatable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Adresse</th>
                        <th>Type</th>
                        <th>Taxe</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($clients as $c): ?>
                    <tr>
                        <td><?= $c['idClient'] ?></td>
                        <td><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($c['email'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($c['telephone'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($c['adresse'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge bg-info"><?= $c['type'] ?></span></td>
                        <td>
                            <?php if ($c['exonere']): ?>
                                <span class="badge bg-warning text-dark">Exonéré</span>
                            <?php else: ?>
                                <span class="badge bg-success">Soumis</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button 
                            class="btn btn-sm btn-warning btn-edit" 
                            data-id="<?= $c['idClient'] ?>"
                            data-nom="<?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?>"
                            data-adresse="<?= htmlspecialchars($c['adresse'], ENT_QUOTES, 'UTF-8') ?>"
                            data-telephone="<?= htmlspecialchars($c['telephone'], ENT_QUOTES, 'UTF-8') ?>"
                            data-email="<?= htmlspecialchars($c['email'], ENT_QUOTES, 'UTF-8') ?>"
                            data-type="<?= $c['type'] ?>"
                            data-exonere="<?= $c['exonere'] ?>"
                            >
                            <i class="fa fa-edit"></i>
                            </button>

                            <button 
                                class="btn btn-sm btn-danger btn-delete"
                                data-id="<?= $c['idClient'] ?>"
                                data-nom="<?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fa fa-trash"></i> 
                            </button>
                        </td>
                    </tr>
                    <?php include __DIR__ . "/modal_modifier_client.php"; ?>
                    <?php include __DIR__ . "/modal_supprimer_client.php"; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . "/modal_ajouter_client.php"; ?>
<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function() {
    $('#clientsTable').DataTable({
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
      document.getElementById("edit-idClient").value = this.dataset.id;
      document.getElementById("edit-nom").value = this.dataset.nom;
      document.getElementById("edit-adresse").value = this.dataset.adresse;
      document.getElementById("edit-telephone").value = this.dataset.telephone;
      document.getElementById("edit-email").value = this.dataset.email;
      document.getElementById("edit-type").value = this.dataset.type;
      document.getElementById("edit-exonere").checked = this.dataset.exonere == "1";

      new bootstrap.Modal(document.getElementById("modalEditClient")).show();
    });
  });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".btn-delete").forEach(btn => {
    btn.addEventListener("click", function () {
      document.getElementById("delete-idClient").value = this.dataset.id;
      document.getElementById("delete-client-name").textContent = this.dataset.nom;

      new bootstrap.Modal(document.getElementById("modalDeleteClient")).show();
    });
  });
});
</script>
