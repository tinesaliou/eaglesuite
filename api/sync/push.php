<?php
// api/sync_push.php
require_once __DIR__.'/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || !isset($body['device_id']) || !isset($body['ops'])) {
    http_response_code(400);
    echo json_encode(['error'=>'Payload invalide']);
    exit;
}

$device_id = $body['device_id'];
$ops = $body['ops']; // array of operations
$results = [];
try {
    $conn->beginTransaction();
    foreach ($ops as $op) {
        // op: { temp_id, action, table, payload }
        $temp_id = $op['temp_id'] ?? null;
        $action = $op['operation'];
        $table = $op['table'];
        $payload = $op['payload'];

        // Persist in sync_queue for audit
        $stmtQ = $conn->prepare("INSERT INTO sync_log (device_id, operation, table_name, payload) VALUES (?, ?, ?, ?)");
        $stmtQ->execute([$device_id, $action, $table, json_encode($payload)]);

        // Basic examples: handle ventes create
        if ($table === 'ventes' && $action === 'create') {
            // Insert vente + lignes + update stock (VERY IMPORTANT: validate stock)
            // Exemple simplifié — assume payload has client_id, date_vente, total, mode_paiement, type_vente, statut, lignes[]
            $pv = $payload;
            // insert ventes
            $stmt = $conn->prepare("INSERT INTO ventes (client_id, date_vente, total, mode_paiement, type_vente, statut, utilisateur_id, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$pv['client_id'] ?: null, $pv['date_vente'] ?: date('Y-m-d'), $pv['total'], $pv['mode_paiement'] ?? null, $pv['type_vente'] ?? 'Comptant', $pv['statut'] ?? 'Payé', $pv['utilisateur_id'] ?: null, $pv['note'] ?? null]);
            $new_id = $conn->lastInsertId();

            // insert lignes and update stock_depot + mouvements_stock
            $stmtL = $conn->prepare("INSERT INTO ventes_lignes (vente_id, produit_id, quantite, prix_unitaire, depot_id) VALUES (?, ?, ?, ?, ?)");
            $stmtStock = $conn->prepare("UPDATE stock_depot SET quantite = quantite - ? WHERE produit_id = ? AND depot_id = ?");
            $stmtMov = $conn->prepare("INSERT INTO mouvements_stock (produit_id, depot_source_id, quantite, type, reference_table, reference_id, utilisateur_id, note) VALUES (?, ?, ?, 'vente', 'ventes', ?, ?, ?)");

            foreach ($pv['lignes'] as $l) {
                // validate stock
                $row = $conn->prepare("SELECT quantite FROM stock_depot WHERE produit_id = ? AND depot_id = ?");
                $row->execute([$l['produit_id'], $l['depot_id']]);
                $st = $row->fetch();
                if (!$st || $st['quantite'] < $l['quantite']) {
                    // conflict -> record and return error for this op
                    $conn->rollBack();
                    // insert conflict
                    $stmtC = $conn->prepare("INSERT INTO sync_conflicts (device_id, table_name, record_id, server_copy, client_copy, reason) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmtC->execute([$device_id, 'stock_depot', $l['produit_id'], json_encode($st), json_encode($l), 'Stock insuffisant']);
                    http_response_code(409);
                    echo json_encode(['error'=>'Stock insuffisant pour produit '.$l['produit_id']]);
                    exit;
                }

                $stmtL->execute([$new_id, $l['produit_id'], $l['quantite'], $l['prix_unitaire'], $l['depot_id']]);
                $stmtStock->execute([$l['quantite'], $l['produit_id'], $l['depot_id']]);
                $stmtMov->execute([$l['produit_id'], $l['depot_id'], $l['quantite'], $new_id, $pv['utilisateur_id'] ?? null, 'Vente offline']);
            }
            $results[] = ['temp_id'=>$temp_id, 'new_id'=>$new_id, 'status'=>'ok'];
        } else {
            // For other tables: implement similar logic or respond unsupported
            $results[] = ['temp_id'=>$temp_id, 'status'=>'ignored', 'note'=>'Pas de handler pour '.$table];
        }
    }
    $conn->commit();
    // mark device last sync
    $stmtDev = $conn->prepare("INSERT INTO device_sync (device_id, last_sync) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_sync = NOW()");
    $stmtDev->execute([$device_id]);
    echo json_encode(['status'=>'ok','results'=>$results]);
} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
