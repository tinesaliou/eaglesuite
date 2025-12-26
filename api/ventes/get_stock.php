<?php
require_once '../../config/db.php'; // adapte le chemin

$produit_id = intval($_GET['produit_id'] ?? 0);
$depot_id = intval($_GET['depot_id'] ?? 0);

if ($produit_id && $depot_id) {
    // Adapte la requête à ta table stock_depot / stocks réelle
    $stmt = $conn->prepare("SELECT sum(quantite) AS quantite FROM stock_depot WHERE produit_id = ? AND depot_id = ? LIMIT 1");
    $stmt->execute([$produit_id, $depot_id]);
    $stock = $stmt->fetchColumn();
    $stock = $stock === false ? 0 : (int)$stock;
    echo json_encode(['stock' => $stock]);
} else {
    echo json_encode(['stock' => 0]);
}
