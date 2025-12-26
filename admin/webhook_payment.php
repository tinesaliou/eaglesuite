<?php
// admin/webhook_payment.php
require_once __DIR__ . '/init.php'; // récupère $masterPdo et config
require_once __DIR__ . '/lib/whatsapp.php'; // si exists

// Lire JSON brut
$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload) {
    // support GET test ?test=1&invoice=...
    if (isset($_GET['test']) && isset($_GET['invoice'])) {
        $payload = [
            'invoice_id' => intval($_GET['invoice']),
            'amount' => floatval($_GET['amount'] ?? 0),
            'status' => 'SUCCESS',
            'transaction_ref' => 'TEST-LOCAL-'.time(),
            'method' => $_GET['method'] ?? 'simulate'
        ];
    } else {
        http_response_code(400);
        echo "INVALID_PAYLOAD";
        exit;
    }
}

$invoiceId = intval($payload['invoice_id'] ?? 0);
$status    = strtoupper($payload['status'] ?? '');
$amount     = floatval($payload['amount'] ?? 0);
$ref        = $payload['transaction_ref'] ?? null;
$method     = $payload['method'] ?? null;

if (!$invoiceId || !$status) { http_response_code(400); echo "INVALID_PAYLOAD"; exit; }

if ($status === 'SUCCESS' || $status === 'COMPLETED' || $status === 'PAID') {
    try {
        // 1) update facture
        $stmt = $masterPdo->prepare("UPDATE saas_factures SET statut='payé', ref_paiement=?, mode_paiement=?, date_paiement=NOW() WHERE id=?");
        $stmt->execute([$ref, $method, $invoiceId]);

        // 2) insert paiement log
        $stmt = $masterPdo->prepare("INSERT INTO saas_paiements (facture_id, montant, ref, methode, date_creation) VALUES (?,?,?,?,NOW())");
        $stmt->execute([$invoiceId, $amount, $ref, $method]);

        // 3) fetch facture + client
        $stmt = $masterPdo->prepare("SELECT f.*, c.societe, c.telephone, c.database_name FROM saas_factures f JOIN clients_saas c ON c.id=f.client_id WHERE f.id=?");
        $stmt->execute([$invoiceId]);
        $fact = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4) send WhatsApp if phone present
        if (!empty($fact['telephone'])) {
            try {
                sendWhatsApp($fact['telephone'], "Bonjour {$fact['societe']}, paiement reçu pour facture #{$invoiceId} ({$amount} XOF). Réf: {$ref}");
            } catch (Exception $ex) { /* ignore */ }
        }

        // 5) Insert notification in tenant DB
        if (!empty($fact['database_name'])) {

            try {

                // Récupérer user/password du client
                $stmt2 = $masterPdo->prepare("
                    SELECT database_user, database_password
                    FROM clients_saas
                    WHERE database_name = ?
                    LIMIT 1
                ");
                $stmt2->execute([$fact['database_name']]);
                $tenantCreds = $stmt2->fetch(PDO::FETCH_ASSOC);

                if ($tenantCreds) {

                    $tenantUser = $tenantCreds['database_user'];
                    $tenantPass = $tenantCreds['database_password'];

                    // Se connecter à la DB cliente
                    $tenantPdo = new PDO(
                        "mysql:host={$MASTER_DB_HOST};dbname={$fact['database_name']};charset=utf8mb4",
                        $tenantUser,
                        $tenantPass,
                        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
                    );

                    // Insert notification
                    $tenantPdo->prepare("
                        INSERT INTO notifications (title, message, type, created_at)
                        VALUES ('Paiement reçu', ?, 'success', NOW())
                    ")->execute(["Votre facture #{$invoiceId} a été réglée."]);
                }

            } catch (Exception $e) {
                // log + ignore pour éviter de casser le webhook
                file_put_contents(__DIR__.'/webhook_error.log', $e->getMessage()."\n", FILE_APPEND);
            }
        }


        http_response_code(200);
        echo "OK_PAID";
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo "ERROR: ".$e->getMessage();
        exit;
    }
}

http_response_code(200);
echo "IGNORED";
exit;
