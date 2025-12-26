<?php

require_once __DIR__ . '/../config/db_master.php';  

$host = $_SERVER['HTTP_HOST'];   // ex : client1.eaglesuite.com OU keurgui.localhost

// Mode local : keurgui.localhost → sous-domaine = "keurgui"
if (str_ends_with($host, "localhost")) {
    $parts = explode('.', $host); // [keurgui, localhost]
    $subdomain = $parts[0];
}
// Mode distant : client1.eaglesuite.com → sous-domaine = "client1"
else {
    $parts = explode('.', $host);
    $subdomain = $parts[0];
}

// Sous-domaines "interdits" (admin, www, racine)
if (in_array($subdomain, ['www', 'admin', 'localhost', ''])) {
    // On est dans l’espace admin → pas de tenant
    return;
}

/* Protection : éviter injections dans le sous-domaine */
if (!preg_match('/^[a-z0-9-]+$/', $subdomain)) {
    die("Sous-domaine invalide.");
}

/* ----------------------------------------------------
   2. Récupération du tenant dans eagle_master
---------------------------------------------------- */
$stmt = $masterPdo->prepare("
    SELECT id, societe, subdomain, database_name, database_user, database_password, statut, expiration
    FROM clients_saas
    WHERE subdomain = ?
    LIMIT 1
");
$stmt->execute([$subdomain]);
$tenant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tenant) {
    die("Aucun client SaaS ne correspond au sous-domaine : " . htmlspecialchars($subdomain, ENT_QUOTES, 'UTF-8'));
}

/* ----------------------------------------------------
   3. Protéger les accès → vérifier statut
---------------------------------------------------- */
if ($tenant['statut'] !== 'actif') {
    die("Ce compte SaaS est désactivé. Contactez l’administrateur.");
}

if (!empty($tenant['expiration']) && strtotime($tenant['expiration']) < time()) {
    die("Abonnement expiré. Merci de renouveler.");
}

/* ----------------------------------------------------
   4. Connexion à la base tenant
---------------------------------------------------- */
try {
    $tenantPdo = new PDO(
        "mysql:host=127.0.0.1;dbname={$tenant['database_name']};charset=utf8mb4",
        $tenant['database_user'],           // tu veux utiliser le même user pour tous les tenants
        $tenant['database_password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Exposer proprement dans global scope
    $GLOBALS['tenantPdo'] = $tenantPdo;
    $GLOBALS['tenantInfo'] = $tenant;

} catch (Exception $e) {
    die("Impossible de se connecter à la base client : " . $e->getMessage());
}

