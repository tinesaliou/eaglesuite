<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../config/db.php";

/* ==========================
   1. Utilisateur connecté
   ========================== */
if (empty($_SESSION['user_id'])) {
    header("Location: /{{TENANT_DIR}}/login.php");
    exit;
}

/* ==========================
   2. Timeout POS (10 min)
   ========================== */
$timeout = 600; // secondes

if (isset($_SESSION['pos_last_activity']) &&
    (time() - $_SESSION['pos_last_activity']) > $timeout) {

    session_unset();
    session_destroy();
    header("Location: /{{TENANT_DIR}}/index.php?page=pos-select-caisse&timeout=1");
    exit;
}

$_SESSION['pos_last_activity'] = time();

/* ==========================
   3. Caisse sélectionnée
   ========================== */
if (empty($_SESSION['pos_caisse_id'])) {
    header("Location: /{{TENANT_DIR}}/index.php?page=pos-select-caisse");
    exit;
}

$caisse_id = (int)$_SESSION['pos_caisse_id'];
$user_id   = (int)$_SESSION['user_id'];

/* ==========================
   4. Caisse active
   ========================== */
$stmt = $conn->prepare("
    SELECT actif
    FROM caisses
    WHERE id = ?
");
$stmt->execute([$caisse_id]);

if (!$stmt->fetchColumn()) {
    session_destroy();
    header("Location: /{{TENANT_DIR}}/index.php?page=pos-select-caisse&inactive=1");
    exit;
}

/* ==========================
   5. Utilisateur affecté à la caisse
   ========================== */
$stmt = $conn->prepare("
    SELECT 1
    FROM utilisateurs_caisses
    WHERE utilisateur_id = ?
      AND caisse_id = ?
");
$stmt->execute([$user_id, $caisse_id]);

if (!$stmt->fetchColumn()) {
    session_destroy();
    header("Location: /{{TENANT_DIR}}/index.php?page=pos-select-caisse&unauthorized=1");
    exit;
}

/* ==========================
   6. Session caisse ouverte
   ========================== */
$stmt = $conn->prepare("
    SELECT id
    FROM sessions_caisse
    WHERE caisse_id = ?
      AND utilisateur_id = ?
      AND statut = 'ouverte'
");
$stmt->execute([$caisse_id, $user_id]);

$session_caisse_id = $stmt->fetchColumn();

if (!$session_caisse_id) {
    header("Location: /{{TENANT_DIR}}/index.php?page=pos-open");
    exit;
}

$_SESSION['session_caisse_id'] = $session_caisse_id;
