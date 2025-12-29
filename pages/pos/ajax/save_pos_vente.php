<?php
require_once __DIR__ . '/../../../config/db.php';

$utilisateur_id = $_SESSION['user']['id'] ?? null;
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

    $conn->beginTransaction();

    try {

    $client_id      = $_POST['client_id'] ?? null;
    $date_vente     = $_POST['date_vente'] ?? date("Y-m-d H:i:s");
    $mode_paiement_id  = $_POST['mode_paiement_id'] ?? 1; // Espèces | Banque | Mobile Money
    $type_vente     = $_POST['type_vente'] ?? 'Comptant';
    $commentaire    = trim($_POST['commentaire'] ?? '');
    $produits       = $_POST['produits'] ?? [];
    $utilisateur_id = $_SESSION['user']['id'] ?? null; 

    $devise_id = $_POST['devise_id'] ?? null;

   
    $taux_change    = floatval($_POST['taux_change'] ?? 1);

    // Conversion en CFA (monnaie interne)

    $taxe           = floatval($_POST['taxe'] ?? 0);
    $remise         = floatval($_POST['remise'] ?? 0);
    $montant_verse  = floatval($_POST['montant_verse'] ?? 0);
    $montant_verse_devise = $montant_verse * $taux_change ;
    $taxe_devise  = $taxe * $taux_change;

    /* =========================
       NUMÉRO VENTE POS
    ========================= */
    $annee = date("Y");
    $mois  = date("m");

    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM ventes
        WHERE YEAR(date_vente)=? AND MONTH(date_vente)=?
    ");
    $stmt->execute([$annee, $mois]);
    $ordre = str_pad($stmt->fetchColumn() + 1, 2, "0", STR_PAD_LEFT);

    $numero = "POS-$annee/$mois-$ordre";

    //  Déterminer la caisse automatiquement selon le mode de paiement
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
                (numero,client_id, date_vente, totalHT, taxe, remise, totalTTC, montant_verse, reste_a_payer, type_vente, mode_paiement_id, statut, commentaire, devise_id, montant_devise, taux_change,utilisateur_id, created_at)
            VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?,?, NOW())
        ");
        $stmt->execute([
            $numero, $client_id, $date_vente, $totalHT, $taxe_devise, $remise, $totalTTC,
            $montant_verse_devise, $reste_a_payer, $type_vente, $mode_paiement_id, $statut, $commentaire ,$devise_id, $montant_devise, $taux_change,$utilisateur_id
        ]);

        $vente_id = $conn->lastInsertId();

    /* =========================
       DÉTAILS + STOCK
    ========================= */
    foreach ($produits as $p) {

    $produit_id = $p['id'];
    $qte        = $p['quantite'];
    $depot_id   = $p['depot_id'];

    $stmt = $conn->prepare("SELECT prix_vente FROM produits WHERE id=?");
    $stmt->execute([$produit_id]);
    $prix = (float)$stmt->fetchColumn();

    $conn->prepare("
        INSERT INTO ventes_details
        (vente_id, produit_id, quantite, prix_unitaire, depot_id)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([$vente_id, $produit_id, $qte, $prix, $depot_id]);

    $conn->prepare("
        UPDATE stock_depot
        SET quantite = quantite - ?
        WHERE produit_id = ? AND depot_id = ?
    ")->execute([$qte, $produit_id, $depot_id]);

    $conn->prepare("
        INSERT INTO mouvements_stock
        (produit_id, depot_source_id, quantite, type,
         reference_table, reference_id, utilisateur_id, date_mouvement)
        VALUES (?, ?, ?, 'vente', 'ventes', ?, ?, NOW())
    ")->execute([$produit_id, $depot_id, $qte, $vente_id, $utilisateur_id]);
}

    /* =========================
       CRÉANCE CLIENT
    ========================= */
    if ($reste_a_payer > 0) {
        $conn->prepare("
            INSERT INTO creances_clients
            (vente_id, client_id, montant_total,
             montant_paye, reste_a_payer, statut, date_creation)
            VALUES (?, ?, ?, ?, ?, 'En cours', NOW())
        ")->execute([
            $vente_id,
            $client_id,
            $totalTTC,
            $montant_verse_devise,
            $reste_a_payer
        ]);
    }

    /* =========================
       CAISSE (SI PAYÉ)
    ========================= */
    if ($montant_verse_devise > 0) {

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

        $stmt = $conn->prepare("
            SELECT ct.id, ct.caisse_id
            FROM sessions_caisse sc
            JOIN caisse_types ct ON ct.caisse_id = sc.caisse_id
            JOIN types_caisse tc ON tc.id = ct.type_caisse_id
            WHERE sc.id = ?
            AND tc.code = ?
        ");
        $stmt->execute([$session_caisse_id, $type_caisse_code]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new Exception("Type de caisse non disponible");
        }

            $caisse_type_id = $row['id'];
            $caisse_id      = $row['caisse_id'];

        $conn->prepare("
            INSERT INTO operations_caisse
            (session_caisse_id, caisse_type_id, type_operation,
            montant, devise_id, mode_paiement_id,
            reference_table, reference_id,
            utilisateur_id, date_operation)
            VALUES (?, ?, 'entree', ?, ?, ?, 'ventes', ?, ?, NOW())
        ")->execute([
            $session_caisse_id,
            $caisse_type_id,
            $montant_verse_devise,
            $devise_id,
            $mode_paiement_id,
            $vente_id,
            $utilisateur_id
        ]);

        $conn->prepare("
            UPDATE caisse_types
            SET solde_actuel = solde_actuel + ?
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
 }  
        $conn->commit();

            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
        }
    
header("Location: /eaglesuite/index.php?page=pos");
exit;
