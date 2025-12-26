<?php
/**
 * Vérifie si le tenant a encore accès
 */

require_once __DIR__ . '/db_master.php'; // master PDO
//session_start();

/* Nom du dossier tenant */
$tenantFolder = basename(dirname(__DIR__)); // ex: eagle_client_sarl1

/* Vérification dans la base master */
$stmt = $masterPdo->prepare("
    SELECT statut, expiration 
    FROM clients_saas 
    WHERE database_name = ?
");
$stmt->execute([$tenantFolder]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

/* Tenant non trouvé → blocage */
if (!$client) {
    include __DIR__ . "/../includes/access_denied.php";
    exit;
}

/* Statut suspendu → blocage */
if ($client['statut'] !== 'actif') {
    include __DIR__ . "/../includes/access_denied.php";
    exit;
}

/* Expiré → blocage */
if (!empty($client['expiration']) && $client['expiration'] < date("Y-m-d")) {
    include __DIR__ . "/../includes/access_denied.php";
    exit;
}

// OK → laisse continuer le tenant
