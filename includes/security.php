<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';


function loadUserPermissions() {
    global $conn;

    if (!isset($_SESSION['user']['id'])) {
        return;
    }

    // Récupérer le rôle de l’utilisateur
    $stmt = $conn->prepare("SELECT role_id FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || empty($user['role_id'])) {
        return;
    }

    $role_id = $user['role_id'];

    // Charger toutes les permissions liées à ce rôle
    $stmt = $conn->prepare("
        SELECT p.code
        FROM permissions p
        INNER JOIN role_permissions rp ON rp.permission_id = p.id
        WHERE rp.role_id = ?
    ");
    $stmt->execute([$role_id]);
    $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Mise en cache des permissions dans la session
    $_SESSION['permissions'] = [];
    foreach ($permissions as $code) {
        $_SESSION['permissions'][$code] = true;
    }
}

/**
 *  Vérifie si l'utilisateur connecté possède une permission spécifique
 */
function checkPermission($code) {
    if (!isset($_SESSION['user']['id'])) {
        return false;
    }

    // Charger les permissions si pas encore fait
    if (!isset($_SESSION['permissions'])) {
        loadUserPermissions();
    }

    return isset($_SESSION['permissions'][$code]) && $_SESSION['permissions'][$code] === true;
}

/**
 *  Vérifie si l'utilisateur possède au moins une des permissions données
 */
function hasAnyPermission($codes) {
    if (!isset($_SESSION['permissions'])) {
        loadUserPermissions();
    }

    if (!is_array($codes)) {
        $codes = [$codes];
    }

    foreach ($codes as $code) {
        if (isset($_SESSION['permissions'][$code]) && $_SESSION['permissions'][$code] === true) {
            return true;
        }
    }

    return false;
}

/**
 *  Vérifie si l'utilisateur possède toutes les permissions données
 */
function hasAllPermissions($codes) {
    if (!isset($_SESSION['permissions'])) {
        loadUserPermissions();
    }

    if (!is_array($codes)) {
        $codes = [$codes];
    }

    foreach ($codes as $code) {
        if (!isset($_SESSION['permissions'][$code]) || $_SESSION['permissions'][$code] !== true) {
            return false;
        }
    }

    return true;
}

/**
 *  Redirige automatiquement si l'utilisateur n'a pas la permission requise
 */
function requirePermission($code) {
    if (!isset($_SESSION['user']['id'])) {
        header("Location: /eaglesuite/login.php");
        exit;
    }

    if (!checkPermission($code)) {
        header("Location: /eaglesuite/access_denied.php");
        exit;
    }
}
