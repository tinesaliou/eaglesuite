<?php
require_once __DIR__ . "/../../../config/db.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Accès interdit");
}

/* Session ouverte */
$stmt = $conn->prepare("
    SELECT *
    FROM sessions_caisse
    WHERE utilisateur_id = ? AND statut = 'ouverte'
");
$stmt->execute([$_SESSION['user_id']]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    die("Aucune caisse ouverte");
}

$session_id = $session['id'];
$caisse_id  = $session['caisse_id'];
$reels      = $_POST['reel'] ?? [];
$commentaire = $_POST['commentaire'] ?? null;

$conn->beginTransaction();

try {

    $total_theorique = 0;
    $total_reel = 0;

    foreach ($reels as $caisse_type_id => $solde_reel) {

        /* Solde initial */
        $stmt = $conn->prepare("
            SELECT solde_initial
            FROM caisse_types
            WHERE id = ?
        ");
        $stmt->execute([$caisse_type_id]);
        $solde_initial = (float)$stmt->fetchColumn();

        /* Entrées */
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(montant),0)
            FROM operations_caisse
            WHERE caisse_type_id = ?
              AND type_operation = 'entree'
              AND reference_id = ?
              AND reference_table = 'session'
              AND mode_paiement_id = ?
        ");
        $stmt->execute([$caisse_id, $session_id, $caisse_type_id]);
        $entrees = (float)$stmt->fetchColumn();

        /* Sorties */
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(montant),0)
            FROM operations_caisse
            WHERE caisse_type_id = ?
              AND type_operation = 'sortie'
              AND reference_id = ?
              AND reference_table = 'session'
              AND mode_paiement_id = ?
        ");
        $stmt->execute([$caisse_id, $session_id, $caisse_type_id]);
        $sorties = (float)$stmt->fetchColumn();

        $theorique = $solde_initial + $entrees - $sorties;
        $ecart = (float)$solde_reel - $theorique;

        /* Détail clôture */
        $conn->prepare("
            INSERT INTO clotures_caisse_details
            (session_caisse_id, caisse_type_id, solde_theorique, solde_reel, ecart)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $session_id,
            $caisse_type_id,
            $theorique,
            $solde_reel,
            $ecart
        ]);

        $total_theorique += $theorique;
        $total_reel += (float)$solde_reel;
    }

    $ecart_global = $total_reel - $total_theorique;

    /* Fermeture session */
    $conn->prepare("
        UPDATE sessions_caisse
        SET date_cloture = NOW(),
            solde_theorique = ?,
            solde_reel = ?,
            ecart = ?,
            commentaire = ?,
            statut = 'fermee'
        WHERE id = ?
    ")->execute([
        $total_theorique,
        $total_reel,
        $ecart_global,
        $commentaire,
        $session_id
    ]);

    $conn->commit();

    header("Location: /eaglesuite/index.php?page=dashboard&cloture=ok");
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    die("Erreur clôture caisse : " . $e->getMessage());
}
