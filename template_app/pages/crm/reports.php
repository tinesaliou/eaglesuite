<?php
// pages/crm/reports.php
require_once __DIR__ . "/../../config/db.php";

require_once __DIR__ . '/../../config/check_access.php';
require_once __DIR__ . "/../../includes/check_auth.php";

requirePermission('clients.view');

// exemple: export opportunités en xls (HTML -> xls)
$format = $_GET['format'] ?? '';
if($format === 'opps_xls'){
  $rows = $conn->query("SELECT o.*, c.nom AS client_nom FROM crm_opportunities o LEFT JOIN clients c ON o.client_id = c.idClient")->fetchAll(PDO::FETCH_ASSOC);
  header("Content-Type: application/vnd.ms-excel; charset=utf-8");
  header("Content-Disposition: attachment; filename=opportunities_".date('Ymd').".xls");
  echo "<table border='1'><tr><th>ID</th><th>Titre</th><th>Client</th><th>Montant</th><th>Etat</th></tr>";
  foreach($rows as $r){
    echo "<tr><td>{$r['id']}</td><td>".htmlspecialchars($r['titre'], ENT_QUOTES, 'UTF-8')."</td><td>".htmlspecialchars($r['client_nom'], ENT_QUOTES, 'UTF-8')."</td><td>{$r['montant']}</td><td>{$r['etat']}</td></tr>";
  }
  echo "</table>"; exit;
}

include __DIR__ . "/../../includes/layout.php";
?>
<div class="container-fluid">
  <h1 class="h4">Rapports CRM</h1>
  <a class="btn btn-sm btn-outline-secondary" href="/{{TENANT_DIR}}/index.php?page=crm_reports&format=opps_xls">Exporter opportunités (XLS)</a>
</div>
<?php include __DIR__ . "/../../includes/layout_end.php"; ?>
