<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    if (!$conn) {
        respond(["success" => false, "message" => "Erreur de connexion à la base de données"], 500);
    }

    $action = $_GET['action'] ?? 'list';

    switch ($action) {
        case 'list':
            $sql = "SELECT 
                        id, 
                        nom, 
                        code, 
                        symbole, 
                        taux_par_defaut, 
                        est_base 
                    FROM devises 
                    WHERE actif = 1 
                    ORDER BY nom ASC";

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            respond([
                "success" => true,
                "message" => count($data) > 0 ? "Devises récupérées avec succès" : "Aucune devise trouvée",
                "data" => $data
            ]);
            break;

        default:
            respond(["success" => false, "message" => "Action inconnue ou non supportée"], 400);
            break;
    }

} catch (PDOException $e) {
    respond(["success" => false, "message" => "Erreur PDO : " . $e->getMessage()], 500);
} catch (Exception $e) {
    respond(["success" => false, "message" => "Erreur serveur : " . $e->getMessage()], 500);
}
