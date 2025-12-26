<?php
require_once __DIR__ . "/../../config/db.php";
//session_start();

/* Sécurité session */
if (empty($_SESSION['user_id'])) {
    header("Location: /{{TENANT_DIR}}/index.php?page=login");
    exit;
}

/* Caisse ouverte ? */
$stmt = $conn->prepare("
    SELECT id
    FROM sessions_caisse
    WHERE utilisateur_id = ? AND statut = 'ouverte'
");
$stmt->execute([$_SESSION['user_id']]);
$session = $stmt->fetch();

/* Redirection */
if ($session) {
    header("Location: /{{TENANT_DIR}}/index.php?page=pos");
    exit;
}

header("Location: /{{TENANT_DIR}}/index.php?page=pos-select-caisse");
exit;
