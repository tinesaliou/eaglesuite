<?php
// pay_wave.php — init checkout (Wave)
require_once __DIR__ . '/../init.php';
$config = require __DIR__ . '/../config/payment.php';
$useSandbox = $config['wave']['sandbox'];

$clientId = $useSandbox ? $config['wave']['sandbox_client_id'] : $config['wave']['prod_client_id'];
$clientSecret = $useSandbox ? $config['wave']['sandbox_client_secret'] : $config['wave']['prod_client_secret'];
$base = $useSandbox ? $config['wave']['sandbox_base'] : $config['wave']['prod_base'];

// expects invoice id (facture) from query or body
$invoiceId = intval($_GET['invoice_id'] ?? $_POST['invoice_id'] ?? 0);
if (!$invoiceId) {
    http_response_code(400);
    echo json_encode(['error'=>'missing invoice_id']);
    exit;
}

// get invoice details
$stmt = $masterPdo->prepare("SELECT f.*, c.societe, c.telephone FROM saas_factures f JOIN clients_saas c ON c.id=f.client_id WHERE f.id=? LIMIT 1");
$stmt->execute([$invoiceId]);
$f = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$f) { http_response_code(404); echo json_encode(['error'=>'invoice not found']); exit; }

// build payload to Wave (this is example / placeholder — adapt to actual Wave API)
$amount = number_format($f['montant'], 2, '.', '');
$payload = [
    'amount' => $amount,
    'currency' => 'XOF',
    'merchant_reference' => 'INV-'.$f['id'],
    'callback_url' => 'https://your-public-domain.tld/eaglesuite/admin/actions.php?action=webhook_payment',
    'redirect_url' => 'https://your-public-domain.tld/eaglesuite/admin/index.php?page=facturation'
];

// Acquire access token if Wave requires (placeholder)
// $token = getWaveToken($clientId, $clientSecret); // implement if necessary

// POST to Wave endpoint (example) — use real URL from Wave docs
$ch = curl_init($base.'/v1/checkout/create'); // placeholder path
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    //"Authorization: Bearer $token"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$res = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http >= 200 && $http < 300) {
    $data = json_decode($res, true);
    // expect a checkout_url in response
    echo json_encode(['checkout_url' => $data['checkout_url'] ?? $data['payment_url'] ?? null, 'raw'=>$data]);
} else {
    echo json_encode(['error'=>'wave_request_failed','raw'=>$res,'http'=>$http]);
}
