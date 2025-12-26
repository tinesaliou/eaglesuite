<?php
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $type = $_POST['type'] ?? 'Particulier';
    $exonere = isset($_POST['exonere']) ? (int)$_POST['exonere'] : 0;

    if ($nom === '') {
        die("❌ Nom requis");
    }

    $stmt = $conn->prepare("
        INSERT INTO clients (nom, adresse, telephone, email, type, exonere)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $ok = $stmt->execute([$nom, $adresse, $telephone, $email, $type, $exonere]);

    if ($ok) {
        header("Location: /eaglesuite/index.php?page=clients");
        exit;
    } else {
        die("Erreur lors de l'ajout du client");
    }
}
