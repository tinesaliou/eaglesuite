<?php

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/check_auth.php"; 


session_start();
// Si on arrive ici, c’est que l’utilisateur est connecté
$page = $_GET['page'] ?? 'dashboard';

check_page_permission($page);

$allowedPages = [
    'dashboard'        => 'pages/dashboard.php',
    'produits'         => 'pages/produits/produits.php',
    'profil'           => 'pages/profil/profile.php',
    'categories'       => 'pages/categories/categories.php',
    'ventes'           => 'pages/ventes/ventes.php',
    'pos'              => 'pages/pos/pos.php',
    'pos_guard'        => 'pages/pos/pos_guard.php',
    'ouvrir_caisse'    => 'pages/pos/ouvrir_caisse.php',
    'fermer_caisse'    => 'pages/pos/fermer_caisse.php',
    'pos-entry'        => 'pages/pos/pos-entry.php',
    'pos-select-caisse'   => 'pages/pos/pos-select-caisse.php',
    'pos-pin'         => 'pages/pos/pos-pin.php',
    'pos-open'         => 'pages/pos/pos-open.php',
    'pos-liste'         => 'pages/pos/pos-liste.php',
    'achats'           => 'pages/achats/achats.php',
    'creances'         => 'pages/creances/creances.php',
    'dettes'           => 'pages/dettes/dettes.php',
    'depots'           => 'pages/depots/depots.php',
    'mouvements'       => 'pages/stocks/mouvements.php',
    'inventaire'       => 'pages/stocks/inventaire.php',
    'retours'          => 'pages/retours/retours.php',
    'clients'          => 'pages/clients/clients.php',
    'tresorerie'       => 'pages/tresorerie/tresorerie.php',
    'caisse_especes'   => 'pages/caisses/caisse.php',
    'caisse_banque'    => 'pages/caisses/caisse.php',
    'caisse_mobile'    => 'pages/caisses/caisse.php',
    'autres_operations'=> 'pages/tresorerie/autres_operations.php',
    'fournisseurs'     => 'pages/fournisseurs/fournisseurs.php',
    'parametres'       => 'pages/parametres/parametres.php',
    'caisses'       => 'pages/parametres/caisses/caisses.php',
    'caisse_types'       => 'pages/parametres/caisses/caisse_types.php',
    'affectation_utilisateurs'   => 'pages/parametres/caisses/affectation_utilisateurs.php',
    'utilisateurs'     => 'pages/utilisateurs/utilisateurs.php',
    'roles'            => 'pages/roles/roles.php',
    'rapports_achats'     => 'pages/rapports/rapports_achats.php',
    'rapports_ventes'     => 'pages/rapports/rapports_ventes.php',
    'rapports_caisse'     => 'pages/rapports/rapports_caisse.php',
    'rapports_stocks'     => 'pages/rapports/rapports_stocks.php',
    'export_report_achats'     => 'pages/rapports/export_report_achats.php',
    'export_report_ventes'     => 'pages/rapports/export_report_ventes.php',
    'export_report_caisse'     => 'pages/rapports/export_report_caisse.php',
    'export_report_stocks'     => 'pages/rapports/export_report_stocks.php',

    'crm_clients'         => 'pages/crm/clients_liste.php',
    'crm_client'          => 'pages/crm/client_view.php',
    'crm_interactions'   =>  'pages/crm/interactions_liste.php',
    'crm_add_interaction' => 'pages/crm/interaction_add.php',
    'crm_opportunites'   =>  'pages/crm/opportunites.php',
    'crm_dashboard'      =>  'pages/crm/dashboard.php',
    'crm_export_clients'  => 'pages/crm/export_clients_csv.php'

];

$contentFile = $allowedPages[$page] ?? 'pages/404.php';

$noLayoutPages = [
    'pos-entry',
    'pos-open',
    'pos-pin',
    'pos_guard',
    'export_report_achats',
    'export_report_ventes',
    'export_report_caisse'
];

if (in_array($page, $noLayoutPages)) {
    require __DIR__ . "/" . $contentFile;
    exit;
}

include __DIR__ . "/includes/layout.php";
include __DIR__ . "/" . $contentFile;
