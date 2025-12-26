<?php
// admin/includes/auth.php
//session_start();
require_once __DIR__ . "/../../config/db_master.php";

if (empty($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Charge admin courant
$stmt = $masterPdo->prepare("SELECT id,email,nom FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$CURRENT_ADMIN = $stmt->fetch();
if (!$CURRENT_ADMIN) {
    session_destroy();
    header("Location: login.php");
    exit;
}
