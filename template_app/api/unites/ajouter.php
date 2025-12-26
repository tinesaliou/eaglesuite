<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST["nom"]);

    if (!empty($nom)) {
        $stmt = $conn->prepare("INSERT INTO unites (nom) VALUES (?)");
        $stmt->execute([$nom]);
    }
}
    header("Location: /{{TENANT_DIR}}/index.php?page=parametres&tab=unites");
    exit;
