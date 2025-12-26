<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Méthode non autorisée";
    exit;
}

$id   = intval($_POST['id'] ?? 0);
$nom  = trim($_POST['nom'] ?? '');
$desc = trim($_POST['description'] ?? '');
$perms = $_POST['permissions'] ?? [];

if ($id <= 0 || $nom === '') {
    http_response_code(400);
    echo "Paramètres invalides";
    exit;
}

try {
    $conn->beginTransaction();

    // 1. Mise à jour du rôle
    $stmt = $conn->prepare("UPDATE roles SET nom = ?, description = ? WHERE id = ?");
    $stmt->execute([$nom, $desc, $id]);

    // 2. Suppression des anciennes permissions
    $conn->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$id]);

    // Exemple d'insertion role_permissions
    foreach ($permissions as $code) {
        $stmt = $conn->prepare("SELECT id FROM permissions WHERE code = ?");
        $stmt->execute([$code]);
        $permission_id = $stmt->fetchColumn();

        if ($permission_id) {
            $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $stmt->execute([$role_id, $permission_id]);
        }
    }


    $conn->commit();
    header("Location: /eaglesuite/index.php?page=roles");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo "Erreur : " . $e->getMessage();
    exit;
}
