<?php
require_once __DIR__ . "/../../config/db.php";
session_start();

$caisse_id = intval($_POST['caisse_id']);
$type = $_POST['type'];
$categorie = $_POST['categorie'];
$montant = floatval($_POST['montant']);
$commentaire = trim($_POST['commentaire']);
$utilisateur_id = $_SESSION['user_id'] ?? null;

if($caisse_id && $montant > 0){
    $stmt = $conn->prepare("INSERT INTO autres_operations (caisse_id, type, categorie, montant, commentaire, utilisateur_id) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$caisse_id, $type, $categorie, $montant, $commentaire, $utilisateur_id]);

    $stmt = $conn->prepare("
                    INSERT INTO operations_caisse 
                    (caisse_id, type_operation, montant, mode_paiement, reference_table, description, date_operation)
                    VALUES (?, ?, ?, ?, 'autres_operations', ?, NOW())
                ");
                $stmt->execute([$caisse_id,$type, $montant, $categorie, $commentaire]);

    
    // Mettre à jour solde caisse
    if($type == 'recette'){
        $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel + ? WHERE id=?")->execute([$montant, $caisse_id]);
    } else {
        $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel - ? WHERE id=?")->execute([$montant, $caisse_id]);
    }

    header("Location: /{{TENANT_DIR}}/index.php?page=autres_operations");
    exit;
} else {
    header("Location: /{{TENANT_DIR}}/index.php?page=autres_operations");
    exit;
}
