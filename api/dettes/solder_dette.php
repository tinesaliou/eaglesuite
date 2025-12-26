<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $dette_id       = $_POST['dette_id'] ?? null;
    $montant_paye   = floatval($_POST['montant_paye'] ?? 0);
    $mode_paiement  = $_POST['mode_paiement'] ?? 'Espèces';

    $montant_paye_devise = $montant_paye * $taux_change;

    // 🔹 Associer mode de paiement à une caisse
    $caisseMap = [
        'Espèces'      => 1,
        'Banque'       => 2,
        'Mobile Money' => 3
    ];
    $caisse_id = $caisseMap[$mode_paiement] ?? 1;

    if ($dette_id && $montant_paye_devise > 0) {
        //  Récupérer la dette
        $stmt = $conn->prepare("SELECT * FROM dettes_fournisseurs WHERE id = ?");
        $stmt->execute([$dette_id]);
        $dette = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dette) {
            $nouveau_paye   = $dette['montant_paye_devise'] + $montant_paye_devise;
            $reste_a_payer  = $dette['montant_total_devise'] - $nouveau_paye;
            $statut         = ($reste_a_payer <= 0) ? "Soldée" : "En cours";

            //  Mise à jour de la dette
            $stmt = $conn->prepare("
                UPDATE dettes_fournisseurs
                SET montant_paye = ?, reste_a_payer = ?, statut = ?
                WHERE id = ?
            ");
            $stmt->execute([$nouveau_paye, max(0, $reste_a_payer), $statut, $dette_id]);

            if (!empty($dette['achat_id'])) {
                $achat_id = $dette['achat_id'];

                // Récupérer la vente
                $stmt = $conn->prepare("SELECT * FROM achats WHERE id = ?");
                $stmt->execute([$achat_id]);
                $achat = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($achat) {
                    $new_montant_verse = $achat['montant_verse'] + $montant_verse;
                    $new_reste         = $achat['totalTTC'] - $new_montant_verse;
                    $achat_statut      = ($new_reste <= 0) ? "Payé" : "Impayé";

                    $stmt = $conn->prepare("
                        UPDATE achats
                        SET montant_verse = ?, reste_a_payer = ?, statut = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$new_montant_verse, max(0, $new_reste), $achat_statut, $achat_id]);
                }
            }

            //  Enregistrement en caisse (sortie)
            $stmt = $conn->prepare("
                INSERT INTO operations_caisse 
                    (caisse_id, type_operation, montant, mode_paiement, reference_table, reference_id, date_operation)
                VALUES (?, 'sortie', ?, ?, 'dettes_fournisseurs', ?, NOW())
            ");
            $stmt->execute([$caisse_id, $montant_paye_devise, $mode_paiement, $dette_id]);

            // 🔹 Mise à jour solde caisse
            $stmt = $conn->prepare("
                UPDATE caisses 
                SET solde_actuel = solde_actuel - ?
                WHERE id = ?
            ");
            $stmt->execute([$montant_paye_devise, $caisse_id]);
        }
    }
}

// 🔹 Retour
header("Location: /eaglesuite/index.php?page=dettes");
exit;
