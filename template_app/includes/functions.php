<?php
function hasPermission(PDO $conn, $userId, $permissionSlug) {
    $sql = "SELECT 1 FROM utilisateurs u
            JOIN utilisateur_roles ur ON ur.utilisateur_id = u.id
            JOIN role_permissions rp ON rp.role_id = ur.role_id
            JOIN permissions p ON p.id = rp.permission_id
            WHERE u.id = ? AND p.slug = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId, $permissionSlug]);
    return (bool)$stmt->fetchColumn();
}

function requirePermission(PDO $conn, $userId, $permissionSlug) {
    if (!hasPermission($conn, $userId, $permissionSlug)) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }
}

function getSetting(PDO $conn, $groupe, $cle=null) {
    if ($cle) {
        $stmt = $conn->prepare("SELECT valeur FROM settings WHERE groupe = ? AND cle = ? AND actif = 1 LIMIT 1");
        $stmt->execute([$groupe, $cle]);
        return $stmt->fetchColumn();
    } else {
        $stmt = $conn->prepare("SELECT * FROM settings WHERE groupe = ? AND actif = 1");
        $stmt->execute([$groupe]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function logAction(PDO $conn, $userId, $action, $objType = null, $objId = null, $details = null) {
    $stmt = $conn->prepare("INSERT INTO audit_logs (utilisateur_id, action, objet_type, objet_id, details, ip) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $objType, $objId, $details, $_SERVER['REMOTE_ADDR']]);
}
?>
