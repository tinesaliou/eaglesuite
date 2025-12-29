<?php
require_once __DIR__ . "/../../config/db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $conn->beginTransaction();

        $retour_id      = $_POST['id'];
        $type           = $_POST['type'] ?? 'client'; // client ou fournisseur
        $client_id      = $_POST['client_id'] ?? null;
        $fournisseur_id = $_POST['fournisseur_id'] ?? null;
        $date_retour    = $_POST['date_retour'] ?? date("Y-m-d H:i:s");
        $raison         = trim($_POST['raison'] ?? '');
        $depot_id       = $_POST['depot_id'] ?? null;
        $produits       = $_POST['produits'] ?? [];
        $user_id        = $_SESSION['user']['id'] ?? null;

        if ($depot_id && !empty($produits)) {
            /*
             * 1. Restaurer l'ancien état (stock + créances/dettes)
             */
            $oldDetails = $conn->prepare("
                SELECT rd.produit_id, rd.quantite, rd.prix_unitaire, r.type, r.client_id, r.fournisseur_id, r.depot_id
                FROM retours_details rd
                JOIN retours r ON r.id = rd.retour_id
                WHERE rd.retour_id = ?
            ");
            $oldDetails->execute([$retour_id]);

            foreach ($oldDetails->fetchAll(PDO::FETCH_ASSOC) as $od) {
                $produit_id     = $od['produit_id'];
                $quantite       = $od['quantite'];
                $prix           = $od['prix_unitaire'];
                $oldDepot       = $od['depot_id'];
                $oldType        = $od['type'];
                $oldClient      = $od['client_id'];
                $oldFournisseur = $od['fournisseur_id'];

                if ($oldType === "client") {
                    // Restaurer stock
                    $conn->prepare("
                        UPDATE stock_depot 
                        SET quantite = quantite - ? 
                        WHERE produit_id = ? AND depot_id = ?
                    ")->execute([$quantite, $produit_id, $oldDepot]);

                    // Restaurer créance (si elle existe, sinon insérer)
                    $montant = $prix * $quantite;
                    $check = $conn->prepare("SELECT COUNT(*) FROM creances_clients WHERE client_id=?");
                    $check->execute([$oldClient]);
                    if ($check->fetchColumn() > 0) {
                        $conn->prepare("UPDATE creances_clients SET montant = montant + ? WHERE client_id=?")
                             ->execute([$montant, $oldClient]);
                    } else {
                        $conn->prepare("INSERT INTO creances_clients (client_id, montant) VALUES (?, ?)")
                             ->execute([$oldClient, $montant]);
                    }

                } elseif ($oldType === "fournisseur") {
                    // Restaurer stock
                    $conn->prepare("
                        UPDATE stock_depot 
                        SET quantite = quantite + ? 
                        WHERE produit_id = ? AND depot_id = ?
                    ")->execute([$quantite, $produit_id, $oldDepot]);

                    // Restaurer dette (si elle existe, sinon insérer)
                    $montant = $prix * $quantite;
                    $check = $conn->prepare("SELECT COUNT(*) FROM dettes_fournisseurs WHERE fournisseur_id=?");
                    $check->execute([$oldFournisseur]);
                    if ($check->fetchColumn() > 0) {
                        $conn->prepare("UPDATE dettes_fournisseurs SET montant = montant + ? WHERE fournisseur_id=?")
                             ->execute([$montant, $oldFournisseur]);
                    } else {
                        $conn->prepare("INSERT INTO dettes_fournisseurs (fournisseur_id, montant) VALUES (?, ?)")
                             ->execute([$oldFournisseur, $montant]);
                    }
                }
            }

            /*
             * 2. Supprimer anciens détails et mouvements
             */
            $conn->prepare("DELETE FROM retours_details WHERE retour_id=?")->execute([$retour_id]);
            $conn->prepare("DELETE FROM mouvements_stock WHERE reference_table='retours' AND reference_id=?")->execute([$retour_id]);

            /*
             * 3. Mise à jour de la table retours
             */
            $stmt = $conn->prepare("
                UPDATE retours 
                SET type=?, client_id=?, fournisseur_id=?, date_retour=?, raison=?, depot_id=?, utilisateur_id=? 
                WHERE id=?
            ");
            $stmt->execute([$type, $client_id, $fournisseur_id, $date_retour, $raison, $depot_id, $user_id, $retour_id]);

            /*
             * 4. Réinsertion des nouveaux détails + mise à jour stock/dettes/créances
             */
            foreach ($produits as $prod) {
                $produit_id = $prod['id'];
                $quantite   = (int)$prod['quantite'];

                // Récupération du prix de référence
                $p = $conn->prepare("SELECT prix_vente FROM produits WHERE id=?");
                $p->execute([$produit_id]);
                $prix = $p->fetchColumn() ?: 0;

                // Insertion détail
                $conn->prepare("
                    INSERT INTO retours_details (retour_id, produit_id, quantite, prix_unitaire) 
                    VALUES (?, ?, ?, ?)
                ")->execute([$retour_id, $produit_id, $quantite, $prix]);

                $montant = $prix * $quantite;

                if ($type === "client") {
                    // Stock +
                    $conn->prepare("
                        INSERT INTO stock_depot (produit_id, depot_id, quantite)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE quantite = quantite + VALUES(quantite)
                    ")->execute([$produit_id, $depot_id, $quantite]);

                    // Mouvement stock
                    $conn->prepare("
                        INSERT INTO mouvements_stock 
                        (produit_id, depot_dest_id, quantite, type, reference_table, reference_id, utilisateur_id, date_mouvement) 
                        VALUES (?, ?, ?, 'retour_client', 'retours', ?, ?, NOW())
                    ")->execute([$produit_id, $depot_id, $quantite, $retour_id, $user_id]);

                    // Créance -
                    $check = $conn->prepare("SELECT COUNT(*) FROM creances_clients WHERE client_id=?");
                    $check->execute([$client_id]);
                    if ($check->fetchColumn() > 0) {
                        $conn->prepare("UPDATE creances_clients SET montant = montant - ? WHERE client_id=?")
                             ->execute([$montant, $client_id]);
                    } else {
                        $conn->prepare("INSERT INTO creances_clients (client_id, montant) VALUES (?, ?)")
                             ->execute([$client_id, -$montant]); // négatif car réduction
                    }

                } elseif ($type === "fournisseur") {
                    // Stock -
                    $conn->prepare("
                        UPDATE stock_depot 
                        SET quantite = quantite - ? 
                        WHERE produit_id = ? AND depot_id = ?
                    ")->execute([$quantite, $produit_id, $depot_id]);

                    // Mouvement stock
                    $conn->prepare("
                        INSERT INTO mouvements_stock 
                        (produit_id, depot_source_id, quantite, type, reference_table, reference_id, utilisateur_id, date_mouvement) 
                        VALUES (?, ?, ?, 'retour_fournisseur', 'retours', ?, ?, NOW())
                    ")->execute([$produit_id, $depot_id, $quantite, $retour_id, $user_id]);

                    // Dette -
                    $check = $conn->prepare("SELECT COUNT(*) FROM dettes_fournisseurs WHERE fournisseur_id=?");
                    $check->execute([$fournisseur_id]);
                    if ($check->fetchColumn() > 0) {
                        $conn->prepare("UPDATE dettes_fournisseurs SET montant = montant - ? WHERE fournisseur_id=?")
                             ->execute([$montant, $fournisseur_id]);
                    } else {
                        $conn->prepare("INSERT INTO dettes_fournisseurs (fournisseur_id, montant) VALUES (?, ?)")
                             ->execute([$fournisseur_id, -$montant]); // négatif car réduction
                    }
                }
            }
        }

        $conn->commit();
        header("Location: /eaglesuite/index.php?page=retours");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        die("❌ Erreur : " . $e->getMessage());
    }
}
