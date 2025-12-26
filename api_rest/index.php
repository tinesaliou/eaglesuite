<?php
// rest_api/index.php

header('Content-Type: application/json');
require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers.php';

// Charger automatiquement les dépendances Composer (JWT)
require_once __DIR__ . '/vendor/autoload.php';

// Récupérer les paramètres GET
$module = $_GET['module'] ?? null;
$action = $_GET['action'] ?? null;

// Vérification de base
if (!$module || !$action) {
    echo json_encode(["success" => false, "message" => "Paramètres module/action requis"]);
    exit;
}

// Cas spécial pour l’authentification
if ($module === 'auth') {
    $file = __DIR__ . "/auth/$action.php";
} else {
    $file = __DIR__ . "/modules/$module.php";
}

// Vérifie si le fichier du module existe
if (!file_exists($file)) {
    echo json_encode(["success" => false, "message" => "Module introuvable: $module"]);
    exit;
}

// Inclure la base de données
$db = new Database();
$conn = $db->getConnection();

// Charger le middleware JWT sauf pour le module auth
if ($module !== 'auth') {
    require_once __DIR__ . '/auth/middleware.php';
    $userData = verifyToken(); // Retourne les infos du user connecté
}

// Inclure le module
require_once $file;

// Vérifie si l’action existe dans le module
if (!function_exists($action)) {
    echo json_encode(["success" => false, "message" => "Action introuvable: $action"]);
    exit;
}

// Exécute la fonction du module
try {
    $response = $action($conn, $userData ?? null);
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
