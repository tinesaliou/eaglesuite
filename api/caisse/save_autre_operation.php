<?php
require_once __DIR__ . "/../../config/db.php";
session_start();

if (empty($_SESSION['user']['id'])) {
    die("Utilisateur non authentifié");
}

$caisse_type_id = (int)$_POST['caisse_id'];
$type_operation = $_POST['type']; // entree | sortie
$categorie = trim($_POST['categorie']);
$montant = (float)$_POST['montant'];
$commentaire = trim($_POST['commentaire']);
$utilisateur_id = $_SESSION['user']['id'];

if (!$caisse_type_id || $montant <= 0 || !in_array($type_operation, ['entree','sortie'])) {
    die("Données invalides");
}

try {
    $conn->beginTransaction();

    /*  Insérer l’opération métier */
    $stmt = $conn->prepare("
        INSERT INTO autres_operations
        (caisse_type_id, type_operation, categorie, montant, commentaire, utilisateur_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $caisse_type_id,
        $type_operation,
        $categorie,
        $montant,
        $commentaire,
        $utilisateur_id
    ]);

    $autre_operation_id = $conn->lastInsertId();

    /*  Insérer dans operations_caisse */
    $stmt = $conn->prepare("
        INSERT INTO operations_caisse
        (
            session_caisse_id,
            caisse_type_id,
            type_operation,
            montant,
            devise_id, 
            mode_paiement_id,
            reference_table,
            reference_id,
            description,
            date_operation
        )
        VALUES (?, ?, ?, 'autres_operations', ?, ?, NOW())
    ");
    $stmt->execute([
        $session_caisse_id,
        $caisse_type_id,
        $type_operation,
        $montant,
        $autre_operation_id,
        $categorie . ($commentaire ? ' - '.$commentaire : '')
    ]);

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

    /*  Mettre à jour le solde caisse automatiquement */
    $stmt = $conn->prepare("
        UPDATE caisse_types
        SET solde_actuel = solde_actuel " . ($type_operation === 'entree' ? '+' : '-') . " ?
        WHERE id = ?
    ");
    $stmt->execute([$montant, $caisse_type_id]);

    $conn->commit();

    header("Location: /eaglesuite/index.php?page=autres_operations&success=1");
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    die("Erreur : " . $e->getMessage());
}
