<?php

require_once __DIR__ . '/../config/db.php';

/* -------------------------
   CHARGER LES PERMISSIONS
--------------------------*/
function loadUserPermissions() {
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $stmt = $conn->prepare("SELECT role_id FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u || !$u['role_id']) {
        $_SESSION['permissions'] = [];
        return;
    }

    $stmt = $conn->prepare("
        SELECT p.code
        FROM role_permissions rp
        JOIN permissions p ON rp.permission_id = p.id
        WHERE rp.role_id = ?
    ");
    $stmt->execute([$u['role_id']]);
    $perms = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $_SESSION['permissions'] = [];
     
    foreach ($perms as $code) {
        $_SESSION['permissions'][$code] = true;
    }
}


/* -------------------------
   VÉRIFIER UNE PERMISSION
--------------------------*/
function checkPermission($code) {
    if (!isset($_SESSION['user_id'])) return false;
    if (!isset($_SESSION['permissions'])) loadUserPermissions();
    return isset($_SESSION['permissions'][$code]);
}

/* ------------------------------------------
   EXIGER UNE PERMISSION AVANT DE CHARGER LA PAGE
---------------------------------------------*/
function requirePermission($code) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /{{TENANT_DIR}}/login.php");
        exit;
    }

    if (!checkPermission($code)) {
        header("Location: /{{TENANT_DIR}}/access_denied.php");
        exit;
    }
}

/* ------------------------------------------
   MAP PAGE → PERMISSION
---------------------------------------------*/
function check_page_permission($page) {

    $map = [

        'dashboard'        => 'dashboard.view',

        'produits'         => 'produits.view',
        'categories'       => 'categories.view',
        'ventes'           => 'ventes.view',
        'achats'           => 'achats.view',
        'creances'         => 'creances.view',
        'dettes'           => 'dettes.view',
        'depots'           => 'depots.view',
        'retours'          => 'retours.view',
        'clients'          => 'clients.view',
        'tresorerie'       => 'tresorerie.view',

        'caisse_especes'   => 'caisse.especes.view',
        'caisse_banque'    => 'caisse.banque.view',
        'caisse_mobile'    => 'caisse.mobile.view',
        'autres_operations'=> 'operations.autres.view',

        'fournisseurs'     => 'fournisseurs.view',
        'parametres'       => 'settings.view',
        'utilisateurs'     => 'users.manage',
        'roles'            => 'roles.manage',

        'rapports_achats'  => 'rapports.achats.view',
        'rapports_ventes'  => 'rapports.ventes.view',
        'rapports_caisse'  => 'rapports.caisse.view',
        'rapports_stocks'  => 'rapports.stocks.view',

        'crm_dashboard'        => 'crm.dashboard.view',
        'crm_clients'          => 'crm.clients.view',
        'crm_client'           => 'crm.clients.view',
        'crm_interactions'     => 'crm.interactions.view',
        'crm_add_interaction'  => 'crm.interactions.manage',
        'crm_opportunites'     => 'crm.opportunites.view',
        'crm_export_clients'   => 'crm.clients.view'

    ];

    if (!isset($map[$page])) { return true; }

    requirePermission($map[$page]);
}

/* --------------------------------------------------------
   Vérifie si l'utilisateur possède AU MOINS une permission
---------------------------------------------------------*/
function hasAnyPermission(array $code) {

    if (!isset($_SESSION['permissions'])) {
        loadUserPermissions();
    }

    foreach ($code as $perm) {
        if (isset($_SESSION['permissions'][$perm]) && $_SESSION['permissions'][$perm] === true) {
            return true;
        }
    }
    return false;
}

/* --------------------------------------------------------
   Vérifie si l'utilisateur possède TOUTES les permissions
---------------------------------------------------------*/
function hasAllPermission(array $code) {

    if (!isset($_SESSION['permissions'])) {
        loadUserPermissions();
    }

    foreach ($code as $perm) {
        if (!isset($_SESSION['permissions'][$perm])) {
            return false;
        }
    }
    return true;
}
