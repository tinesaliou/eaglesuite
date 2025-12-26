<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $exonere = isset($_POST['exonere']) ? (int)$_POST['exonere'] : 0;

    if ($nom === '') {
        die("❌ Nom requis");
    }

    $stmt = $conn->prepare("
        INSERT INTO fournisseurs (nom, adresse, telephone, email, exonere)
        VALUES (?, ?, ?, ?, ?)
    ");
    $ok = $stmt->execute([$nom, $adresse, $telephone, $email, $exonere]);

    if ($ok) {
        header("Location: /{{TENANT_DIR}}/index.php?page=fournisseurs");
        exit;
    } else {
        die("Erreur lors de l'ajout du fournisseur");
    }
}
