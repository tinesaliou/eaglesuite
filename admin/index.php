
<?php
//require_once __DIR__ . "/../config/db_master.php";
// /eaglesuite/admin/index.php
require_once __DIR__ . "/init.php"; 
require_admin();

$page = $_GET['page'] ?? 'dashboard';

$allowedPages = [
    'dashboard'     => 'pages/dashboard.php',
    'clients'       => 'pages/clients.php',
    'abonnements'   => 'pages/abonnements.php',
    'facturation'   => 'pages/facturation.php',
    'renouvellement'=> 'pages/renouvellement.php',
    'suspensions'    => 'pages/suspensions.php',
    'paiement'      => 'pages/paiements.php',
    'client_new'    => 'pages/client_new.php',
    'client_edit'   => 'pages/client_edit.php'
];

$contentFile = $allowedPages[$page] ?? 'pages/404.php';

$CURRENT_PAGE = $page; // utile pour le sidebar / breadcrumbs
include __DIR__ . "/includes/header.php";
include __DIR__ . "/" . $contentFile;
include __DIR__ . "/includes/footer.php";
