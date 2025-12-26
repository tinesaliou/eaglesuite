<?php
require_once __DIR__ . '/../../config/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM fournisseurs WHERE id = ?");
    $ok = $stmt->execute([$id]);

    if ($ok) {
        header("Location: /{{TENANT_DIR}}/index.php?page=fournisseurs");
        exit;
    } else {
        die("Erreur lors de la suppression");
    }
} else {
    die("❌ ID invalide");
}
