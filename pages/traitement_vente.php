<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'];
    $type_vente = $_POST['type_vente'];
    $mode_paiement = $_POST['mode_paiement'];
    $produits = $_POST['produits'];
    $prixs = $_POST['prixs'];
    $qtes = $_POST['qtes'];
    $montants = $_POST['montants'];
    $utilisateeur = $_SESSION['utilisateur'];

    $total = array_sum($montants);
    $date_vente = date('Y-m-d H:i:s');
    $statut = ($type_vente === 'Comptant') ? 'Payé' : 'Impayé';

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("INSERT INTO ventes (client_id, date_vente, mode_paiement, total, type_vente, statut,utilisateur_id) VALUES (?, ?, ?, ?, ?, ?,?)");
        $stmt->execute([$client_id, $date_vente, $mode_paiement, $total, $type_vente, $statut, $utilisateeur ]);

        $vente_id = $conn->lastInsertId();

        $detailStmt = $conn->prepare("INSERT INTO ventes_lignes (vente_id, produit_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");

        foreach ($produits as $i => $produit_id) {
            $qte = $qtes[$i];
            $prix = $prixs[$i];
            $detailStmt->execute([$vente_id, $produit_id, $qte, $prix]);

            // Mettre à jour le stock (si nécessaire)
            $conn->prepare("UPDATE produits SET quantite = quantite - ? WHERE id = ?")->execute([$qte, $produit_id]);
        }

        $conn->commit();
        $_SESSION['message'] = "Vente enregistrée avec succès.";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['message'] = "Erreur lors de l'enregistrement de la vente : " . $e->getMessage();
    }

    header("Location: ventes.php");
    exit;
} else {
    header("Location: ventes.php");
    exit;
}
