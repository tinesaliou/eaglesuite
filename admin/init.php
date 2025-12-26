<?php
// admin/init.php
session_start();

// chemin vers le fichier de config master (ajuste si besoin)
require_once __DIR__ . '/../config/db_master.php'; // doit définir $masterPdo (PDO vers eagle_master)

// helper permission simple
function is_admin_logged(): bool {
    return !empty($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
}

// redirect to login if not authenticated (use sur pages admin)
function require_admin() {
    if (!is_admin_logged()) {
        header('Location: /eaglesuite/admin/login.php');
        exit;
    }
}
