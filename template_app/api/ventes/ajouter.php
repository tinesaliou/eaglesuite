<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $client_id      = $_POST['client_id'] ?? null;
    $date_vente     = $_POST['date_vente'] ?? date("Y-m-d H:i:s");
    $mode_paiement  = $_POST['mode_paiement'] ?? 'Espèces'; // Espèces | Banque | Mobile Money
    $type_vente     = $_POST['type_vente'] ?? 'Comptant';
    $commentaire    = trim($_POST['commentaire'] ?? '');
    $produits       = $_POST['produits'] ?? [];

    $devise_id = $_POST['devise_id'] ?? null;

   
    $taux_change    = floatval($_POST['taux_change'] ?? 1);

    // Conversion en CFA (monnaie interne)

    $taxe           = floatval($_POST['taxe'] ?? 0);
    $remise         = floatval($_POST['remise'] ?? 0);
    $montant_verse  = floatval($_POST['montant_verse'] ?? 0);
    $montant_verse_devise = $montant_verse * $taux_change ;
    $taxe_devise  = $taxe * $taux_change;

    $annee = date("Y");
    $mois  = date("m");
    $sql = "SELECT COUNT(*) as nb 
            FROM ventes 
            WHERE YEAR(date_vente) = :annee AND MONTH(date_vente) = :mois";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':annee' => $annee,
        ':mois'  => $mois
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $ordre = str_pad($row['nb'] + 1, 2, "0", STR_PAD_LEFT);
    $numero = "FAC-" . $annee . "/" . $mois . "-" . $ordre;

    //  Déterminer la caisse automatiquement selon le mode de paiement
    $map_caisses = [
        "Espèces"      => 1,
        "Banque"       => 2,
        "Mobile Money" => 3
    ];
    $caisse_id = $map_caisses[$mode_paiement] ?? 1; // défaut = espèces

    if ($client_id && !empty($produits)) {
        // Calcul total HT
        $totalHT = 0;
        foreach ($produits as $prod) {
            $p = $conn->prepare("SELECT prix_vente FROM produits WHERE id = ?");
            $p->execute([$prod['id']]);
            $prix = $p->fetchColumn();
            $totalHT += $prix * $prod['quantite'];
        }

        $totalTTC = $totalHT + $taxe - $remise;
        $montant_devise = ($taux_change > 0) ? ($totalTTC / $taux_change) : $totalTTC; // en devise
        $reste_a_payer = $totalTTC - $montant_verse_devise;
        $statut = ($reste_a_payer <= 0) ? "Payé" : "Impayé";

        // Enregistrer la vente
        $stmt = $conn->prepare("
            INSERT INTO ventes 
                (numero,client_id, date_vente, totalHT, taxe, remise, totalTTC, montant_verse, reste_a_payer, type_vente, mode_paiement, statut, commentaire, devise_id, montant_devise, taux_change, created_at)
            VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, NOW())
        ");
        $stmt->execute([
            $numero, $client_id, $date_vente, $totalHT, $taxe_devise, $remise, $totalTTC,
            $montant_verse_devise, $reste_a_payer, $type_vente, $mode_paiement, $statut, $commentaire ,$devise_id, $montant_devise, $taux_change
        ]);

        $vente_id = $conn->lastInsertId();

        //  Détails produits + stock
        foreach ($produits as $prod) {
            $produit_id = $prod['id'];
            $quantite   = $prod['quantite'];
            $depot_id   = $prod['depot_id'];

            $p = $conn->prepare("SELECT prix_vente FROM produits WHERE id=?");
            $p->execute([$produit_id]);
            $prix = $p->fetchColumn();

            $conn->prepare("
                INSERT INTO ventes_details (vente_id, produit_id, quantite, prix_unitaire, depot_id) 
                VALUES (?, ?, ?, ?, ?)
            ")->execute([$vente_id, $produit_id, $quantite, $prix, $depot_id]);

            //  Mise à jour stock
            $stmt = $conn->prepare("SELECT id, quantite FROM stock_depot WHERE produit_id = ? AND depot_id = ?");
            $stmt->execute([$produit_id, $depot_id]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stock) {
                $conn->prepare("UPDATE stock_depot SET quantite = quantite - ? WHERE produit_id = ? AND depot_id = ?")
                     ->execute([$quantite, $produit_id, $depot_id]);
            } else {
                $conn->prepare("INSERT INTO stock_depot (produit_id, depot_id, quantite) VALUES (?, ?, ?)")
                     ->execute([$produit_id, $depot_id, -$quantite]);
            }

            //  Mouvement de sortie
            $conn->prepare("
                INSERT INTO mouvements_stock 
                (produit_id, depot_source_id, quantite, type, reference_table, reference_id, date_mouvement) 
                VALUES (?, ?, ?, 'vente', 'ventes', ?, NOW())
            ")->execute([$produit_id, $depot_id, $quantite, $vente_id]);
        }

        //  Créance client
        $statut_creance = ($reste_a_payer > 0) ? "En cours" : "Soldé";
        $conn->prepare("
            INSERT INTO creances_clients 
                (vente_id, client_id, montant_total, montant_paye, reste_a_payer, statut, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ")->execute([
            $vente_id,
            $client_id,
            $totalTTC,
            $montant_verse_devise,
            $reste_a_payer,
            $statut_creance
        ]);

        if ($montant_verse_devise > 0 && !empty($caisse_id)) {
            // Enregistrer l'opération
            $stmt = $conn->prepare("
                INSERT INTO operations_caisse 
                (caisse_id, type_operation, montant, devise_id, mode_paiement, reference_table, reference_id, date_operation)
                VALUES (?, 'entree', ?,  ?,?,  'ventes', ?, NOW())
            ");
            $stmt->execute([$caisse_id, $montant_verse_devise,$devise_id, $mode_paiement, $vente_id]);

            // Mettre à jour le solde actuel de la caisse
            $stmt = $conn->prepare("
                UPDATE caisses 
                SET solde_actuel = solde_actuel + ? 
                WHERE id = ?
            ");
            $stmt->execute([$montant_verse_devise, $caisse_id]);
        }

    }
}

header("Location: /{{TENANT_DIR}}/index.php?page=ventes");
exit;
