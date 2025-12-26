<?php
require_once "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom       = trim($_POST['nom'] ?? '');
    $adresse   = trim($_POST['adresse'] ?? null);
    $telephone = trim($_POST['telephone'] ?? null);
    $email     = trim($_POST['email'] ?? null);
    $site_web  = trim($_POST['site_web'] ?? null);
    $ninea     = trim($_POST['ninea'] ?? null);
    $rccm      = trim($_POST['rccm'] ?? null);

    // Gestion du logo
    $logoPath = null;
    if (!empty($_FILES['logo']['name'])) {
        $uploadDir = __DIR__ . "/../../public/uploads/logo/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid("logo_") . "." . strtolower($ext);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
            $logoPath = "uploads/logo/" . $fileName;
        } else {
            die("❌ Échec du téléchargement du logo.");
        }
    }

    // Préparer la requête SQL
    $stmt = $conn->prepare("
        INSERT INTO entreprise 
        (nom, adresse, telephone, email, site_web, ninea, rccm, logo, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $nom, $adresse, $telephone, $email, $site_web, $ninea, $rccm, $logoPath
    ]);

    // Redirection avec succès
    header("Location: /{{TENANT_DIR}}/index.php?page=parametres&tab=entreprise");
    exit;
}
