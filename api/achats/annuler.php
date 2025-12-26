<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? null;

    if ($id) {
        // Marquer l'achat comme annulé
        $stmt = $conn->prepare("UPDATE achats SET annule = 1, statut = 'Annulé' WHERE id = ?");
        $stmt->execute([$id]);

        // Récupérer les détails de l'achat (produit, quantité, dépôt utilisé)
        $stmt = $conn->prepare("
            SELECT ad.produit_id, ad.quantite, ad.depot_id
            FROM achats_details ad
            WHERE ad.achat_id = ?
        ");
        $stmt->execute([$id]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($details as $d) {
            // Vérifier si une ligne existe déjà dans stock_depot
            $stmt = $conn->prepare("SELECT id FROM stock_depot WHERE produit_id = ? AND depot_id = ?");
            $stmt->execute([$d['produit_id'], $d['depot_id']]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stock) {
                // Mise à jour du stock (on retire les quantités achetées)
                $conn->prepare("
                    UPDATE stock_depot 
                    SET quantite = quantite - ? 
                    WHERE produit_id = ? AND depot_id = ?
                ")->execute([$d['quantite'], $d['produit_id'], $d['depot_id']]);
            } else {
                // ⚠️ Normalement, on ne devrait pas avoir à créer une ligne ici,
                // mais pour éviter une erreur on peut sécuriser à 0 - quantité
                $conn->prepare("
                    INSERT INTO stock_depot (produit_id, depot_id, quantite) 
                    VALUES (?, ?, 0)
                ")->execute([$d['produit_id'], $d['depot_id']]);
            }

            // Enregistrer le mouvement d’annulation
            $conn->prepare("
                INSERT INTO mouvements_stock 
                (produit_id, depot_dest_id, quantite, type, reference_table, reference_id, date_mouvement) 
                VALUES (?, ?, ?, 'annulation_achat', 'achats', ?, NOW())
            ")->execute([$d['produit_id'], $d['depot_id'], $d['quantite'], $id]);
        }
    }
}

// Redirection simple
header("Location: /eaglesuite/index.php?page=achats");
exit;
