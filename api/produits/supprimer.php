<?php
require_once "../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Sécurisation de l’ID ---
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        die("❌ ID du produit invalide !");
    }

    // --- Récupération du produit avant suppression ---
    $stmt = $conn->prepare("SELECT image FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produit) {
        die("❌ Produit introuvable !");
    }

    $imageFile = $produit['image'] ?? null;

    // --- Dossier des images ---
    $uploadDir = realpath(__DIR__ . '/../../public/uploads/produits/');

    // --- Suppression du produit ---
    $stmtDelete = $conn->prepare("DELETE FROM produits WHERE id = ?");
    $deleted = $stmtDelete->execute([$id]);

    if ($deleted) {
        //  Si suppression réussie → supprimer l’image associée
        if ($imageFile && file_exists($uploadDir . DIRECTORY_SEPARATOR . $imageFile)) {
            unlink($uploadDir . DIRECTORY_SEPARATOR . $imageFile);
        }

        // --- Redirection ou message ---
        header("Location: /eaglesuite/index.php?page=produits&delete=1");
        exit;
    } else {
        die("❌ Échec de la suppression du produit.");
    }
}
?>
