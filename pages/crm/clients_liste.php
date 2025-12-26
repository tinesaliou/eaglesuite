<?php
require_once __DIR__ . "/../../config/db.php";
//require_once __DIR__ . "/../../includes/check_auth.php";
//requirePermission('crm.clients.view');

$clients = $conn->query("
    SELECT *, 
           DATE_FORMAT(created_at,'%d/%m/%Y') AS created,
           DATE_FORMAT(derniere_interaction,'%d/%m/%Y %H:%i') AS last
    FROM clients
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

  <div class="container-fluid">
        <h1 class=class="mt-4">Clients CRM</h1>
          <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=crm_dashboard">CRM</a></li>
            <li class="breadcrumb-item active">Clients</li>
         </ol>

      <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between">
            <span><i class="fa fa-users"></i> Liste des clients</span>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddClient">
                <i class="fa fa-plus"></i> Ajouter
            </button>
        </div>

          <div class="card-body table-responsive">
            <table id="tableCRMClients" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Origine</th>
                        <th>Score</th>
                        <th>Dernière interaction</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($clients as $c): ?>

                    <tr>
                        <td><strong><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></strong></td>

                        <td>
                            <span class="badge bg-info"><?= $c['type'] ?></span>
                        </td>

                        <td>
                            <?php
                                $colors = [
                                    'Actif' => 'success',
                                    'Inactif' => 'secondary',
                                    'Prospect' => 'warning'
                                ];
                            ?>
                            <span class="badge bg-<?= $colors[$c['statut']] ?? 'light' ?>">
                                <?= $c['statut'] ?>
                            </span>
                        </td>

                        <td><?= $c['origine'] ?: '-' ?></td>

                        <td>
                            <span class="badge bg-primary">
                                <?= $c['score'] ?> pts
                            </span>
                        </td>

                        <td><?= $c['last'] ?: '-' ?></td>

                        <td>
                            <a href="/eaglesuite/index.php?page=crm_client&id=<?= $c['idClient'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                Voir
                            </a>
                        </td>
                    </tr>

                    <?php endforeach; ?>
                </tbody>

            </table>

        </div>
    </div>
</div>

<?php include __DIR__ . "/modal_add_client.php"; ?>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function(){
    $('#tableCRMClients').DataTable({
        pageLength: 25,
        language: {
            url: "/eaglesuite/public/js/fr-FR.json"
        }
    });
});
</script>
