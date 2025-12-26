<?php
// pay_orangemoney.php — init Orange Money merchant
require_once __DIR__.'/../init.php';
$config = require __DIR__.'/../config/payment.php';
$base = $config['orangemoney']['base'];
$key = $config['orangemoney']['sandbox'] ? $config['orangemoney']['sandbox_api_key'] : $config['orangemoney']['prod_api_key'];

$invoiceId = intval($_GET['invoice_id'] ?? $_POST['invoice_id'] ?? 0);
if (!$invoiceId) { http_response_code(400); echo json_encode(['error'=>'missing invoice_id']); exit; }

$stmt = $masterPdo->prepare("SELECT f.*, c.societe, c.telephone FROM saas_factures f JOIN clients_saas c ON c.id=f.client_id WHERE f.id=? LIMIT 1");
$stmt->execute([$invoiceId]);
$f = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$f) { http_response_code(404); echo json_encode(['error'=>'invoice not found']); exit; }

// build merchant request according to Orange's "Merchant Payment" API
$payload = [
    'amount' => number_format($f['montant'],2,'.',''),
    'currency' => 'XOF',
    'merchantReference' => 'INV-'.$f['id'],
    'returnUrl' => 'https://your-public-domain.tld/eaglesuite/admin/index.php?page=facturation',
    'callbackUrl' => 'https://your-public-domain.tld/eaglesuite/admin/actions.php?action=webhook_payment',
    'customer' => [
        'msisdn' => preg_replace('/\D/','', $f['telephone'] ?? ''),
        'email' => $f['email'] ?? null,
        'name' => $f['societe'] ?? ''
    ]
];

$ch = curl_init($base.'/merchant/v1/checkout'); // placeholder — adapte selon doc
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "X-API-Key: $key"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$res = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http >= 200 && $http < 300) {
    $data = json_decode($res, true);
    echo json_encode(['payment_url' => $data['checkoutUrl'] ?? $data['payment_url'] ?? null, 'raw' => $data]);
} else {
    echo json_encode(['error'=>'om_request_failed','raw'=>$res,'http'=>$http]);
}
