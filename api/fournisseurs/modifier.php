<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $exonere = isset($_POST['exonere']) ? (int)$_POST['exonere'] : 0;

    if ($id <= 0 || $nom === '') {
        die("❌ ID ou Nom invalide");
    }

    $stmt = $conn->prepare("
        UPDATE fournisseurs 
        SET nom = ?, adresse = ?, telephone = ?, email = ?,  exonere = ?
        WHERE id = ?
    ");
    $ok = $stmt->execute([$nom, $adresse, $telephone, $email,  $exonere, $id]);

    if ($ok) {
        header("Location: /eaglesuite/index.php?page=fournisseurs");
        exit;
    } else {
        die("Erreur lors de la mise à jour du fournisseur");
    }
}
