<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/middleware.php';

$db = new Database();
$conn = $db->getConnection();


$action = $_REQUEST['action'] ?? '';

switch ($action) {

    // ✅ Vérifier le stock d’un produit dans un dépôt
    case 'check':
        $produit_id = isset($_REQUEST['produit_id']) ? intval($_REQUEST['produit_id']) : 0;
        $depot_id   = isset($_REQUEST['depot_id']) ? intval($_REQUEST['depot_id']) : 0;

        if ($produit_id <= 0 || $depot_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Paramètres manquants (produit_id ou depot_id).'
            ]);
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT quantite FROM stock_depot WHERE produit_id = ? AND depot_id = ? LIMIT 1");
            $stmt->execute([$produit_id, $depot_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                echo json_encode([
                    'success' => true,
                    'stock' => intval($row['quantite']),
                    'message' => 'Stock trouvé.'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'stock' => 0,
                    'message' => 'Aucun stock trouvé pour ce produit dans ce dépôt.'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la vérification du stock : ' . $e->getMessage()
            ]);
        }
        break;

    // ⚠️ Action par défaut
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Action non reconnue pour le module stock.'
        ]);
        break;
}
