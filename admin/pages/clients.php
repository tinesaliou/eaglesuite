<?php
require_once __DIR__ . '/../init.php';
require_admin();

$clients = $masterPdo->query("
    SELECT * FROM clients_saas 
    ORDER BY date_creation DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Clients SaaS — EagleSuite</title>
<!-- link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"> -->
   <link rel="stylesheet" href="/eaglesuite/public/vendor/bootstrap/css/bootstrap.min.css">
 <link rel="stylesheet" href="/eaglesuite/public/vendor/bootstrap/icons/bootstrap-icons.css">

 <link rel="stylesheet" href="/eaglesuite/public/vendor/fontawesome/css/all.min.css">
</head>
<body class="bg-light">



<div class="container-fluid px-2 mt-4">
<div class="container-fluid px-2">
   <!--  <h1 class="fw-bold">Clients SaaS</h1> -->
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/admin/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Clients Saas</li>
    </ol>

   <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
      <h3 class="mb-4">Liste des Clients</h3>
      <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalCreateClient">
        + Nouveau Client
      </button>
   </div>

    <table class="table table-bordered table-hover bg-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Société</th>
                <th>Email</th>
                <th>Sous-domaine</th>
                <th>Expiration</th>
                <th>Statut</th>
                <th width="220px">Actions</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($clients as $c): ?>
            <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['societe']) ?></td>
                <td><?= htmlspecialchars($c['email']) ?></td>
                <td><?= htmlspecialchars($c['subdomain']) ?></td>
                <td><?= $c['expiration'] ?></td>
                <td>
                    <?php if ($c['statut'] === 'actif'): ?>
                        <span class="badge bg-success">Actif</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Suspendu</span>
                    <?php endif; ?>
                </td>

                <td>
                <button class="btn btn-warning btn-sm btnEditClient"
                        data-json='<?= htmlspecialchars(json_encode($c), ENT_QUOTES) ?>'
                        title="Modifier">
                    <i class="fa fa-edit"></i>
                </button>

                <!-- Ouvrir le tenant -->
                <a href="/eaglesuite/admin/actions.php?action=impersonate&client=<?= urlencode($c['subdomain']) ?>"
                  class="btn btn-info btn-sm"
                  target="_blank"
                  title="Ouvrir l'espace client">
                    <i class="fa fa-eye"></i>
                </a>

                <!-- Suppression → modal -->
                <button class="btn btn-danger btn-sm btnDeleteClient"
                        data-id="<?= $c['id'] ?>"
                        data-sub="<?= htmlspecialchars($c['subdomain']) ?>"
                        title="Supprimer">
                    <i class="fa fa-trash"></i>
                </button>


                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</div>
</div>

<!-- ======= MODALES ======= -->
<?php include __DIR__ . '/../modals/modal_client_create.php'; ?>
<?php include __DIR__ . '/../modals/modal_client_edit.php'; ?>
<?php include __DIR__ . '/../modals/modal_client_delete.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* Remplissage modal EDIT */
document.querySelectorAll('.btnEditClient').forEach(btn => {
    btn.addEventListener('click', () => {
        const data = JSON.parse(btn.dataset.json);
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_societe').value = data.societe;
        document.getElementById('edit_subdomain').value = data.subdomain;
        document.getElementById('edit_pack').value = data.pack;
        new bootstrap.Modal(document.getElementById('modalEditClient')).show();
    });
});

/* Remplissage modal DELETE */
document.querySelectorAll('.btnDeleteClient').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('delete_client_id').value = btn.dataset.id;
        document.getElementById('delete_client_sub').textContent = btn.dataset.sub;
        new bootstrap.Modal(document.getElementById('modalDeleteClient')).show();
    });
});
</script>

</body>
</html>
