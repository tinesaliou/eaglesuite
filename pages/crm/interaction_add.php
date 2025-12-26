<?php
// pages/crm/actions.php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/check_auth.php";
header('Content-Type: application/json; charset=utf-8');
if(empty($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Non connecté']); exit; }

$action = $_POST['action'] ?? '';

try {
  if($action === 'add_interaction'){
    $client_id = intval($_POST['client_id']);
    $type = $_POST['type'] ?? 'note';
    $sujet = $_POST['sujet'] ?? null;
    $message = $_POST['message'] ?? null;
    $stmt = $conn->prepare("INSERT INTO crm_interactions (client_id, utilisateur_id, type, sujet, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$client_id, $_SESSION['user_id'], $type, $sujet, $message]);
    echo json_encode(['success'=>true,'id'=>$conn->lastInsertId()]);
    exit;
  }

  if($action === 'add_opp'){
    $client_id = intval($_POST['client_id']);
    $titre = trim($_POST['titre'] ?? 'Nouvelle opportunité');
    $montant = floatval($_POST['montant'] ?? 0);
    $stmt = $conn->prepare("INSERT INTO crm_opportunities (client_id, titre, montant, utilisateur_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$client_id, $titre, $montant, $_SESSION['user_id']]);
    echo json_encode(['success'=>true,'id'=>$conn->lastInsertId()]);
    exit;
  }

  if($action === 'delete_client'){
    $id = intval($_POST['id']);
    // permission check
    if(!checkPermission('users.manage') && !checkPermission('clients.view')) {
      echo json_encode(['success'=>false,'error'=>'Permission refusée']); exit;
    }
    $stmt = $conn->prepare("DELETE FROM clients WHERE idClient = ?");
    $stmt->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
  }

  echo json_encode(['success'=>false,'error'=>'Action inconnue']);
} catch(Exception $e){
  echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
