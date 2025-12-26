<?php
// pages/crm/export_clients_csv.php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';
require_once __DIR__ . "/../../includes/check_auth.php";
requirePermission('clients.view');

$rows = $conn->query("SELECT idClient, nom, telephone, email, adresse, type, created_at FROM clients ORDER BY idClient")->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=clients_export_'.date('Ymd_His').'.csv');
$out = fopen('php://output','w');
fputcsv($out, ['id','nom','telephone','email','adresse','type','created_at']);
foreach($rows as $r){
  fputcsv($out, [$r['idClient'],$r['nom'],$r['telephone'],$r['email'],$r['adresse'],$r['type'],$r['created_at']]);
}
fclose($out);
exit;
