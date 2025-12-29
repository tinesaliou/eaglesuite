<?php
require_once __DIR__ . "/../../config/db.php";
//session_start();

/* Sécurité session */
if (empty($_SESSION['user']['id'])) {
    header("Location: /eaglesuite/index.php?page=login");
    exit;
}

/* Caisse ouverte ? */
$stmt = $conn->prepare("
    SELECT id
    FROM sessions_caisse
    WHERE utilisateur_id = ? AND statut = 'ouverte'
");
$stmt->execute([$_SESSION['user']['id']]);
$session = $stmt->fetch();

/* Redirection */
if ($session) {
    header("Location: /eaglesuite/index.php?page=pos");
    exit;
}

header("Location: /eaglesuite/index.php?page=pos-select-caisse");
exit;
