<?php
require_once "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Sécurisation des données ---
    $id = intval($_POST['id'] ?? 0);
    $reference = trim($_POST['reference'] ?? null);
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? null);
    $prix_achat = floatval($_POST['prix_achat'] ?? 0);
    $prix_vente = floatval($_POST['prix_vente'] ?? 0);
    $seuil_alerte = intval($_POST['seuil_alerte'] ?? 0);
    $categorie_id = trim($_POST['categorie_id'] ?? null);
    $depot_id = trim($_POST['depot_id'] ?? null);
    $unite_id = trim($_POST['unite_id'] ?? null);

    if ($id <= 0) {
        die("❌ ID du produit invalide.");
    }

    if ($nom === '') {
        die("❌ Le nom du produit est requis !");
    }

    // --- Récupération de l'ancien produit ---
    $stmtOld = $conn->prepare("SELECT image FROM produits WHERE id = ?");
    $stmtOld->execute([$id]);
    $oldProduct = $stmtOld->fetch(PDO::FETCH_ASSOC);
    $oldImage = $oldProduct['image'] ?? null;

    // --- Gestion de la nouvelle image (si présente) ---
    $imagePath = $oldImage;

    if (!empty($_FILES['image']['name'])) {

        $photo = $_FILES['image'];
        $tmpPath = $photo['tmp_name'];

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 3 * 1024 * 1024; // 3 Mo

        $fileType = mime_content_type($tmpPath);
        $fileSize = $photo['size'];

        if (!in_array($fileType, $allowedTypes)) {
            die("❌ Seules les images JPG, PNG, GIF ou WEBP sont autorisées.");
        }

        if ($fileSize > $maxSize) {
            die("❌ L'image dépasse la taille maximale autorisée (3 Mo).");
        }

        // ✅ Dossier final (existant)
        $uploadDir = realpath(__DIR__ . '/../../public/uploads/produits/');
        if (!$uploadDir) {
            die("❌ Dossier 'public/uploads/produits' introuvable !");
        }

        // Nouveau nom unique
        $ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
        $fileName = uniqid("prod_") . "." . $ext;
        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        // Déplacer la nouvelle image
        if (move_uploaded_file($tmpPath, $targetPath)) {

            // Supprimer l’ancienne image si elle existe
            if ($oldImage && file_exists($uploadDir . DIRECTORY_SEPARATOR . $oldImage)) {
                unlink($uploadDir . DIRECTORY_SEPARATOR . $oldImage);
            }

            $imagePath = $fileName; // enregistrer uniquement le nom
        } else {
            die("❌ Échec du téléchargement de la nouvelle image.");
        }
    }

    // --- Mise à jour en base ---
    $stmt = $conn->prepare("
        UPDATE produits SET 
            reference = ?, 
            nom = ?, 
            description = ?, 
            prix_achat = ?, 
            prix_vente = ?, 
            seuil_alerte = ?, 
            categorie_id = ?, 
            depot_id = ?, 
            unite_id = ?, 
            image = ? 
        WHERE id = ?
    ");

    $stmt->execute([
        $reference,
        $nom,
        $description,
        $prix_achat,
        $prix_vente,
        $seuil_alerte,
        $categorie_id,
        $depot_id,
        $unite_id,
        $imagePath,
        $id
    ]);

    // --- Redirection ---
    header("Location: /eaglesuite/index.php?page=produits&update=1");
    exit;
}
?>
