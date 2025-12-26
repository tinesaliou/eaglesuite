<?php
require_once __DIR__ . "/../../../config/db.php";

$user = (int)$_POST['utilisateur_id'];
$caisses = $_POST['caisses'] ?? [];

$conn->prepare("DELETE FROM utilisateurs_caisses WHERE utilisateur_id = ?")
     ->execute([$user]);

$stmt = $conn->prepare("
    INSERT INTO utilisateurs_caisses (utilisateur_id, caisse_id)
    VALUES (?, ?)
");

foreach ($caisses as $caisse) {
    $stmt->execute([$user, $caisse]);
}

header("Location: /utilisateurs_caisses.php");
