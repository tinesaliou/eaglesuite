<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (empty($_SESSION['user_id'])) exit("Non autorisé.");

$id = $_SESSION['user_id'];

// Charger mot de passe actuel
$stmt = $conn->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$hash = $stmt->fetchColumn();

// Vérifier ancien mot de passe
if (!password_verify($_POST['old_password'], $hash)) {
    header("Location: profil/profile.php?error=wrong_old");
    exit;
}

// Vérifier confirmation
if ($_POST['new_password'] !== $_POST['confirm_password']) {
    header("Location: profil/profile.php?error=confirm");
    exit;
}

// Mettre à jour
$new = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

$update = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
$update->execute([$new, $id]);

header("Location: profil/profile.php?password=ok");
