<?php
require_once "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id           = intval($_POST['id'] ?? 0);
    $nom          = trim($_POST['nom'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');
    $entreprise_id = intval($_POST['entreprise_id'] ?? 1);
    $actif        = intval($_POST['actif'] ?? 1);
    $role_id = $_POST['role_id'] ?? null;

    if ($id <= 0) {
        die("❌ Utilisateur introuvable.");
    }

    if (!empty($mot_de_passe)) {
        $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            UPDATE utilisateurs 
            SET nom=?, email=?, mot_de_passe=?, entreprise_id=?, actif=?, role_id=?
            WHERE id=?
        ");
        $stmt->execute([$nom, $email, $hash, $entreprise_id, $actif, $role_id, $id]);
    } else {
        $stmt = $conn->prepare("
            UPDATE utilisateurs 
            SET nom=?, email=?, entreprise_id=?, actif=?, role_id=?
            WHERE id=?
        ");
        $stmt->execute([$nom, $email, $entreprise_id, $actif, $id, $role_id]);
    }

    header("Location: /eaglesuite/index.php?page=utilisateurs");
    exit;
}
