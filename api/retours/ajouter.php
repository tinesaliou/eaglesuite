<?php
require_once __DIR__ . "/../../config/db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type         = $_POST['type'] ?? 'client'; // client ou fournisseur
    $client_id    = $_POST['client_id'] ?? null;
    $fournisseur_id = $_POST['fournisseur_id'] ?? null;
    $date_retour  = $_POST['date_retour'] ?? date("Y-m-d H:i:s");
    $raison       = trim($_POST['raison'] ?? '');
    $depot_id     = $_POST['depot_id'] ?? null;
    $produits     = $_POST['produits'] ?? []; 
    $user_id      = $_SESSION['user']['id'] ?? null;

    if ($depot_id && !empty($produits)) {
        // 🔹 Insert retour
        $stmt = $conn->prepare("
            INSERT INTO retours (type, client_id, fournisseur_id, date_retour, raison, depot_id, utilisateur_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$type, $client_id, $fournisseur_id, $date_retour, $raison, $depot_id, $user_id]);
        $retour_id = $conn->lastInsertId();

        //  Parcours des produits
        foreach ($produits as $prod) {
            $produit_id = $prod['id'];
            $quantite   = (int)$prod['quantite'];

            $p = $conn->prepare("SELECT prix_vente FROM produits WHERE id=?");
            $p->execute([$produit_id]);
            $prix = $p->fetchColumn() ?: 0;

            // ➕ Insert détail
            $conn->prepare("
                INSERT INTO retours_details (retour_id, produit_id, quantite, prix_unitaire, depot_id) 
                VALUES (?, ?, ?, ?, ?)
            ")->execute([$retour_id, $produit_id, $quantite, $prix, $depot_id]);

            if ($type === "client") {
                // Stock + car le client retourne
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

                // Mise à jour créance client
                $conn->prepare("
                    UPDATE creances_clients 
                    SET montant = montant - ?
                    WHERE client_id = ?
                ")->execute([$prix * $quantite, $client_id]);

            } elseif ($type === "fournisseur") {
                // Stock - car on retourne au fournisseur
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

                // Mise à jour dette fournisseur
                $conn->prepare("
                    UPDATE dettes_fournisseurs 
                    SET montant = montant - ?
                    WHERE fournisseur_id = ?
                ")->execute([$prix * $quantite, $fournisseur_id]);
            }
        }
    }
}

// Redirection simple
header("Location: /eaglesuite/index.php?page=retours");
exit;
