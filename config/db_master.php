<?php
// config/master_db.php
$MASTER_DB_HOST = 'localhost';
$MASTER_DB_NAME = 'eagle_master';
$MASTER_DB_USER = 'saliou';
$MASTER_DB_PASS = 'tine';

try {
    $masterPdo = new PDO(
        "mysql:host={$MASTER_DB_HOST};dbname={$MASTER_DB_NAME};charset=utf8mb4",
        $MASTER_DB_USER,
        $MASTER_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die("Erreur connexion master DB : " . $e->getMessage());
}
