<?php
// rest_api/auth/register.php

require_once __DIR__ . '/../config/database.php';  // ou ton fichier de connexion
require_once __DIR__ . '/../vendor/autoload.php';

// Fonction d'inscription
function register($conn) {
    // Lecture du JSON reçue
    $input = json_decode(file_get_contents("php://input"), true);

    $nom = $input['nom'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (!$nom || !$email || !$password) {
        return ["success" => false, "message" => "Tous les champs sont requis"];
    }

    // Vérifier si email existe déjà
    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ["success" => false, "message" => "Email déjà utilisé"];
    }

    // Hash sécurisé
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Création de l'utilisateur
    $stmt = $conn->prepare("
        INSERT INTO utilisateurs (nom, email, mot_de_passe, role_id, actif, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    $ok = $stmt->execute([$nom, $email, $hash, 5, 1]);

    if (!$ok) {
        return ["success" => false, "message" => "Erreur lors de l'inscription"];
    }

    // Récupérer infos utilisateur
    $id = $conn->lastInsertId();
    $stmt2 = $conn->prepare("SELECT id, nom, email, role_id, actif FROM utilisateurs WHERE id = ?");
    $stmt2->execute([$id]);
    $user = $stmt2->fetch(PDO::FETCH_ASSOC);

    return [
        "success" => true,
        "utilisateur" => $user
    ];
}
