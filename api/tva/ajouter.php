<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"]);
    $taux = trim($_POST["taux"]);

    if (!empty($nom)) {
        $stmt = $conn->prepare("INSERT INTO tva (nom, taux, actif) VALUES (?, ?, 1)");
        $stmt->execute([$nom, $taux]);
    }
}
 header("Location: /eaglesuite/index.php?page=parametres&tab=tva");
exit;
