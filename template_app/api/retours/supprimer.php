<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $conn->beginTransaction();

            //  Récupérer les infos du retour
            $stmt = $conn->prepare("SELECT type, client_id, fournisseur_id FROM retours WHERE id = ?");
            $stmt->execute([$id]);
            $retour = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$retour) {
                throw new Exception("Retour introuvable.");
            }

            $type           = $retour['type'];
            $client_id      = $retour['client_id'];
            $fournisseur_id = $retour['fournisseur_id'];

            //  Récupérer les détails du retour
            $stmt = $conn->prepare("
                SELECT rd.produit_id, rd.quantite, rd.prix_unitaire, r.depot_id
                FROM retours_details rd
                JOIN retours r ON r.id = rd.retour_id
                WHERE rd.retour_id = ?
            ");
            $stmt->execute([$id]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($details as $d) {
                $produit_id = $d['produit_id'];
                $quantite   = $d['quantite'];
                $prix       = $d['prix_unitaire'];
                $depot_id   = $d['depot_id'];

                /*
                 * Réajustement stock et créance/dette
                 */
                if ($type === "client") {
                    // Stock : retour client → on l'avait augmenté, donc on le réduit
                    $conn->prepare("
                        UPDATE stock_depot 
                        SET quantite = quantite - ? 
                        WHERE produit_id = ? AND depot_id = ?
                    ")->execute([$quantite, $produit_id, $depot_id]);

                    // Créance client : retour client → on l'avait diminuée, donc on la réaugmente
                    $montant = $prix * $quantite;
                    $check = $conn->prepare("SELECT COUNT(*) FROM creances_clients WHERE client_id=?");
                    $check->execute([$client_id]);
                    if ($check->fetchColumn() > 0) {
                        $conn->prepare("UPDATE creances_clients SET montant = montant + ? WHERE client_id=?")
                             ->execute([$montant, $client_id]);
                    } else {
                        $conn->prepare("INSERT INTO creances_clients (client_id, montant) VALUES (?, ?)")
                             ->execute([$client_id, $montant]);
                    }

                } elseif ($type === "fournisseur") {
                    // Stock : retour fournisseur → on l’avait réduit, donc on le réaugmente
                    $conn->prepare("
                        UPDATE stock_depot 
                        SET quantite = quantite + ? 
                        WHERE produit_id = ? AND depot_id = ?
                    ")->execute([$quantite, $produit_id, $depot_id]);

                    // Dette fournisseur : retour fournisseur → on l’avait diminuée, donc on la réaugmente
                    $montant = $prix * $quantite;
                    $check = $conn->prepare("SELECT COUNT(*) FROM dettes_fournisseurs WHERE fournisseur_id=?");
                    $check->execute([$fournisseur_id]);
                    if ($check->fetchColumn() > 0) {
                        $conn->prepare("UPDATE dettes_fournisseurs SET montant = montant + ? WHERE fournisseur_id=?")
                             ->execute([$montant, $fournisseur_id]);
                    } else {
                        $conn->prepare("INSERT INTO dettes_fournisseurs (fournisseur_id, montant) VALUES (?, ?)")
                             ->execute([$fournisseur_id, $montant]);
                    }
                }

                //  Enregistrer le mouvement d'annulation
                $conn->prepare("
                    INSERT INTO mouvements_stock 
                    (produit_id, depot_source_id, quantite, type, reference_table, reference_id, date_mouvement) 
                    VALUES (?, ?, ?, 'suppression_retour', 'retours', ?, NOW())
                ")->execute([$produit_id, $depot_id, $quantite, $id]);
            }

            //  Supprimer les détails
            $stmt = $conn->prepare("DELETE FROM retours_details WHERE retour_id = ?");
            $stmt->execute([$id]);

            //  Supprimer le retour maître
            $stmt = $conn->prepare("DELETE FROM retours WHERE id = ?");
            $stmt->execute([$id]);

            //  Supprimer les opérations de caisse liées (ex: remboursement client)
            $stmt = $conn->prepare("DELETE FROM operations_caisse WHERE reference_table = 'retours' AND reference_id = ?");
            $stmt->execute([$id]);

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            die("❌ Erreur : " . $e->getMessage());
        }
    }
}

// Redirection simple
header("Location: /{{TENANT_DIR}}/index.php?page=retours");
exit;
