<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST["id"]);
    $nom = trim($_POST["nom"]);
    $taux = trim($_POST["taux"]);

    if ($id > 0 && !empty($nom)) {
        $stmt = $conn->prepare("UPDATE tva SET nom=?, taux=? WHERE id=?");
        $stmt->execute([$nom, $taux, $id]);
    }
}
 header("Location: /{{TENANT_DIR}}/index.php?page=parametres&tab=tva");
exit;
