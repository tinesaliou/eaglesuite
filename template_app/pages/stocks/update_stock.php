<?php
// pages/inventaire/update_stock.php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /{{TENANT_DIR}}/index.php?page=inventaire");
    exit;
}

$stock_id = !empty($_POST['stock_id']) ? intval($_POST['stock_id']) : null;
$produit_id = intval($_POST['produit_id'] ?? 0);
$depot_id = intval($_POST['depot_id'] ?? 0);
$qty_physique = intval($_POST['qty_physique'] ?? 0);
$note = trim($_POST['note'] ?? '');
$type = trim($_POST['type'] ?? 'ajustement');

if (!$produit_id || !$depot_id) {
    $_SESSION['flash_error'] = "Produit ou dépôt invalide.";
    header("Location: /{{TENANT_DIR}}/index.php?page=inventaire");
    exit;
}

try {
    $conn->beginTransaction();

    // Récupérer stock ERP courant (si stock_id fourni)
    if ($stock_id) {
        $stmt = $conn->prepare("SELECT id, quantite FROM stock_depot WHERE id = ? FOR UPDATE");
        $stmt->execute([$stock_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Chercher par produit+depot
        $stmt = $conn->prepare("SELECT id, quantite FROM stock_depot WHERE produit_id = ? AND depot_id = ? FOR UPDATE");
        $stmt->execute([$produit_id, $depot_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($row) {
        $old_qty = (int)$row['quantite'];
        $stockId = (int)$row['id'];
        // mise à jour
        $stmt = $conn->prepare("UPDATE stock_depot SET quantite = ? WHERE id = ?");
        $stmt->execute([$qty_physique, $stockId]);
    } else {
        // créer nouvelle ligne
        $old_qty = 0;
        $stmt = $conn->prepare("INSERT INTO stock_depot (produit_id, depot_id, quantite) VALUES (?, ?, ?)");
        $stmt->execute([$produit_id, $depot_id, $qty_physique]);
        $stockId = $conn->lastInsertId();
    }

    // calcul écart et type mouvement
    $delta = $qty_physique - $old_qty;
    // Choisir un type lisible pour mouvements_stock ; si delta>0 => 'ajustement_entree' else 'ajustement_sortie'
    $m_type = $delta > 0 ? 'ajustement_entree' : ($delta < 0 ? 'ajustement_sortie' : 'ajustement');

    // Insérer dans mouvements_stock
    $stmt = $conn->prepare("
        INSERT INTO mouvements_stock 
            (produit_id, depot_source_id, depot_dest_id, quantite, type, reference_table, reference_id, utilisateur_id, note, date_mouvement)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    // pour un ajustement : depot_source_id = depot_id si sortie, depot_dest_id = depot_id si entrée
    $depot_source_id = $delta < 0 ? $depot_id : null;
    $depot_dest_id = $delta > 0 ? $depot_id : null;
    $ref_table = 'inventaire';
    $ref_id = $stockId;
    $utilisateur_id = $_SESSION['user_id'];

    $stmt->execute([
        $produit_id,
        $depot_source_id,
        $depot_dest_id,
        abs($delta),
        $m_type,
        $ref_table,
        $ref_id,
        $utilisateur_id,
        $note
    ]);

    $conn->commit();

    $_SESSION['flash_success'] = "Ajustement enregistré (écart : " . ($delta) . ").";
    header("Location: /{{TENANT_DIR}}/index.php?page=inventaire");
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Erreur update_stock.php : " . $e->getMessage());
    $_SESSION['flash_error'] = "Erreur lors de l'enregistrement. Voir logs.";
    header("Location: /{{TENANT_DIR}}/index.php?page=inventaire");
    exit;
}
