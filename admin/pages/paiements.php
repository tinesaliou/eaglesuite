<?php
require_once __DIR__ . '/../init.php';
require_admin();

// filters
$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');

// build query
$sql = "SELECT f.*, c.societe, c.subdomain FROM saas_factures f JOIN clients_saas c ON c.id=f.client_id";
$where = []; $params=[];
if ($status) { $where[]="f.statut=?"; $params[]=$status; }
if ($q) { $where[]="(c.societe LIKE ? OR f.reference LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; }
if ($where) $sql .= " WHERE ".implode(" AND ", $where);
$sql .= " ORDER BY f.date_creation DESC";

$stmt = $masterPdo->prepare($sql);
$stmt->execute($params);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="container-fluid px-2 mt-4">
    <div class="container-fluid px-2">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/admin/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Paiements</li>
    </ol>

  <div class="d-flex justify-content-between align-items-center mt-3">
    <h4>Paiements / Factures</h4>
    <form class="d-flex" method="get">
      <input type="hidden" name="page" value="payments">
      <input name="q" value="<?= htmlspecialchars($q) ?>" class="form-control form-control-sm me-2" placeholder="Search...">
      <select name="status" class="form-select form-select-sm me-2">
        <option value="">Tous</option>
        <option value="impayé" <?= $status=='impayé'?'selected':'' ?>>Impayé</option>
        <option value="payé" <?= $status=='payé'?'selected':'' ?>>Payé</option>
      </select>
      <button class="btn btn-primary btn-sm">Filtrer</button>
    </form>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <table class="table table-hover table-sm align-middle">
        <thead class="table-dark">
          <tr><th>#</th><th>Client</th><th>Ref</th><th>Montant</th><th>Émise</th><th>Échéance</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach($factures as $f): ?>
          <tr>
            <td><?= $f['id'] ?></td>
            <td><strong><?= htmlspecialchars($f['societe']) ?></strong><br><small><?= htmlspecialchars($f['subdomain']) ?></small></td>
            <td><?= htmlspecialchars($f['reference'] ?? '') ?></td>
            <td><?= number_format($f['montant'],0,',',' ') ?> F CFA</td>
            <td><?= $f['date_emission'] ?></td>
            <td><?= $f['date_echeance'] ?? '-' ?></td>
            <td>
              <?php if ($f['statut']=='payé'): ?><span class="badge bg-success">Payé</span>
              <?php else: ?><span class="badge bg-danger">Impayé</span><?php endif; ?>
            </td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="/eaglesuite/admin/actions.php?action=facture_pdf&id=<?= $f['id'] ?>" target="_blank"><i class="bi bi-file-earmark-pdf"></i></a>

              <form method="post" action="/eaglesuite/admin/actions.php" style="display:inline">
                <input type="hidden" name="action" value="mark_facture_paid">
                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                <button class="btn btn-sm btn-success" title="Marquer payé"><i class="bi bi-check-lg"></i></button>
              </form>

              <a href="/eaglesuite/admin/actions.php?action=send_invoice&id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-envelope"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
