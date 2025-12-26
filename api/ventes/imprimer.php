<?php
require_once '../../config/db.php';

if (isset($_GET['id'])) {
    $vente_id = $_GET['id'];

    // Supprimer d'abord les lignes de vente associées
    $conn->prepare("DELETE FROM ligne_vente WHERE vente_id = ?")->execute([$vente_id]);

    // Puis la vente elle-même
    $conn->prepare("DELETE FROM ventes WHERE id = ?")->execute([$vente_id]);

    header("Location: ventes.php?suppression=success");
    exit();
}
?>
