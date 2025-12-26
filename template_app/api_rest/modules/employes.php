<?php
// rest_api/modules/employes.php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth/middleware.php';

$action = $_GET['action'] ?? 'list';

switch($action) {
    case 'list':
        $stmt = $conn->query("SELECT * FROM employes ORDER BY nom");
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    case 'get':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM employes WHERE idEmploye = ?");
        $stmt->execute([$id]);
        respond(["success"=>true,"data"=>$stmt->fetch()]);
        break;

    case 'create':
        $user = require_auth();
        $d = input_json();
        $stmt = $conn->prepare("INSERT INTO employes (nom, poste, telephone, email, salaire, adresse) VALUES (?, ?, ?, ?, ?, ?)");
        $ok = $stmt->execute([
            $d['nom'] ?? '',
            $d['poste'] ?? null,
            $d['telephone'] ?? null,
            $d['email'] ?? null,
            $d['salaire'] ?? null,
            $d['adresse'] ?? null
        ]);
        respond(["success"=>$ok,"id"=>$conn->lastInsertId()]);
        break;

    case 'update':
        $user = require_auth();
        $d = input_json();
        $stmt = $conn->prepare("UPDATE employes SET nom=?, poste=?, telephone=?, email=?, salaire=?, adresse=? WHERE idEmploye = ?");
        $ok = $stmt->execute([
            $d['nom'] ?? '',
            $d['poste'] ?? null,
            $d['telephone'] ?? null,
            $d['email'] ?? null,
            $d['salaire'] ?? null,
            $d['adresse'] ?? null,
            $d['idEmploye'] ?? 0
        ]);
        respond(["success"=>$ok]);
        break;

    case 'delete':
        $user = require_auth();
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM employes WHERE idEmploye = ?");
        $ok = $stmt->execute([$id]);
        respond(["success"=>$ok]);
        break;

    default:
        respond(["success"=>false,"message"=>"Action inconnue"],400);
}
