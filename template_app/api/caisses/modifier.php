<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST["id"]);
    $code = trim($_POST["code"]);
    $nom = trim($_POST["nom"]);
    $symbole = trim($_POST["symbole"]);
    $taux_par_defaut = trim($_POST["taux_par_defaut"]);

    if ($id > 0 && !empty($nom)) {
        $stmt = $conn->prepare("UPDATE devises SET code=?,nom=?,symbole=?, taux_par_defaut=? WHERE id=?");
        $stmt->execute([$code,$nom,$symbole, $taux_par_defaut, $id]);
    }
}
 header("Location: /{{TENANT_DIR}}/index.php?page=parametres&tab=devises");
exit;
