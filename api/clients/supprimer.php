<?php
require_once __DIR__ . '/../../config/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM clients WHERE idClient = ?");
    $ok = $stmt->execute([$id]);

    if ($ok) {
        header("Location: /eaglesuite/index.php?page=clients");
        exit;
    } else {
        die("Erreur lors de la suppression");
    }
} else {
    die("❌ ID invalide");
}
