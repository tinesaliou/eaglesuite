<?php
// rest_api/modules/tresorerie.php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/middleware.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    respond(["success" => false, "message" => "Échec de la connexion à la base de données"], 500);
}

$action = $_GET['action'] ?? 'dashboard';

function log_debug($data) {
    $file = __DIR__ . '/../logs/tresorerie_debug.log';
    file_put_contents($file, date('[Y-m-d H:i:s] ') . print_r($data, true) . "\n", FILE_APPEND);
}

// util helper
function fetchAllAssoc($stmt) {
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    // ----------------------------------------------------
    // DASHBOARD (situation globale)
    // GET ?action=dashboard
    // ----------------------------------------------------
    if ($action === 'dashboard') {
        $totalCaisses = $conn->query("SELECT COALESCE(SUM(solde_actuel),0) AS total FROM caisses")->fetchColumn();
        $creancesTotal = $conn->query("SELECT COALESCE(SUM(reste_a_payer),0) FROM creances_clients WHERE statut='En cours'")->fetchColumn();
        $dettesTotal = $conn->query("SELECT COALESCE(SUM(reste_a_payer),0) FROM dettes_fournisseurs WHERE statut='En cours'")->fetchColumn();
        $caisses = $conn->query("SELECT id, nom, type, solde_actuel, devise_id FROM caisses ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

        $operations = $conn->query("
            SELECT ao.*, c.nom AS caisse
            FROM autres_operations ao
            LEFT JOIN caisses c ON c.id = ao.caisse_id
            ORDER BY ao.date_operation DESC
            LIMIT 50
        ")->fetchAll(PDO::FETCH_ASSOC);

        respond([
            "success" => true,
            "data" => [
                "total_caisses" => (float)$totalCaisses,
                "creances" => (float)$creancesTotal,
                "dettes" => (float)$dettesTotal,
                "caisses" => $caisses,
                "operations" => $operations
            ]
        ]);
        exit;
    }

    // ----------------------------------------------------
    // CREATE OPERATION (encaissement / decaissement)
    // POST JSON body:
    // { caisse_id, type: 'entree'|'sortie', categorie, montant, commentaire, devise_id, montant_devise, taux_change, utilisateur_id }
    // ----------------------------------------------------
    if ($action === 'operation_create') {
        $d = input_json();
        log_debug(['operation_create_payload' => $d]);

        $required = ['caisse_id','type','categorie','montant'];
        foreach ($required as $r) {
            if (!isset($d[$r])) respond(["success"=>false,"message"=>"Champ manquant: $r"],400);
        }

        $caisse_id = intval($d['caisse_id']);
        $type = $d['type'] === 'sortie' ? 'sortie' : 'entree';
        $categorie = substr(trim($d['categorie']),0,100);
        $montant = floatval($d['montant']);
        $commentaire = $d['commentaire'] ?? '';
        $devise_id = $d['devise_id'] ?? 1;
        $montant_devise = $d['montant_devise'] ?? null;
        $taux_change = $d['taux_change'] ?? null;
        $utilisateur_id = $d['utilisateur_id'] ?? null;

        try {
            $conn->beginTransaction();

            // insert autres_operations
            $stmt = $conn->prepare("INSERT INTO autres_operations (caisse_id, type, categorie, montant, date_operation, commentaire, utilisateur_id, devise_id, montant_devise, taux_change) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
            $stmt->execute([$caisse_id, $type, $categorie, $montant, $commentaire, $utilisateur_id, $devise_id, $montant_devise, $taux_change]);

            // update caisse solde
            if ($type === 'entree') {
                $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel + ? WHERE id = ?")->execute([$montant, $caisse_id]);
            } else {
                $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel - ? WHERE id = ?")->execute([$montant, $caisse_id]);
            }

            $conn->commit();
            respond(["success"=>true,"message"=>"Opération enregistrée"]);
        } catch (Exception $e) {
            $conn->rollBack();
            log_debug(['op_create_error'=>$e->getMessage()]);
            respond(["success"=>false,"message"=>"Erreur: ".$e->getMessage()],500);
        }
        exit;
    }

    // ----------------------------------------------------
    // LIST CREANCES (GET)
    // GET ?action=creances_list
    // ----------------------------------------------------
    if ($action === 'creances_list') {
        $rows = $conn->query("SELECT cr.*, c.nom AS client_nom, d.symbole AS symbole, d.taux_par_defaut AS taux, cr.reste_a_payer / d.taux_par_defaut AS reste_devise

      FROM creances_clients cr 
        LEFT JOIN clients c ON c.idClient = cr.client_id 
        LEFT JOIN ventes v ON v.id = cr.vente_id
        LEFT JOIN devises d ON v.devise_id = d.id
        
         WHERE 
        cr.reste_a_payer IS NOT NULL 
        AND cr.reste_a_payer <> 0 ORDER BY cr.date_creation DESC")->fetchAll(PDO::FETCH_ASSOC);
        respond(["success"=>true,"data"=>$rows]);
        exit;
    }

    // ----------------------------------------------------
    // PAYER CREANCE (POST)
    // POST JSON { id, montant_paye, caisse_id, mode_paiement, utilisateur_id }
    // ----------------------------------------------------
    if ($action === 'creance_pay') {
        $d = input_json();
        log_debug(['creance_pay'=>$d]);
        $id = intval($d['id'] ?? 0);
        $montant = floatval($d['montant_paye'] ?? 0);
        $caisse_id = intval($d['caisse_id'] ?? 0);
        $mode_paiement = $d['mode_paiement'] ?? 'Espèces';
        $utilisateur_id = $d['utilisateur_id'] ?? null;
        if ($id<=0 || $montant <= 0) respond(["success"=>false,"message"=>"Données invalides"],400);

        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("SELECT montant_paye, montant_total FROM creances_clients WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) respond(["success"=>false,"message"=>"Créance introuvable"],404);

            $nouveauPaye = floatval($row['montant_paye']) + $montant;
            $reste = floatval($row['montant_total']) - $nouveauPaye;
            $statut = $reste <= 0 ? 'Soldé' : 'En cours';

            $conn->prepare("UPDATE creances_clients SET montant_paye = ?, reste_a_payer = ?, statut = ? WHERE id = ?")
                 ->execute([$nouveauPaye, $reste, $statut, $id]);

            // enregistrement operation caisse
            if ($caisse_id>0) {
                $conn->prepare("INSERT INTO operations_caisse (caisse_id, type_operation, montant, mode_paiement, reference_table, reference_id, date_operation) VALUES (?, 'entree', ?, ?, 'creances_clients', ?, NOW())")
                     ->execute([$caisse_id, $montant, $mode_paiement, $id]);
                $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel + ? WHERE id = ?")->execute([$montant, $caisse_id]);
            }

            $conn->commit();
            respond(["success"=>true,"message"=>"Paiement enregistré", "reste" => $reste, "statut" => $statut]);
        } catch (Exception $e) {
            $conn->rollBack();
            log_debug(['creance_pay_err'=>$e->getMessage()]);
            respond(["success"=>false,"message"=>"Erreur: ".$e->getMessage()],500);
        }
        exit;
    }

    // ----------------------------------------------------
    // LIST DETTES
    // GET ?action=dettes_list
    // ----------------------------------------------------
    if ($action === 'dettes_list') {
        $rows = $conn->query("SELECT dt.*, f.nom AS fournisseur_nom FROM dettes_fournisseurs dt LEFT JOIN fournisseurs f ON f.id = dt.fournisseur_id WHERE 
        dt.reste_a_payer IS NOT NULL 
        AND dt.reste_a_payer <> 0 ORDER BY dt.date_creation DESC")->fetchAll(PDO::FETCH_ASSOC);
        respond(["success"=>true,"data"=>$rows]);
        exit;
    }

    // ----------------------------------------------------
    // PAYER DETTE (POST)
    // POST JSON { id, montant_paye, caisse_id, mode_paiement, utilisateur_id }
    // ----------------------------------------------------
    if ($action === 'dette_pay') {
        $d = input_json();
        log_debug(['dette_pay'=>$d]);
        $id = intval($d['id'] ?? 0);
        $montant = floatval($d['montant_paye'] ?? 0);
        $caisse_id = intval($d['caisse_id'] ?? 0);
        $mode_paiement = $d['mode_paiement'] ?? 'Virement';
        $utilisateur_id = $d['utilisateur_id'] ?? null;
        if ($id<=0 || $montant <= 0) respond(["success"=>false,"message"=>"Données invalides"],400);

        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("SELECT montant_paye, montant_total FROM dettes_fournisseurs WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) respond(["success"=>false,"message"=>"Dette introuvable"],404);

            $nouveauPaye = floatval($row['montant_paye']) + $montant;
            $reste = floatval($row['montant_total']) - $nouveauPaye;
            $statut = $reste <= 0 ? 'Soldé' : 'En cours';

            $conn->prepare("UPDATE dettes_fournisseurs SET montant_paye = ?, reste_a_payer = ?, statut = ? WHERE id = ?")
                 ->execute([$nouveauPaye, $reste, $statut, $id]);

            // opération caisse (sortie)
            if ($caisse_id>0) {
                $conn->prepare("INSERT INTO operations_caisse (caisse_id, type_operation, montant, mode_paiement, reference_table, reference_id, date_operation) VALUES (?, 'sortie', ?, ?, 'dettes_fournisseurs', ?, NOW())")
                     ->execute([$caisse_id, $montant, $mode_paiement, $id]);
                $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel - ? WHERE id = ?")->execute([$montant, $caisse_id]);
            }

            $conn->commit();
            respond(["success"=>true,"message"=>"Paiement dette enregistré", "reste" => $reste, "statut" => $statut]);
        } catch (Exception $e) {
            $conn->rollBack();
            log_debug(['dette_pay_err'=>$e->getMessage()]);
            respond(["success"=>false,"message"=>"Erreur: ".$e->getMessage()],500);
        }
        exit;
    }

    // default
    respond(["success"=>false,"message"=>"Action inconnue"],400);
} catch (Exception $e) {
    log_debug(['global_error'=>$e->getMessage()]);
    respond(["success"=>false,"message"=>"Erreur serveur: ".$e->getMessage()],500);
}
