<?php
//include __DIR__ . "/../../includes/layout.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';

// Charger toutes les catégories
$stmt = $conn->query("SELECT * FROM depots ORDER BY id DESC");
$depots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-2">
    <h1 class="mt-4">Dépôts</h1>
       <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/{{TENANT_DIR}}/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Dépôts</li>
      </ol>

       <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
             <span><i class="fa fa-tags"></i> Liste des depôts</span>
               <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterDepot">
                   <i class="fa fa-plus"></i> Ajouter
               </button>
          </div>
           <div class="card-body">
               <table id="tableDepots" class="table table-bordered table-striped datatable">
                 <!-- <thead class="table-dark"> -->
                 <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($depots as $dep): ?>
                    <tr>
                        <td><?= $dep['id'] ?></td>
                        <td><?= htmlspecialchars($dep['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($dep['description'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $dep['created_at'] ?></td>
                        <td>
                            <button 
                                class="btn btn-sm btn-warning btnEditDepot"
                                data-id="<?= $dep['id'] ?>"
                                data-nom="<?= htmlspecialchars($dep['nom'], ENT_QUOTES, 'UTF-8') ?>"
                                data-description="<?= htmlspecialchars($dep['description'], ENT_QUOTES, 'UTF-8') ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#modalModifierDepot"
                            >
                                <i class="fa fa-edit"></i>
                            </button>
                            <a href="../../api/depots/supprimer.php?id=<?= $dep['id'] ?>"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm('Supprimer ce dépôt ?')">
                            <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . "/modal_ajouter_depot.php"; ?>
<?php include __DIR__ . "/modal_modifier_depot.php"; ?>



<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function() {
    $('#tableDepots').DataTable({
        responsive: true,
        language: { url: "/{{TENANT_DIR}}/public/js/fr-FR.json" }
    });
});
</script>

