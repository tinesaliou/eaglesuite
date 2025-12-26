<?php
require_once __DIR__ . "/../../config/db.php";
//session_start();

if (empty($_SESSION['pos_caisse_pending'])) {
    header("Location: /{{TENANT_DIR}}/index.php?page=pos-entry");
    exit;
}

$caisse_id = $_SESSION['pos_caisse_pending'];
$utilisateur_id = $_SESSION['user_id'];
$solde_ouverture = floatval($_POST['solde_ouverture'] ?? 0);

$stmt = $conn->prepare("
    INSERT INTO sessions_caisse
    (caisse_id, utilisateur_id, date_ouverture, solde_ouverture, statut)
    VALUES (?, ?, NOW(), ?, 'ouverte')
");
$stmt->execute([$caisse_id, $utilisateur_id, $solde_ouverture]);

unset($_SESSION['pos_caisse_pending']);

header("Location: /{{TENANT_DIR}}/index.php?page=pos");
exit;
