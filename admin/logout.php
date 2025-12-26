<?php
session_start();

// Supprime toutes les variables de session
$_SESSION = [];

// Détruit la session côté serveur
session_destroy();

// Redirige vers la page de connexion
header("Location: login.php");
exit;
