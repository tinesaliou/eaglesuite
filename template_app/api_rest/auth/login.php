<?php
// rest_api/auth/login.php

require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;

function login($conn) {
    // Lire le JSON reçu
    $input = json_decode(file_get_contents("php://input"), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (!$email || !$password) {
        return ["success" => false, "message" => "Email et mot de passe requis"];
    }

    // Vérifier utilisateur
    $stmt = $conn->prepare("SELECT id, nom, email, mot_de_passe, role_id, actif FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return ["success" => false, "message" => "Utilisateur introuvable"];
    }

    // Vérification du mot de passe
    $hash = $user['mot_de_passe'];
    $ok = false;
    if (preg_match('/^\$2[ayb]\$/', $hash)) {
        $ok = password_verify($password, $hash);
    } else {
        $ok = (md5($password) === $hash || hash('sha256', $password) === $hash);
    }

    if (!$ok) {
        return ["success" => false, "message" => "Identifiants incorrects"];
    }

    if (!$user['actif']) {
        return ["success" => false, "message" => "Utilisateur désactivé"];
    }

    // Création du JWT
    $secret = 'CHANGE_ME_JWT_SECRET_À_MODIFIER';
    $payload = [
        "iss" => "quincaillerie_app",
        "iat" => time(),
        "exp" => time() + (60 * 60 * 12), // 12h
        "sub" => $user['id'],
        "role_id" => $user['role_id']
    ];

    $jwt = JWT::encode($payload, $secret, 'HS256');

    return [
        "success" => true,
        "token" => $jwt,
        "user" => [
            "id" => $user['id'],
            "nom" => $user['nom'],
            "email" => $user['email'],
            "role_id" => $user['role_id']
        ]
    ];
}
