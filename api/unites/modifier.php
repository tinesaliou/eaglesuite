<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST["id"]);
    $nom = trim($_POST["nom"]);

    if ($id > 0 && !empty($nom)) {
        $stmt = $conn->prepare("UPDATE unites SET nom=? WHERE id=?");
        $stmt->execute([$nom, $id]);
    }
}
    header("Location: /eaglesuite/index.php?page=parametres&tab=unites");
    exit;
