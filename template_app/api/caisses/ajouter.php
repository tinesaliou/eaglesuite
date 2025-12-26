<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code = trim($_POST["code"]);
    $nom = trim($_POST["nom"]);
    $symbole = trim($_POST["symbole"]);
    $taux_par_defaut = trim($_POST["taux_par_defaut"]);

    if (!empty($nom)) {
        $stmt = $conn->prepare("INSERT INTO devises (code,nom, symbole,taux_par_defaut, actif) VALUES (?, ?, ?,?,1");
        $stmt->execute([$nom, $taux]);
    }
}
 header("Location: /{{TENANT_DIR}}/index.php?page=parametres&tab=devises");
exit;
