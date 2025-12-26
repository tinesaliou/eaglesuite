<?php
require_once __DIR__ . "/../../config/db.php";

header("Content-Type: application/json");

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(["error" => "ID manquant"]);
    exit;
}

try {
    // 🔹 Retour principal
    $stmt = $conn->prepare("
        SELECT r.id, r.type, r.client_id, r.fournisseur_id, 
               r.date_retour, r.raison, r.depot_id
        FROM retours r
        WHERE r.id = ?
    ");
    $stmt->execute([$id]);
    $retour = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$retour) {
        echo json_encode(["error" => "Retour introuvable"]);
        exit;
    }

    // 🔹 Produits
    $stmt = $conn->prepare("
        SELECT rd.produit_id AS id, p.nom, rd.quantite, rd.prix_unitaire
        FROM retours_details rd
        JOIN produits p ON rd.produit_id = p.id
        WHERE rd.retour_id = ?
    ");
    $stmt->execute([$id]);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $retour["produits"] = $produits;

    echo json_encode($retour);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
