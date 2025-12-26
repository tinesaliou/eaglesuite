<?php
// rest_api/modules/admin.php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth/middleware.php';

$action = $_GET['action'] ?? 'users';

switch($action) {
    case 'users':
        $stmt = $conn->query("SELECT id, nom, email, role_id, actif FROM utilisateurs ORDER BY nom");
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    case 'get_user':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT id, nom, email, role_id, actif FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        respond(["success"=>true,"data"=>$stmt->fetch()]);
        break;

    case 'create_user':
        $user = require_auth();
        $d = input_json();
        // mot_de_passe doit être envoyé en clair; on hashe ici (bcrypt)
        $hash = password_hash($d['mot_de_passe'] ?? 'changeMe123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role_id, actif) VALUES (?, ?, ?, ?, ?)");
        $ok = $stmt->execute([
            $d['nom'] ?? '',
            $d['email'] ?? '',
            $hash,
            $d['role_id'] ?? 2,
            $d['actif'] ?? 1
        ]);
        respond(["success"=>$ok,"id"=>$conn->lastInsertId()]);
        break;

    case 'roles':
        $stmt = $conn->query("SELECT * FROM roles ORDER BY id");
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    case 'permissions':
        $stmt = $conn->query("SELECT * FROM permissions ORDER BY id");
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    case 'assign_role':
        $user = require_auth();
        $d = input_json();
        $stmt = $conn->prepare("UPDATE utilisateurs SET role_id = ? WHERE id = ?");
        $ok = $stmt->execute([$d['role_id'] ?? 2, $d['user_id'] ?? 0]);
        respond(["success"=>$ok]);
        break;

    default:
        respond(["success"=>false,"message"=>"Action inconnue"],400);
}
