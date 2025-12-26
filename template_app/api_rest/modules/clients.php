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

        //  Liste des clients
        case 'list':
            $stmt = $conn->query("SELECT * FROM clients ORDER BY nom ASC");
            respond([
                "success" => true,
                "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);
            break;

        //  Récupérer un client
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) respond(["success" => false, "message" => "ID invalide"], 400);

            $stmt = $conn->prepare("SELECT * FROM clients WHERE idClient = ?");
            $stmt->execute([$id]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);

            respond([
                "success" => $client ? true : false,
                "data" => $client
            ]);
            break;

        //  Ajouter un client
        case 'create':
            $d = input_json();

            $stmt = $conn->prepare("
                INSERT INTO clients (nom, telephone, email, adresse, exonere, type, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $ok = $stmt->execute([
                trim($d['nom'] ?? ''),
                $d['telephone'] ?? null,
                $d['email'] ?? null,
                $d['adresse'] ?? null,
                (int)($d['exonere'] ?? 0),
                $d['type'] ?? 'Particulier'
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
                UPDATE clients 
                SET nom = ?, telephone = ?, email = ?, adresse = ?, exonere = ?, type = ?
                WHERE idClient = ?
            ");
            $ok = $stmt->execute([
                trim($d['nom'] ?? ''),
                $d['telephone'] ?? null,
                $d['email'] ?? null,
                $d['adresse'] ?? null,
                (int)($d['exonere'] ?? 0),
                $d['type'] ?? 'Particulier',
                (int)($d['idClient'] ?? 0)
            ]);

            respond(["success" => $ok]);
            break;

        //  Supprimer un client
        case 'delete':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) respond(["success" => false, "message" => "ID invalide"], 400);

            $stmt = $conn->prepare("DELETE FROM clients WHERE idClient = ?");
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
