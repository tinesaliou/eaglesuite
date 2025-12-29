<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (empty($_SESSION['user']['id'])) exit("Non autorisé.");

$id = $_SESSION['user']['id'];

// Mise à jour nom + email
if (!empty($_POST['nom']) && !empty($_POST['email'])) {

    $stmt = $conn->prepare("UPDATE utilisateurs SET nom = ?, email = ? WHERE id = ?");
    $stmt->execute([$_POST['nom'], $_POST['email'], $id]);

    header("Location: profil/profile.php?success=1");
    exit;
}

// Upload photo
if (!empty($_FILES["photo"]["name"])) {

    $dir = __DIR__ . "/public/uploads/profiles/";
    if (!file_exists($dir)) mkdir($dir, 0777, true);

    $filename = "profile_" . $id . "_" . time() . ".jpg";
    $path = $dir . $filename;

    move_uploaded_file($_FILES["photo"]["tmp_name"], $path);

    // Supprimer ancienne photo
    $old = $conn->prepare("SELECT photo FROM utilisateurs WHERE id = ?");
    $old->execute([$id]);
    $oldPhoto = $old->fetchColumn();

    if ($oldPhoto) {
        @unlink($dir . $oldPhoto);
    }

    // Enregistrer nouvelle
    $stmt = $conn->prepare("UPDATE utilisateurs SET photo = ? WHERE id = ?");
    $stmt->execute([$filename, $id]);

    header("Location: profil/profile.php?photo=1");
    exit;
}

header("Location: profil/profile.php");
