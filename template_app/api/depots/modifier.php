<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST["id"]);
    $nom = trim($_POST["nom"]);
    $description = trim($_POST["description"]);

    if ($id > 0 && !empty($nom)) {
        $stmt = $conn->prepare("UPDATE depots SET nom=?, description=? WHERE id=?");
        $stmt->execute([$nom, $description, $id]);
    }
}
header("Location: /{{TENANT_DIR}}/index.php?page=depots");
exit;
