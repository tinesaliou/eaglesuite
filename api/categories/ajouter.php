<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"]);
    $description = trim($_POST["description"]);

    if (!empty($nom)) {
        $stmt = $conn->prepare("INSERT INTO categories (nom, description) VALUES (?, ?)");
        $stmt->execute([$nom, $description]);
    }
}
header("Location: /eaglesuite/index.php?page=categories");
exit;
