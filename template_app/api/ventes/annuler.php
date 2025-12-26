<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? null;

    if ($id) {
        //  Marquer la vente comme annulée
        $stmt = $conn->prepare("UPDATE ventes SET annule = 1 WHERE id = ?");
        $stmt->execute([$id]);

        //  Récupérer les détails de la vente
        $stmt = $conn->prepare("
            SELECT vd.produit_id, vd.quantite, vd.depot_id
            FROM ventes_details vd
            WHERE vd.vente_id = ?
        ");
        $stmt->execute([$id]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($details as $d) {
            // Vérifier si une ligne existe déjà dans stock_depot
            $stmt = $conn->prepare("SELECT id FROM stock_depot WHERE produit_id = ? AND depot_id = ?");
            $stmt->execute([$d['produit_id'], $d['depot_id']]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stock) {
                //  Mise à jour du stock
                $conn->prepare("
                    UPDATE stock_depot 
                    SET quantite = quantite + ? 
                    WHERE produit_id = ? AND depot_id = ?
                ")->execute([$d['quantite'], $d['produit_id'], $d['depot_id']]);
            } else {
                //  Si la ligne n’existe pas encore, on la crée
                $conn->prepare("
                    INSERT INTO stock_depot (produit_id, depot_id, quantite) 
                    VALUES (?, ?, ?)
                ")->execute([$d['produit_id'], $d['depot_id'], $d['quantite']]);
            }

            //  Enregistrer le mouvement d’annulation
            $conn->prepare("
                INSERT INTO mouvements_stock 
                (produit_id, depot_dest_id, quantite, type, reference_table, reference_id, date_mouvement) 
                VALUES (?, ?, ?, 'annulation_vente', 'ventes', ?, NOW())
            ")->execute([$d['produit_id'], $d['depot_id'], $d['quantite'], $id]);
        }

        //  Supprimer les créances liées
        $conn->prepare("DELETE FROM creances_clients WHERE vente_id = ?")->execute([$id]);

        //  Supprimer les opérations de caisse liées
        $conn->prepare("DELETE FROM operations_caisse WHERE reference_table = 'ventes' AND reference_id = ?")
             ->execute([$id]);
    }
}

//  Redirection simple
header("Location: /{{TENANT_DIR}}/index.php?page=ventes");
exit;
