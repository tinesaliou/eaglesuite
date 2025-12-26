<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

$utilisateur_id = $_SESSION['user_id'] ?? null;
if (!$utilisateur_id) {
    throw new Exception("Utilisateur non authentifié");
}

$stmt = $conn->prepare("
    SELECT id, caisse_id
    FROM sessions_caisse
    WHERE utilisateur_id = ?
      AND statut = 'ouverte'
    LIMIT 1
");
$stmt->execute([$utilisateur_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    throw new Exception("Aucune session de caisse ouverte pour cet utilisateur");
}

$session_caisse_id = $session['id'];
$caisse_id = $session['caisse_id'];


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $fournisseur_id = $_POST['fournisseur_id'] ?? null;
    $date_achat     = $_POST['date_achat'] ?? date("Y-m-d H:i:s");
    $mode_paiement_id  = $_POST['mode_paiement_id'] ?? 1;  
    $type_achat     = $_POST['type_achat'] ?? 'Comptant';
    $produits       = $_POST['produits'] ?? [];
    $devise_id = $_POST['devise_id'] ?? null;
    $utilisateur_id = $_SESSION['user_id'] ?? null; 

    
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
            FROM achats 
            WHERE YEAR(date_achat) = :annee AND MONTH(date_achat) = :mois";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':annee' => $annee,
        ':mois'  => $mois
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $ordre = str_pad($row['nb'] + 1, 2, "0", STR_PAD_LEFT);
    $numero = "BC-" . $annee . "/" . $mois . "-" . $ordre;

    $stmt = $conn->prepare("
            SELECT tc.code
            FROM modes_paiement mp
            JOIN types_caisse tc ON tc.code = mp.code
            WHERE mp.id = ?
        ");
        $stmt->execute([$mode_paiement_id]);
        $type_caisse_code = $stmt->fetchColumn();

        if (!$mode_paiement_id) {
            throw new Exception("Mode de paiement non défini");
        }

    if ($fournisseur_id && !empty($produits)) {
        $conn->beginTransaction();

        try {
            //  Calcul total HT
            $totalHT = 0;
            foreach ($produits as $prod) {
                $p = $conn->prepare("SELECT prix_achat FROM produits WHERE id = ?");
                $p->execute([$prod['id']]);
                $prix = $p->fetchColumn();
                $totalHT += $prix * $prod['quantite'];
            }

            //  Calcul TTC
            $totalTTC = $totalHT + $taxe - $remise;
            $montant_devise = ($taux_change > 0) ? ($totalTTC / $taux_change) : $totalTTC; // en devise
            $reste_a_payer = $totalTTC - $montant_verse_devise;
            $statut = ($reste_a_payer <= 0) ? "Payé" : "Impayé";

            //  Enregistrer achat
            $stmt = $conn->prepare("
                INSERT INTO achats (numero, fournisseur_id, date_achat, totalHT, taxe, remise, totalTTC, montant_verse, reste_a_payer, type_achat, mode_paiement_id, statut, devise_id, montant_devise, taux_change, utilisateur_id, created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())
            ");
            $stmt->execute([
                $numero, $fournisseur_id, $date_achat, $totalHT, $taxe_devise, $remise, $totalTTC,
                $montant_verse_devise, $reste_a_payer, $type_achat, $mode_paiement_id, $statut, $devise_id, $montant_devise, $taux_change,$utilisateur_id
            ]);

            $achat_id = $conn->lastInsertId();

            //  Détails & stock
            foreach ($produits as $prod) {
                $p = $conn->prepare("SELECT prix_achat FROM produits WHERE id=?");
                $p->execute([$prod['id']]);
                $prix = $p->fetchColumn();

                // Détail achat
                $conn->prepare("
                    INSERT INTO achats_details (achat_id, produit_id, quantite, prix_unitaire, depot_id) 
                    VALUES (?, ?, ?, ?, ?)
                ")->execute([$achat_id, $prod['id'], $prod['quantite'], $prix, $prod['depot_id']]);

                // Stock
                $check = $conn->prepare("SELECT id FROM stock_depot WHERE produit_id=? AND depot_id=?");
                $check->execute([$prod['id'], $prod['depot_id']]);
                $exist = $check->fetch(PDO::FETCH_ASSOC);

                if ($exist) {
                    $conn->prepare("UPDATE stock_depot SET quantite = quantite + ? WHERE id=?")
                         ->execute([$prod['quantite'], $exist['id']]);
                } else {
                    $conn->prepare("INSERT INTO stock_depot (produit_id, depot_id, quantite) VALUES (?, ?, ?)")
                         ->execute([$prod['id'], $prod['depot_id'], $prod['quantite']]);
                }

                // Mouvement stock (entrée)
                $conn->prepare("
                    INSERT INTO mouvements_stock 
                    (produit_id, depot_dest_id, quantite, type, reference_table, reference_id, utilisateur_id,date_mouvement) 
                    VALUES (?, ?, ?, 'achat', 'achats', ?, ?, NOW())
                ")->execute([$prod['id'], $prod['depot_id'], $prod['quantite'], $achat_id, $utilisateur_id]);
            }

            //  Dette fournisseur
            $statut_dette = ($reste_a_payer > 0) ? "En cours" : "Soldé";
            $conn->prepare("
                INSERT INTO dettes_fournisseurs 
                    (achat_id, fournisseur_id, montant_total, montant_paye,  reste_a_payer, statut, date_creation)
                VALUES (?, ?, ?, ?, ?, ?,   NOW())
            ")->execute([
                $achat_id,
                $fournisseur_id,
                $totalTTC,
                $montant_verse_devise,
                
                $reste_a_payer,
                $statut_dette
            ]);

            //  Opération de caisse (sortie si versement > 0)
        if ($montant_verse_devise > 0 ) {
                // Enregistrer l'opération
            $stmt = $conn->prepare("
                SELECT ct.id AS caisse_type_id
                FROM sessions_caisse sc
                JOIN caisse_types ct ON ct.caisse_id = sc.caisse_id
                JOIN types_caisse tc ON tc.id = ct.type_caisse_id
                JOIN modes_paiement mp ON mp.code = tc.code
                WHERE sc.id = ?
                AND mp.id = ?
                ");
                $stmt->execute([
                    $session_caisse_id,
                    $mode_paiement_id
                ]);

                $caisseType = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$caisseType) {
                    throw new Exception("Type de caisse non autorisé dans la caisse ouverte");
                }

                $caisse_type_id = $caisseType['caisse_type_id'];


            $conn->prepare("
            INSERT INTO operations_caisse
            (session_caisse_id, caisse_type_id, type_operation,
            montant, devise_id, mode_paiement_id,
            reference_table, reference_id,
            utilisateur_id, date_operation)
            VALUES (?, ?, 'sortie', ?, ?, ?, 'achats', ?, ?, NOW())
        ")->execute([
            $session_caisse_id,
            $caisse_type_id,
            $montant_verse_devise,
            $devise_id,
            $mode_paiement_id,
            $achat_id,
            $utilisateur_id
        ]);
        $conn->prepare("
            UPDATE caisse_types
            SET solde_actuel = solde_actuel - ?
            WHERE id = ?
        ")->execute([$montant_verse_devise, $caisse_type_id]);

        $conn->prepare("
            UPDATE caisses c
            SET c.solde_actuel = (
                SELECT SUM(solde_actuel)
                FROM caisse_types
                WHERE caisse_id = c.id
            )
            WHERE c.id = ?
        ")->execute([$caisse_id]);
    }

        $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            die("❌ Erreur : " . $e->getMessage());
        }
    }
}

//  Redirection
header("Location: /{{TENANT_DIR}}/index.php?page=achats");
exit;
