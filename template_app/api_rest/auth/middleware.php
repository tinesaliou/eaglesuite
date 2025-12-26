<?php
// 🔒 middleware.php — Sécurité & Autorisation JWT pour ton API ERP
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * 🔐 Récupère le header Authorization (compatible Apache, Nginx, PHP intégré)
 */
function getAuthorizationHeader() {
    if (isset($_SERVER['Authorization'])) {
        return trim($_SERVER["Authorization"]);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                return trim($value);
            }
        }
    }
    return null;
}

/**
 * ✅ Vérifie le token JWT et retourne les infos utilisateur
 * Arrête le script si le token est invalide ou expiré
 */
function require_auth() {
    $authHeader = getAuthorizationHeader();

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Token manquant"]);
        exit;
    }

    $token = $matches[1];
    $secret = 'CHANGE_ME_JWT_SECRET_À_MODIFIER'; // ⚠️ Change ce secret dans ton projet

    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        return (array)$decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Token invalide ou expiré",
            "error" => $e->getMessage()
        ]);
        exit;
    }
}

/**
 * 🧩 Vérifie un token sans bloquer le script (optionnel)
 */
function verifyToken() {
    $authHeader = getAuthorizationHeader();
    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        return null;
    }

    $token = $matches[1];
    $secret = 'CHANGE_ME_JWT_SECRET_À_MODIFIER';

    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        return (array)$decoded;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * 🛡️ Middleware d’autorisation par rôle
 * Exemple d’utilisation :
 *    $user = require_role(['admin', 'gestionnaire']);
 */
function require_role(array $rolesAutorises) {
    $decoded = require_auth();

    // Le JWT contient déjà "role_id"
    $role_id = $decoded['role_id'] ?? null;

    if (!$role_id) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Rôle non défini"]);
        exit;
    }

    // Si les rôles sont numériques (id)
    if (in_array($role_id, $rolesAutorises)) {
        return $decoded;
    }

    // Si les rôles sont textuels (ex: "admin")
    if (in_array(strtolower($role_id), array_map('strtolower', $rolesAutorises))) {
        return $decoded;
    }

    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Accès refusé. Rôle non autorisé.",
        "role_actuel" => $role_id,
        "roles_autorises" => $rolesAutorises
    ]);
    exit;
}
