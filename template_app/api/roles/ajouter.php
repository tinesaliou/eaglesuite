<?php
require_once __DIR__ . "/../../config/db.php";

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $permissions = $_POST['permissions'] ?? [];

    if ($nom === '') {
        die("Nom du rôle obligatoire.");
    }

    try {
        $conn->beginTransaction();

        // Insertion du rôle
        $stmt = $conn->prepare("INSERT INTO roles (nom, description) VALUES (?, ?)");
        $stmt->execute([$nom, $description]);
        $roleId = $conn->lastInsertId();

        //  Insertion des permissions associées
        if (!empty($permissions)) {
            $stmtPerm = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($permissions as $perm) {
                $stmtPerm->execute([$roleId, $perm]);
            }
        }

        $conn->commit();

        // Redirection après succès
        header("Location: /{{TENANT_DIR}}/index.php?page=roles");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        die("Erreur lors de l'ajout du rôle : " . $e->getMessage());
    }
} else {
    die("Méthode non autorisée.");
}
