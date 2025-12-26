<?php
require_once "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id        = intval($_POST['id'] ?? 0);
    $nom       = trim($_POST['nom'] ?? '');
    $adresse   = trim($_POST['adresse'] ?? null);
    $telephone = trim($_POST['telephone'] ?? null);
    $email     = trim($_POST['email'] ?? null);
    $site_web  = trim($_POST['site_web'] ?? null);
    $ninea     = trim($_POST['ninea'] ?? null);
    $rccm      = trim($_POST['rccm'] ?? null);

    // Récupérer l'ancien logo
    $stmtOld = $conn->prepare("SELECT logo FROM entreprise WHERE id = ?");
    $stmtOld->execute([$id]);
    $ancien = $stmtOld->fetch(PDO::FETCH_ASSOC);

    if (!$ancien) {
        die("❌ Entreprise introuvable.");
    }

    $imagePath = $ancien['logo']; // ancien logo

    // Gestion nouveau logo
    if (!empty($_FILES['logo']['name'])) {
        $uploadDir = __DIR__ . "/../../public/uploads/logo/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid("logo_") . "." . strtolower($ext);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
            // Supprimer l’ancien logo si existe
            if ($imagePath && file_exists(__DIR__ . "/../../public/" . $imagePath)) {
                unlink(__DIR__ . "/../../public/" . $imagePath);
            }
            $imagePath = "uploads/logo/" . $fileName;
        } else {
            die("❌ Échec du téléchargement du nouveau logo.");
        }
    }

    // Mise à jour en BDD
    $stmt = $conn->prepare("
        UPDATE entreprise SET 
            nom = ?,
            adresse = ?,
            telephone = ?,
            email = ?,
            site_web = ?,
            ninea = ?,
            rccm = ?,
            logo = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $nom, $adresse, $telephone, $email, $site_web, $ninea, $rccm, $imagePath, $id
    ]);

    // Redirection après succès
    header("Location: /{{TENANT_DIR}}/index.php?page=parametres&tab=entreprise");
    exit;
}
