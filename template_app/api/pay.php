<?php
// Bloque tout output parasite
if (function_exists('ob_clean')) ob_clean();
header('Content-Type: application/json');

// Charger DB MASTER
require_once __DIR__ . '/../config/db_master.php'; 
global $masterPdo;

// ------------------------------
// RÉCUP PARAMÈTRES
// ------------------------------
$invoiceId = intval($_GET['invoice_id'] ?? 0);
$method    = $_GET['method'] ?? '';
$simulate  = intval($_GET['simulate'] ?? 0);

// Méthodes valides
$validMethods = ['wave','om','card'];

// ------------------------------
// VALIDATION
// ------------------------------
if ($invoiceId <= 0 || !in_array($method, $validMethods)) {
    echo json_encode([
        'ok' => false,
        'error' => 'invalid_request',
        'details' => "invoice_id={$invoiceId}, method={$method}"
    ]);
    exit;
}

// ------------------------------
// CHARGER FACTURE
// ------------------------------
$stmt = $masterPdo->prepare("
    SELECT f.*, c.societe, c.telephone, c.database_name
    FROM saas_factures f
    JOIN clients_saas c ON c.id = f.client_id
    WHERE f.id = ?
");
$stmt->execute([$invoiceId]);
$inv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inv) {
    echo json_encode(['ok'=>false, 'error'=>'invoice_not_found']);
    exit;
}

$amount   = floatval($inv['montant']);
$currency = "XOF";
$phone    = preg_replace('/\D/', '', $inv['telephone']);


// ===========================================================
// 🧪 MODE SIMULATION (LOCALHOST)
// ===========================================================
if ($simulate === 1) {

    // QR simulé
    $qr = "https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=PAY_{$invoiceId}_{$method}";

    // Code marchand simulé
    $manualCode = strtoupper($method) . "-SIMU-" . rand(10000,99999);

    // URL webhook simulée
    $fakeWebhook =
        "http://localhost/eaglesuite/admin/actions.php?action=webhook_payment" .
        "&invoice_id={$invoiceId}" .
        "&amount={$amount}" .
        "&status=SUCCESS" .
        "&method={$method}" .
        "&transaction_ref={$manualCode}";

    echo json_encode([
        'ok'          => true,
        'simulate'    => true,
        'invoice'     => $invoiceId,
        'amount'      => $amount,
        'currency'    => $currency,
        'manual_code' => $manualCode,
        'qr_url'      => $qr,

        // (pour les paiements carte)
        'checkout_url' => $method === 'card'
            ? "https://sandbox.paytech.sn/checkout/example"
            : null,

        'webhook_test_url' => $fakeWebhook
    ]);
    exit;
}


// ===========================================================
// 🚀 MODE PRODUCTION (WAVE / OM / CARD)
// ===========================================================
//
// IMPORTANT : ici on ne fait que renvoyer du "placeholder".
// C’est dans cette partie que tu connecteras les API réelles.
//
// ===========================================================


// 🌊 WAVE
if ($method === 'wave') {
    echo json_encode([
        'ok' => true,
        'message' => "Wave API non configurée (placeholder)"
    ]);
    exit;
}


// 🟧 ORANGE MONEY
if ($method === 'om') {
    echo json_encode([
        'ok' => true,
        'message' => "Orange Money API non configurée (placeholder)"
    ]);
    exit;
}


// 💳 CARTE BANCAIRE (PayTech / CinetPay)
if ($method === 'card') {
    echo json_encode([
        'ok' => true,
        'checkout_url' => "https://sandbox.paytech.sn/checkout/example"
    ]);
    exit;
}


// Méthode inconnue
echo json_encode(['ok'=>false,'error'=>'unknown_method']);
exit;
