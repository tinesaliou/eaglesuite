<?php
require_once "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id=?");
        $stmt->execute([$id]);
    }

    header("Location: /{{TENANT_DIR}}/index.php?page=utilisateurs");
    exit;
}
