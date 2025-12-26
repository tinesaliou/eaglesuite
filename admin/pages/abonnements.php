<?php
require_once __DIR__ . '/../init.php';
require_admin();

// Récupérer abonnements + info client
$stmt = $masterPdo->query("
    SELECT a.*, c.societe, c.subdomain
    FROM abonnements_saas a
    JOIN clients_saas c ON c.id = a.client_id
    ORDER BY a.date_creation DESC
");
$abos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-2 mt-4">
  <div class="container-fluid px-2">

   <!--  <h1 class="fw-bold">Abonnements SaaS</h1> -->
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/admin/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Abonnement Saas</li>
    </ol>

    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h2 class="mt-4">
            <i class="bi bi-calendar-check text-primary me-2"></i> Liste Abonnements 
        </h2>

        <button class="btn btn-success btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCreateAbo">
            <i class="bi bi-plus-circle me-1"></i> Nouvel abonnement
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <table id="abosTable" class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Prix d'acquisition</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th class="text-center" width="140">Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach ($abos as $a): ?>
                    <tr>
                        <td><strong><?= $a['id'] ?></strong></td>

                        <td>
                            <strong><?= htmlspecialchars($a['societe']) ?></strong><br>
                            <small class="text-muted"><?= $a['subdomain'] ?></small>
                        </td>

                        <td>
                            <span class="badge bg-primary"><?= ucfirst($a['type']) ?></span>
                        </td>

                        <td><?= number_format($a['prix_acquisition'], 0, ',', ' ') ?> F CFA</td>

                        <td><?= $a['date_debut'] ?></td>
                        <td><?= $a['date_fin'] ?></td>

                        <td>
                            <?php if ($a['statut'] === 'actif'): ?>
                                <span class="badge bg-success">Actif</span>
                            <?php elseif ($a['statut'] === 'expiré'): ?>
                                <span class="badge bg-warning text-dark">Expiré</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Suspendu</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">

                            <!-- Edit -->
                            <button class="btn btn-warning btn-sm rounded-circle btnEditAbo " data-bs-toggle="modal" data-bs-target="#modalEditAbo"
                                title="Modifier"
                                data-json='<?= htmlspecialchars(json_encode($a), ENT_QUOTES) ?>'>
                                <i class="bi bi-pencil"></i>
                            </button>

                            <!-- Suppression -->
                            <button class="btn btn-danger btn-sm rounded-circle ms-1 btnDeleteAbo"
                                title="Supprimer"
                                data-id="<?= $a['id'] ?>">
                                <i class="bi bi-trash"></i>
                            </button>

                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
 </div>
</div>

<!-- Modales -->
<?php include __DIR__ . '/../modals/modal_abo_create.php'; ?>
<?php include __DIR__ . '/../modals/modal_abo_edit.php'; ?>
<?php include __DIR__ . '/../modals/modal_abo_delete.php'; ?>

<!-- JS -->
<script src="/eaglesuite/public/js/jquery-3.7.0.min.js"></script>
<script src="/eaglesuite/public/js/bootstrap.bundle.min.js"></script>
<script src="/eaglesuite/public/js/datatables.min.js"></script>

<script>
$(function() {

    $('#abosTable').DataTable({
        pageLength: 10,
        order: [[0, 'desc']],
        language: {
            url: "/eaglesuite/public/js/datatable-fr.json"
        }
    });

    // Edit modal
    $('.btnEditAbo').on('click', function(){
        const data = JSON.parse($(this).data('json'));
        $('#edit_abo_id').val(data.id);
        $('#edit_abo_prix').val(data.prix);
        $('#edit_abo_type').val(data.type);
        $('#edit_abo_debut').val(data.date_debut);
        $('#edit_abo_fin').val(data.date_fin);
        $('#modalEditAbo').modal('show');
    });

    // Delete modal
    $('.btnDeleteAbo').on('click', function(){
        $('#delete_abo_id').val($(this).data('id'));
        $('#modalDeleteAbo').modal('show');
    });

});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
