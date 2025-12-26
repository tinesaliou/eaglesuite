<?php
// pages/crm/widgets.php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';
$clientsCount = $conn->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$oppsCount = $conn->query("SELECT COUNT(*) FROM crm_opportunities")->fetchColumn();
$interCount = $conn->query("SELECT COUNT(*) FROM crm_interactions")->fetchColumn();
?>
<div class="row g-2">
  <div class="col"><div class="card p-2 text-center"><h6>Clients</h6><div class="h4"><?= $clientsCount ?></div></div></div>
  <div class="col"><div class="card p-2 text-center"><h6>Opportunités</h6><div class="h4"><?= $oppsCount ?></div></div></div>
  <div class="col"><div class="card p-2 text-center"><h6>Interactions</h6><div class="h4"><?= $interCount ?></div></div></div>
</div>
