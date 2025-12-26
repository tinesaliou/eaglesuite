<?php
require_once __DIR__ . '/../init.php';
require_admin();

// récupérer clients suspendus (ou tous avec filtre)
$filter = $_GET['filter'] ?? 'suspendu';

$sql = "SELECT * FROM clients_saas ORDER BY date_creation DESC";
$stmt = $masterPdo->query($sql);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

//include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-2 mt-4">
    <div class="container-fluid px-2">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/admin/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Suspension</li>
    </ol>
  <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h1 class="h4">Suspensions / Réactivations</h1>
    <div>
      <a href="index.php?page=clients" class="btn btn-outline-secondary btn-sm">Retour clients</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-hover">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Société</th>
            <th>Subdomain</th>
            <th>Expiration</th>
            <th>Statut</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($clients as $c): ?>
            <tr>
              <td><?= $c['id'] ?></td>
              <td><?= htmlspecialchars($c['societe']) ?></td>
              <td><?= htmlspecialchars($c['subdomain']) ?></td>
              <td><?= $c['expiration'] ?? '-' ?></td>
              <td>
                <?php if ($c['statut'] === 'actif'): ?>
                  <span class="badge bg-success">Actif</span>
                <?php else: ?>
                  <span class="badge bg-danger">Suspendu</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($c['statut'] === 'actif'): ?>
                  <form method="POST" action="/eaglesuite/admin/actions.php" class="d-inline">
                    <input type="hidden" name="action" value="suspend_client">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn btn-warning btn-sm">Suspendre</button>
                  </form>
                <?php else: ?>
                  <form method="POST" action="/eaglesuite/admin/actions.php" class="d-inline">
                    <input type="hidden" name="action" value="reactivate_client">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn btn-success btn-sm">Réactiver</button>
                  </form>
                <?php endif; ?>

                <a href="/eaglesuite/admin/actions.php?action=impersonate&client=<?= urlencode($c['subdomain']) ?>" class="btn btn-info btn-sm" target="_blank">Ouvrir</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
