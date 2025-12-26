<?php
require_once __DIR__ . '/../init.php';
require_admin();

$stmt = $masterPdo->query("
    SELECT a.*, c.societe, c.subdomain
    FROM abonnements_saas a
    JOIN clients_saas c ON c.id = a.client_id
    WHERE a.statut = 'actif'
    ORDER BY a.date_fin ASC
");
$abos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-2 mt-4">
    <div class="container-fluid px-2">
   <!--  <h1 class="fw-bold">Renouvellement </h1> -->
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/admin/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Renouvellement</li>
    </ol>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-arrow-repeat"></i> Renouvellement automatique</h2>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <table id="renewTable" class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Client</th>
            <th>Abonnement</th>
            <th>Expire le</th>
            <th>Auto-renew</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($abos as $a): ?>
          <tr>
            <td><strong><?= htmlspecialchars($a['societe']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($a['subdomain']) ?></small></td>
            <td><?= ucfirst($a['type']) ?> — <?= number_format($a['prix_acquisition'],0,',',' ') ?> F CFA</td>
            <td><?= $a['date_fin'] ?></td>
            <td>
              <?php if (!empty($a['auto_renew']) && $a['auto_renew'] == 1): ?>
                <span class="badge bg-success">Oui</span>
              <?php else: ?>
                <span class="badge bg-secondary">Non</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <a href="/eaglesuite/admin/actions.php?action=toggle_auto_renew&id=<?= $a['id'] ?>" class="btn btn-sm btn-primary rounded-circle" title="Toggle auto-renew">
                <i class="bi bi-toggle2-on"></i>
              </a>

              <a href="/eaglesuite/admin/actions.php?action=renew_now&id=<?= $a['id'] ?>" class="btn btn-sm btn-warning rounded-circle ms-1" title="Renouveler maintenant">
                <i class="bi bi-arrow-clockwise"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="/eaglesuite/public/js/jquery-3.7.0.min.js"></script>
<script src="/eaglesuite/public/js/bootstrap.bundle.min.js"></script>
<script src="/eaglesuite/public/js/datatables.min.js"></script>
<script>
$(function(){ $('#renewTable').DataTable({ order:[[2,'asc']], language: { url: "/eaglesuite/public/js/datatable-fr.json" } }); });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
