<?php
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $from_id    = intval($_POST['from_caisse_id'] ?? 0);
    $to_id      = intval($_POST['to_caisse_id'] ?? 0);
    $montant    = floatval($_POST['montant'] ?? 0);
    $commentaire= trim($_POST['commentaire'] ?? '');

    // Vérification des champs
    if ($from_id <= 0 || $to_id <= 0 || $from_id === $to_id || $montant <= 0) {
        die("❌ Données invalides.");
    }

    // Récupérer caisse source
    $stmt = $conn->prepare("SELECT * FROM caisses WHERE id = ?");
    $stmt->execute([$from_id]);
    $from_caisse = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer caisse destination
    $stmt = $conn->prepare("SELECT * FROM caisses WHERE id = ?");
    $stmt->execute([$to_id]);
    $to_caisse = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$from_caisse || !$to_caisse) {
        die("❌ Caisse introuvable.");
    }

    // Vérifier solde suffisant
    if ($from_caisse['solde_actuel'] < $montant) {
        die("❌ Solde insuffisant dans la caisse source.");
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("
            INSERT INTO caisses_transferts 
                (from_caisse_id, to_caisse_id, montant, commentaire, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$from_id, $to_id, $montant, $commentaire]);

        // Débiter la caisse source
        $stmt = $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel - ? WHERE id = ?");
        $stmt->execute([$montant, $from_id]);

        // Créditer la caisse destination
        $stmt = $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel + ? WHERE id = ?");
        $stmt->execute([$montant, $to_id]);

        // Enregistrer sortie dans opérations caisse (source)
        $stmt = $conn->prepare("
            INSERT INTO operations_caisse 
                (caisse_id, type_operation, montant, mode_paiement, reference_table, reference_id, description, date_operation)
            VALUES (?, 'sortie', ?, 'Transfert', 'caisses', ?, ?, NOW())
        ");
        $stmt->execute([$from_id, $montant, $to_id, "Transfert vers caisse ID $to_id. $commentaire"]);

        // Enregistrer entrée dans opérations caisse (destination)
        $stmt = $conn->prepare("
            INSERT INTO operations_caisse 
                (caisse_id, type_operation, montant, mode_paiement, reference_table, reference_id, description, date_operation)
            VALUES (?, 'entree', ?, 'Transfert', 'caisses', ?, ?, NOW())
        ");
        $stmt->execute([$to_id, $montant, $from_id, "Transfert depuis caisse ID $from_id. $commentaire"]);

        $conn->commit();

        header("Location: /eaglesuite/index.php?page=tresorerie");
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        die("❌ Erreur transfert : " . $e->getMessage());
    }
} else {
    die("❌ Accès non autorisé.");
}
