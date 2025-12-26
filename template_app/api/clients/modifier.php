<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['idClient'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $type = $_POST['type'] ?? 'Particulier';
    $exonere = isset($_POST['exonere']) ? (int)$_POST['exonere'] : 0;

    if ($id <= 0 || $nom === '') {
        die("❌ ID ou Nom invalide");
    }

    $stmt = $conn->prepare("
        UPDATE clients 
        SET nom = ?, adresse = ?, telephone = ?, email = ?, type = ?, exonere = ?
        WHERE idClient = ?
    ");
    $ok = $stmt->execute([$nom, $adresse, $telephone, $email, $type, $exonere, $id]);

    if ($ok) {
        header("Location: /{{TENANT_DIR}}/index.php?page=clients");
        exit;
    } else {
        die("Erreur lors de la mise à jour du client");
    }
}
