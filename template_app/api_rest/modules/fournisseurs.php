<?php
// api_rest/modules/clients.php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/middleware.php';

//  Connexion à la base
$db = new Database();
$conn = $db->getConnection();

//  Vérification du token (active une fois ton auth prête)
//$user = require_auth();

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {

        //  Liste des fournisseurs
        case 'list':
            $stmt = $conn->query("SELECT * FROM fournisseurs ORDER BY nom ASC");
            respond([
                "success" => true,
                "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);
            break;

        //  Récupérer un fournisseur
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) respond(["success" => false, "message" => "ID invalide"], 400);

            $stmt = $conn->prepare("SELECT * FROM fournisseurs WHERE id = ?");
            $stmt->execute([$id]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);

            respond([
                "success" => $client ? true : false,
                "data" => $client
            ]);
            break;

        //  Ajouter un fournisseur
        case 'create':
            $d = input_json();

            $stmt = $conn->prepare("
                INSERT INTO fournisseurs (nom, telephone, email, adresse, exonere, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $ok = $stmt->execute([
                trim($d['nom'] ?? ''),
                $d['telephone'] ?? null,
                $d['email'] ?? null,
                $d['adresse'] ?? null,
                (int)($d['exonere'] ?? 0)
            ]);

            respond([
                "success" => $ok,
                "id" => $conn->lastInsertId()
            ]);
            break;

        //  Modifier un client
        case 'update':
            $d = input_json();

            $stmt = $conn->prepare("
                UPDATE fournisseurs 
                SET nom = ?, telephone = ?, email = ?, adresse = ?, exonere = ?
                WHERE id = ?
            ");
            $ok = $stmt->execute([
                trim($d['nom'] ?? ''),
                $d['telephone'] ?? null,
                $d['email'] ?? null,
                $d['adresse'] ?? null,
                (int)($d['exonere'] ?? 0),
                (int)($d['id'] ?? 0)
            ]);

            respond(["success" => $ok]);
            break;

        //  Supprimer un fournisseur
        case 'delete':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) respond(["success" => false, "message" => "ID invalide"], 400);

            $stmt = $conn->prepare("DELETE FROM fournisseurs WHERE id = ?");
            $ok = $stmt->execute([$id]);

            respond(["success" => $ok]);
            break;

        default:
            respond(["success" => false, "message" => "Action inconnue"], 400);
    }

} catch (Exception $e) {
    respond([
        "success" => false,
        "message" => "Erreur serveur : " . $e->getMessage()
    ], 500);
}
