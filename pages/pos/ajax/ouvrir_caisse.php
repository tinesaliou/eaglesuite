<?php
require_once __DIR__ . "/../../../config/db.php";
session_start();

if (!isset($_SESSION['user']['id'])) {
    die("Utilisateur non authentifié");
}

$utilisateur_id = $_SESSION['user']['id'];
$caisse_id      = (int)$_POST['caisse_id'];

/* =======================
   VERIF SESSION EXISTANTE
======================= */
$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM sessions_caisse
    WHERE utilisateur_id = ?
      AND statut = 'ouverte'
");
$stmt->execute([$utilisateur_id]);

if ($stmt->fetchColumn() > 0) {
    die("Une caisse est déjà ouverte");
}

/* =======================
   SOLDE OUVERTURE = DERNIÈRE CLÔTURE
======================= */
$stmt = $conn->prepare("
    SELECT solde_reel
    FROM sessions_caisse
    WHERE caisse_id = ?
      AND statut = 'fermee'
    ORDER BY date_cloture DESC
    LIMIT 1
");
$stmt->execute([$caisse_id]);
$dernierSolde = $stmt->fetchColumn();

$solde_ouverture = ($dernierSolde !== false) ? $dernierSolde : 0;

/* =======================
   OUVERTURE SESSION
======================= */
$stmt = $conn->prepare("
    INSERT INTO sessions_caisse
    (caisse_id, utilisateur_id, date_ouverture, solde_ouverture, statut)
    VALUES (?, ?, NOW(), ?, 'ouverte')
");
$stmt->execute([$caisse_id, $utilisateur_id, $solde_ouverture]);

header("Location: /eaglesuite/index.php?page=pos&opened=1");
exit;
