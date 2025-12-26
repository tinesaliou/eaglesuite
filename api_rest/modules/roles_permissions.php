<?php
// rest_api/modules/roles_permissions.php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth/middleware.php';

$action = $_GET['action'] ?? 'roles';

switch ($action) {
    case 'roles':
        $stmt = $conn->query("SELECT * FROM roles ORDER BY id");
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    case 'permissions':
        $stmt = $conn->query("SELECT * FROM permissions ORDER BY module");
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    case 'role_permissions':
        $id = intval($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT p.* FROM role_permissions rp JOIN permissions p ON rp.permission_id=p.id WHERE rp.role_id=?");
        $stmt->execute([$id]);
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    default:
        respond(["success"=>false,"message"=>"Action inconnue"],400);
}
