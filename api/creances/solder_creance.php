<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $creance_id     = $_POST['creance_id'] ?? null;
    $montant_verse  = floatval($_POST['montant_verse'] ?? 0);
    $mode_paiement  = $_POST['mode_paiement'] ?? 'Espèces';

    $montant_verse_devise = $montant_paye * $taux_change;

    // Associer mode de paiement à la caisse
    $caisseMap = [
        'Espèces'      => 1,
        'Banque'       => 2,
        'Mobile Money' => 3
    ];
    $caisse_id = $caisseMap[$mode_paiement] ?? 1;

    if ($creance_id && $montant_verse_devise > 0) {
        //  Récupérer la créance
        $stmt = $conn->prepare("SELECT * FROM creances_clients WHERE id = ?");
        $stmt->execute([$creance_id]);
        $creance = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($creance) {
            $nouveau_paye   = $creance['montant_paye_devise'] + $montant_verse_devise;
            $reste_a_payer  = $creance['montant_total_devise'] - $nouveau_paye;
            $statut         = ($reste_a_payer <= 0) ? "Soldé" : "En cours";

            //  Mise à jour de la créance
            $stmt = $conn->prepare("
                UPDATE creances_clients
                SET montant_paye = ?, reste_a_payer = ?, statut = ?
                WHERE id = ?
            ");
            $stmt->execute([$nouveau_paye, max(0, $reste_a_payer), $statut, $creance_id]);

            //  Mettre à jour la table ventes liée (si vente_id existe)
            if (!empty($creance['vente_id'])) {
                $vente_id = $creance['vente_id'];

                // Récupérer la vente
                $stmt = $conn->prepare("SELECT * FROM ventes WHERE id = ?");
                $stmt->execute([$vente_id]);
                $vente = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($vente) {
                    $new_montant_verse = $vente['montant_verse'] + $montant_verse_devise;
                    $new_reste         = $vente['totalTTC'] - $new_montant_verse;
                    $vente_statut      = ($new_reste <= 0) ? "Payé" : "Impayé";

                    $stmt = $conn->prepare("
                        UPDATE ventes
                        SET montant_verse = ?, reste_a_payer = ?, statut = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$new_montant_verse, max(0, $new_reste), $vente_statut, $vente_id]);
                }
            }

            // 🔹 Enregistrement en caisse
            $stmt = $conn->prepare("
                INSERT INTO operations_caisse 
                    (caisse_id, type_operation, montant, mode_paiement, reference_table, reference_id, date_operation)
                VALUES (?, 'entree', ?, ?, 'creances_clients', ?, NOW())
            ");
            $stmt->execute([$caisse_id, $montant_verse_devise, $mode_paiement, $creance_id]);

            // 🔹 Mise à jour solde caisse
            $stmt = $conn->prepare("
                UPDATE caisses 
                SET solde_actuel = solde_actuel + ?
                WHERE id = ?
            ");
            $stmt->execute([$montant_verse_devise, $caisse_id]);
        }
    }
}

header("Location: /eaglesuite/index.php?page=creances");
exit;
