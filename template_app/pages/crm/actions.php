<?php
// pages/crm/actions.php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/check_auth.php";
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Non connecté']); exit; }

$action = $_REQUEST['action'] ?? '';
  // add interaction
  if ($action === 'add_interaction') {
    $client_id = intval($_POST['client_id']);
    if ($client_id <= 0) exit(json_encode(['success'=>false,'error'=>'Client invalide']));

    $type = $_POST['type'] ?? 'note';
    $sujet = trim($_POST['sujet']);
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("
        INSERT INTO crm_interactions (client_id, utilisateur_id, type, sujet, message, suivi)
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([$client_id, $_SESSION['user_id'], $type, $sujet, $message]);

    echo json_encode(['success'=>true]);
    exit;
}

  if ($action === 'update_interaction') {
    $id = intval($_POST['id']);
    $sujet = trim($_POST['sujet']);
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("
        UPDATE crm_interactions
        SET sujet=?, message=?
        WHERE id=?
    ");
    $stmt->execute([$sujet, $message, $id]);

    echo json_encode(['success'=>true]);
    exit;
}

if ($action === 'delete_interaction') {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM crm_interactions WHERE id=?");
    $stmt->execute([$id]);

    echo json_encode(['success'=>true]);
    exit;
}

  // add opportunity
  if ($action === 'add_opp') {
    $client_id = intval($_REQUEST['client_id']);
    $titre = trim($_REQUEST['titre'] ?? 'Opportunité');
    $montant = floatval($_REQUEST['montant'] ?? 0);
    $stmt = $conn->prepare("INSERT INTO crm_opportunites (client_id, titre, montant, utilisateur_id, probabilite) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$client_id, $titre, $montant, $_SESSION['user_id'], intval($_REQUEST['probabilite'] ?? 0)]);
    echo json_encode(['success'=>true,'id'=>$conn->lastInsertId()]); exit;
  }

  // add task
  if ($action === 'add_task') {
    $stmt = $conn->prepare("INSERT INTO crm_taches (client_id, opportunite_id, utilisateur_id, titre, description, date_echeance, priorite) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([ intval($_REQUEST['client_id']?:null), intval($_REQUEST['opportunite_id']?:null), $_SESSION['user_id'], $_REQUEST['titre'], $_REQUEST['description'], $_REQUEST['date_echeance']?:null, $_REQUEST['priorite']?:'moyenne' ]);
    echo json_encode(['success'=>true,'id'=>$conn->lastInsertId()]); exit;
  }

  if ($action === 'delete_task') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) return json_encode(['success'=>false,'error'=>'ID invalide']);

    $stmt = $conn->prepare("DELETE FROM crm_taches WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success'=>true]);
    exit;
}
if ($action === 'complete_task') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE crm_taches SET statut='terminee' WHERE id=?");
    $stmt->execute([$id]);

    echo json_encode(['success'=>true]);
    exit;
}
if ($action === 'update_task') {
    $id = intval($_POST['id']);
    $titre = $_POST['titre'];
    $desc = $_POST['description'];
    $echeance = $_POST['date_echeance'];
    $priorite = $_POST['priorite'];

    $stmt = $conn->prepare("UPDATE crm_taches SET titre=?, description=?, date_echeance=?, priorite=? WHERE id=?");
    $stmt->execute([$titre, $desc, $echeance, $priorite, $id]);

    echo json_encode(['success'=>true]);
    exit;
}

if ($action === 'create_client') {

    $nom     = trim($_POST['nom']);
    $tel     = trim($_POST['telephone']);
    $email   = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    $type    = $_POST['type']     ?? 'Particulier';
    $statut  = $_POST['statut']   ?? 'Actif';
    $origine = $_POST['origine']  ?? null;
    $secteur = $_POST['secteur']  ?? null;

    $exonere = isset($_POST['exonere']) ? 1 : 0;

    if ($nom === '') {
        echo json_encode(['success'=>false,'error'=>'Le nom est obligatoire']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO clients (nom, telephone, email, adresse, exonere, type, statut, origine, secteur, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");

    $stmt->execute([$nom, $tel, $email, $adresse, $exonere, $type, $statut, $origine, $secteur]);

    echo json_encode([
        'success' => true,
        'id'      => $conn->lastInsertId()
    ]);
    exit;
}

if ($action === 'update_client') {

    $id      = intval($_POST['idClient']);
    $nom     = trim($_POST['nom']);
    $tel     = trim($_POST['telephone']);
    $email   = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    $type    = $_POST['type']     ?? 'Particulier';
    $statut  = $_POST['statut']   ?? 'Actif';
    $origine = $_POST['origine']  ?? null;
    $secteur = $_POST['secteur']  ?? null;

    $exonere = isset($_POST['exonere']) ? 1 : 0;

    if ($nom === '') {
        echo json_encode(['success'=>false,'error'=>'Le nom ne peut pas être vide']);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE clients
        SET nom=?, telephone=?, email=?, adresse=?, exonere=?, type=?, statut=?, origine=?, secteur=?, updated_at=NOW()
        WHERE idClient=?
    ");

    $stmt->execute([$nom, $tel, $email, $adresse, $exonere, $type, $statut, $origine, $secteur, $id]);

    echo json_encode(['success'=>true]);
    exit;
}

  // delete client
  if ($action === 'delete_client') {

    $id = intval($_REQUEST['id']);

    if (!checkPermission('crm.clients.manage')) {
        echo json_encode(['success'=>false,'error'=>'Permission refusée']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM clients WHERE idClient = ?");
    $stmt->execute([$id]);

    echo json_encode(['success'=>true]);
    exit;
}



  // simple scoring endpoint: compute score from interactions + opps
  if ($action === 'score_client') {
    $client_id = intval($_GET['client_id']);

    $interactions = $conn->query("
        SELECT COUNT(*) FROM crm_interactions
        WHERE client_id = $client_id
    ")->fetchColumn();

    $won = $conn->query("
        SELECT COUNT(*) FROM crm_opportunites
        WHERE client_id = $client_id AND etat = 'gagne'
    ")->fetchColumn();

    $avgp = $conn->query("
        SELECT AVG(probabilite) FROM crm_opportunites
        WHERE client_id = $client_id
    ")->fetchColumn();

    $score = intval($interactions * 5 + ($avgp / 10) + ($won * 50));

    echo json_encode(['success'=>true,'score'=>$score]);
    exit;
}

if ($action === 'add_opportunity') {

    $client_id   = intval($_POST['client_id']);
    $titre       = trim($_POST['titre']);
    $montant     = floatval($_POST['montant']);
    $devise_id   = $_POST['devise_id'] ?: null;
    $etat        = $_POST['etat'];
    $prob        = intval($_POST['probabilite']);
    $date_cloture= $_POST['date_cloture_prevue'] ?: null;
    $user_id     = $_POST['utilisateur_id'] ?: null;
    $desc        = trim($_POST['description']);

    if ($titre === "") {
        echo json_encode(['success'=>false, 'error'=>"Le titre est obligatoire"]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO crm_opportunites 
        (client_id, titre, montant, devise_id, etat, probabilite, date_cloture_prevue, utilisateur_id, description, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $client_id, $titre, $montant, $devise_id, $etat, $prob, $date_cloture, $user_id, $desc
    ]);

    echo json_encode(['success'=>true]);
    exit;
}


if ($action === 'get_opportunity') {

    $stmt = $conn->prepare("SELECT * FROM crm_opportunites WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $item]);
    exit;
}

if ($action === 'delete_opportunity') {
    $stmt = $conn->prepare("DELETE FROM crm_opportunites WHERE id=?");
    $stmt->execute([$_POST['id']]);

    echo json_encode(['success' => true]);
    exit;
}


if ($action === 'update_opportunity') {

    $stmt = $conn->prepare("
        UPDATE crm_opportunites
        SET titre=?, montant=?, devise_id=?, etat=?, probabilite=?, date_cloture_prevue=?, utilisateur_id=?, description=?
        WHERE id=?
    ");

    $stmt->execute([
        $_POST['titre'],
        $_POST['montant'] ?? 0,
        $_POST['devise_id'] ?: null,
        $_POST['etat'],
        $_POST['probabilite'] ?? 0,
        $_POST['date_cloture_prevue'] ?: null,
        $_POST['utilisateur_id'] ?: null,
        $_POST['description'] ?: null,
        $_POST['id']
    ]);

    echo json_encode(['success' => true]);
    exit;
}



if ($action === 'delete_opportunity_safe') {
    $id = intval($_POST['id']);

    $conn->prepare("INSERT INTO crm_activity_log (objet_type, objet_id, action, utilisateur_id, meta)
                    VALUES ('opportunity', ?, 'delete', ?, '{}')")
         ->execute([$id, $_SESSION['user_id']]);

    $conn->prepare("DELETE FROM crm_opportunites WHERE id=?")->execute([$id]);

    echo json_encode(['success'=>true]);
    exit;
}

// --------- GESTION DES STAGES (CRUD + reorder) -----------
if ($action === 'add_stage') {
    $nom = trim($_POST['nom'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $couleur = trim($_POST['couleur'] ?? 'secondary');
    if ($nom === '' || $slug === '') { echo json_encode(['success'=>false,'error'=>'Nom et slug requis']); exit; }
    $pos = $conn->query("SELECT IFNULL(MAX(position),-1)+1 FROM crm_stages")->fetchColumn();
    $stmt = $conn->prepare("INSERT INTO crm_stages (slug, nom, couleur, position) VALUES (?, ?, ?, ?)");
    $stmt->execute([$slug, $nom, $couleur, intval($pos)]);
    echo json_encode(['success'=>true,'id'=>$conn->lastInsertId()]); exit;
}

if ($action === 'update_stage') {
    $id = intval($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $couleur = trim($_POST['couleur'] ?? 'secondary');
    if ($id <= 0 || $nom === '') { echo json_encode(['success'=>false,'error'=>'Données manquantes']); exit; }
    $stmt = $conn->prepare("UPDATE crm_stages SET nom=?, slug=?, couleur=? WHERE id=?");
    $stmt->execute([$nom, $slug, $couleur, $id]);
    echo json_encode(['success'=>true]); exit;
}

if ($action === 'delete_stage') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['success'=>false,'error'=>'ID invalide']); exit; }
    // Avant suppression : re-classer opportunités vers 'prospect' (fallback)
    $fallback = $conn->prepare("SELECT slug FROM crm_stages ORDER BY position LIMIT 1");
    $fallback->execute();
    $fallbackSlug = $fallback->fetchColumn() ?: 'prospect';
    $conn->prepare("UPDATE crm_opportunites SET etat = ? WHERE etat = (SELECT slug FROM (SELECT slug FROM crm_stages WHERE id = ?) x)")->execute([$fallbackSlug, $id]);
    $stmt = $conn->prepare("DELETE FROM crm_stages WHERE id=?");
    $stmt->execute([$id]);
    echo json_encode(['success'=>true]); exit;
}

if ($action === 'reorder_stages') {
    // attente : POST['order'] = "id1,id2,id3..."
    $order = trim($_POST['order'] ?? '');
    if ($order === '') { echo json_encode(['success'=>false,'error'=>'Order manquant']); exit; }
    $ids = array_filter(explode(',', $order));
    $pos = 0;
    $stmt = $conn->prepare("UPDATE crm_stages SET position=? WHERE id=?");
    foreach ($ids as $id) {
        $stmt->execute([ $pos++, intval($id) ]);
    }
    echo json_encode(['success'=>true]); exit;
}