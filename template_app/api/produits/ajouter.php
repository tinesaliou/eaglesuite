<?php
require_once "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Sécurisation des données ---
    $reference = trim($_POST['reference'] ?? null);
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? null);
    $prix_achat = floatval($_POST['prix_achat'] ?? 0);
    $prix_vente = floatval($_POST['prix_vente'] ?? 0);
    $stock_total = 0;
    $seuil_alerte = intval($_POST['seuil_alerte'] ?? 0);
    $categorie_id = trim($_POST['categorie_id'] ?? null);
    $depot_id = trim($_POST['depot_id'] ?? null);
    $unite_id = trim($_POST['unite_id'] ?? null);

    if ($nom === '') {
        die("❌ Le nom du produit est requis !");
    }

    // --- Gestion de l'image ---
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {

        $photo = $_FILES['image'];
        $tmpPath = $photo['tmp_name'];

        // Types et tailles autorisés
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

        // ✅ Dossier final (déjà existant)
        $uploadDir = realpath(__DIR__ . '/../../public/uploads/produits/');
        if (!$uploadDir) {
            die("❌ Dossier 'public/uploads/produits' introuvable !");
        }

        // Nom unique du fichier
        $ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));
        $fileName = uniqid("prod_") . "." . $ext;

        // Chemin complet
        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        // Déplacement du fichier
        if (move_uploaded_file($tmpPath, $targetPath)) {
            $imagePath = $fileName; // seul le nom en base
        } else {
            die("❌ Échec du téléchargement de l'image (vérifie les permissions du dossier).");
        }
    }

    // --- Insertion en base ---
    $stmt = $conn->prepare("
        INSERT INTO produits 
        (reference, nom, description, prix_achat, prix_vente, stock_total, seuil_alerte, categorie_id, depot_id, unite_id, image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $reference,
        $nom,
        $description,
        $prix_achat,
        $prix_vente,
        $stock_total,
        $seuil_alerte,
        $categorie_id,
        $depot_id,
        $unite_id,
        $imagePath
    ]);

    // --- Redirection ---
    header("Location: /{{TENANT_DIR}}/index.php?page=produits&success=1");
    exit;
}
?>
