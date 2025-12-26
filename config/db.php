<?php
 
$host = 'localhost';
$dbname = 'eaglesuite';
$user = 'saliou';
$pass = 'tine'; // ou 'votre_mot_de_passe'

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $conn; 
    
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
