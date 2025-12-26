<?php
// admin/api/mobilemoney_push.php
require_once __DIR__ . '/../init.php'; // adapte selon emplacement
header('Content-Type: application/json; charset=utf-8');

$invoiceId = intval($_GET['invoice_id'] ?? 0);
$method    = $_GET['method'] ?? '';
$simulate  = isset($_GET['simulate']) && $_GET['simulate']=='1';

// validation
if (!$invoiceId || !in_array($method,['wave','om','card'])) {
    echo json_encode(['error'=>'invalid_request']);
    exit;
}

// fetch facture + client info (master DB)
$stmt = $masterPdo->prepare("
    SELECT f.*, c.societe, c.telephone, c.subdomain, c.database_name
    FROM saas_factures f
    JOIN clients_saas c ON c.id = f.client_id
    WHERE f.id = ?
");
$stmt->execute([$invoiceId]);
$inv = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$inv) { echo json_encode(['error'=>'invoice_not_found']); exit; }

$phone = preg_replace('/\D/','',$inv['telephone'] ?? '');
$amount = floatval($inv['montant']);

// simulate local environment
if ($simulate) {
    // log and return fake response
    file_put_contents(__DIR__.'/../../mobile_sim.log', date('c')." SIMULATE {$method} invoice={$invoiceId}\n", FILE_APPEND);
    echo json_encode([
        'ok'=>true,
        'message'=>"Simulation : push {$method} envoyé au {$phone} pour {$amount} XOF. Webhook simulable via actions.php?action=webhook_payment&test=1&invoice={$invoiceId}"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/*
  Ici, tu intégreras les vraies requêtes HTTP vers l'API du provider.
  Exemple (pseudo) :
    - Wave : POST https://api.wave.com/v1/push
    - Orange Money : POST https://api.orange.sn/push
    - Card : redirection vers checkout (checkout_url)
  Chaque provider te renverra un objet JSON avec un statut et éventuellement un checkout_url.
  IMPORTANT : Fourni par le provider -> callback_url = webhook (public)
*/

// EXEMPLE GÉNÉRIQUE (PLACEHOLDERS) : retourne "demande envoyée"
$response = [
    'ok' => true,
    'provider' => $method,
    'message' => "Demande de paiement envoyée à {$phone} (opérateur={$method}) — montant {$amount} XOF",
];

// Si provider renvoie checkout_url (carte), inclure:
// $response['checkout_url'] = 'https://...';

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
