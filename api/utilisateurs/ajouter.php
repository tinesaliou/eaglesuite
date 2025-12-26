<?php
require_once "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom          = trim($_POST['nom'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');
    $entreprise_id = intval($_POST['entreprise_id'] ?? 1);
    $actif        = intval($_POST['actif'] ?? 1);
    $role_id = $_POST['role_id'] ?? null;


    if (empty($nom) || empty($email) || empty($mot_de_passe)) {
        die("❌ Tous les champs obligatoires doivent être remplis.");
    }

    // Hash du mot de passe
    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO utilisateurs (nom, email, mot_de_passe, entreprise_id, actif,role_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$nom, $email, $hash, $entreprise_id, $actif, $role_id]);

    // Redirection vers l’onglet Utilisateurs
    header("Location: /eaglesuite/index.php?page=utilisateurs");
    exit;
}
