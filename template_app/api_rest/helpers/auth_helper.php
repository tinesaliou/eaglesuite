<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Vérifie et décode le token JWT envoyé dans le header Authorization
 * Retourne le payload si valide, sinon null
 */
function getUserFromToken() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        return null;
    }

    $jwt = $matches[1];
    $secret = 'CHANGE_ME_JWT_SECRET_À_MODIFIER'; // Même clé que dans login.php

    try {
        $decoded = JWT::decode($jwt, new Key($secret, 'HS256'));
        return (array) $decoded;
    } catch (Exception $e) {
        return null;
    }
}
