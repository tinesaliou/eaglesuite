<?php
require_once "../../config/db.php";

// Vérifier méthode HTTP (on interdit GET pour sécurité)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /{{TENANT_DIR}}/index.php?page=parametres&error=method");
    exit;
}

// Vérifier ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    header("Location: /{{TENANT_DIR}}/index.php?page=parametres&error=id");
    exit;
}

try {
    // Récupérer entreprise
    $stmt = $conn->prepare("SELECT logo FROM entreprise WHERE id = ?");
    $stmt->execute([$id]);
    $entreprise = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($entreprise) {
        // Supprimer le logo si existe
        if (!empty($entreprise['logo'])) {
            $logoPath = __DIR__ . "/../../uploads/" . $entreprise['logo'];
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }
        }

        // Supprimer en BDD
        $delete = $conn->prepare("DELETE FROM entreprise WHERE id = ?");
        $delete->execute([$id]);

        header("Location: /{{TENANT_DIR}}/index.php?page=parametres&tab=entreprise");
        exit;

    } else {
        header("Location: /{{TENANT_DIR}}/index.php?page=parametres&tab=entreprise&error=notfound");
        exit;
    }
} catch (Exception $e) {
    header("Location: /{{TENANT_DIR}}/index.php?page=parametres&tab=entreprise&error=" . urlencode($e->getMessage()));
    exit;
}
