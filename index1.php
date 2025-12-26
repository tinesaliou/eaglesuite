<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/check_auth.php"; 

//require_login(); // 🔐 doit être connecté

$page = $_GET['page'] ?? 'dashboard';

// Liste des pages autorisées
$allowedPages = [
    'dashboard'        => 'pages/dashboard.php',
    'produits'         => 'pages/produits/produits.php',
    'categories'       => 'pages/categories/categories.php',
    'ventes'           => 'pages/ventes/ventes.php',
    'achats'           => 'pages/achats/achats.php',
    'creances'         => 'pages/creances/creances.php',
    'dettes'           => 'pages/dettes/dettes.php',
    'depots'           => 'pages/depots/depots.php',
    'retours'          => 'pages/retours/retours.php',
    'clients'          => 'pages/clients/clients.php',
    'tresorerie'       => 'pages/tresorerie/tresorerie.php',
    'caisse_especes'   => 'pages/caisses/caisse.php',
    'caisse_banque'    => 'pages/caisses/caisse.php',
    'caisse_mobile'    => 'pages/caisses/caisse.php',
    'autres_operations'=> 'pages/tresorerie/autres_operations.php',
    'fournisseurs'     => 'pages/fournisseurs/fournisseurs.php',
    'parametres'       => 'pages/parametres/parametres.php',
    'utilisateurs'     => 'pages/utilisateurs/utilisateurs.php',
    'roles'            => 'pages/roles/roles.php',
    'rapports_achats'  => 'pages/rapports/rapports_achats.php',
    'rapports_ventes'  => 'pages/rapports/rapports_ventes.php',
    'rapports_caisse'  => 'pages/rapports/rapports_caisse.php',
    'rapports_stocks'  => 'pages/rapports/rapports_stocks.php'
];

if (!isset($allowedPages[$page])) {
    $contentFile = "pages/404.php";
} else {

    // 🔐 sécurité : vérifier la permission associée
    if (!check_page_permission($page)) {

        if (!headers_sent()) {
            header("Location: /{{TENANT_DIR}}/access_denied.php");
        } else {
            //echo "<script>window.location='/{{TENANT_DIR}}/access_denied.php';</script>";
        }
        exit;
    }

    $contentFile = $allowedPages[$page];
}

include __DIR__ . "/includes/layout.php";
include __DIR__ . "/" . $contentFile;
