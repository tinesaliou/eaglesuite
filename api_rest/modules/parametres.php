<?php
// /modules/parametres.php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Action non reconnue'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    if (!$conn) throw new Exception("Erreur connexion DB");

    switch ($action) {

        // ---------------------------------------------------
        // get_all : entreprise + devises + tva + parametres_app
        // ---------------------------------------------------
        case 'get_all':
            $entreprise = $conn->query("SELECT * FROM entreprise LIMIT 1")->fetch(PDO::FETCH_ASSOC);

            // Devises
            $devises = $conn->query("SELECT * FROM devises ORDER BY est_base DESC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);

            // TVA
            // table nommée 'tva' ou 'tvas' selon ta base — j'utilise 'tva'
            $tva = $conn->query("SELECT * FROM tva ORDER BY taux ASC")->fetchAll(PDO::FETCH_ASSOC);

            // Paramètres (cle => valeur)
            $params = [];
            $paramRows = $conn->query("SELECT cle, valeur, `type` FROM parametres_app")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($paramRows as $p) {
                // cast JSON strings if type == 'json'
                if (isset($p['type']) && $p['type'] === 'json') {
                    $v = json_decode($p['valeur'], true);
                    $params[$p['cle']] = $v !== null ? $v : $p['valeur'];
                } else {
                    $params[$p['cle']] = $p['valeur'];
                }
            }

            $response = [
                'success' => true,
                'data' => [
                    'entreprise' => $entreprise,
                    'devises' => $devises,
                    'tva' => $tva,
                    'params' => $params,
                ]
            ];
            break;

        // ---------------------------------------------------
        // update_parametres : upsert clé/valeur dans parametres_app
        // body JSON: { "cle": "nom_cle", "valeur": "...", "type": "texte|nombre|bool|json" }
        // ---------------------------------------------------
        //case 'update_parametres':
        case 'update_parametre': // alias
            $data = json_decode();
            if (!$data || !isset($data['cle'])) {
                throw new Exception("Paramètre 'cle' manquant");
            }
            $cle = trim($data['cle']);
            $type = $data['type'] ?? 'texte';
            $valeur = $data['valeur'] ?? '';

            // if type json, encode value
            if ($type === 'json' && !is_string($valeur)) {
                $valeur = json_encode($valeur, JSON_UNESCAPED_UNICODE);
            }

            // On tente un UPDATE, sinon INSERT (pour compatibilité si pas d'index UNIQUE)
            $stmt = $conn->prepare("UPDATE parametres_app SET valeur = :valeur, `type` = :type, updated_at = NOW() WHERE cle = :cle");
            $stmt->execute([':valeur' => $valeur, ':type' => $type, ':cle' => $cle]);
            if ($stmt->rowCount() === 0) {
                $stmt2 = $conn->prepare("INSERT INTO parametres_app (cle, valeur, `type`, updated_at) VALUES (:cle, :valeur, :type, NOW())");
                $stmt2->execute([':cle' => $cle, ':valeur' => $valeur, ':type' => $type]);
            }

            $response = ['success' => true, 'message' => 'Paramètre enregistré'];
            break;

        // ---------------------------------------------------
        // update_tva : body { "tva": number } => wrapper to update_parametres
        // ---------------------------------------------------
        case 'update_tva':
            $data = json_decode();
            $t = $data['tva'] ?? null;
            if ($t === null) throw new Exception("TVA manquante");
            // store as nombre
            $stmt = $conn->prepare("UPDATE parametres_app SET valeur = :valeur, `type`='nombre', updated_at = NOW() WHERE cle = 'tva_par_defaut'");
            $stmt->execute([':valeur' => $t]);
            if ($stmt->rowCount() === 0) {
                $stmt2 = $conn->prepare("INSERT INTO parametres_app (cle, valeur, `type`, updated_at) VALUES ('tva_par_defaut', :valeur, 'nombre', NOW())");
                $stmt2->execute([':valeur' => $t]);
            }
            $response = ['success' => true, 'message' => 'TVA par défaut mise à jour'];
            break;

        // ---------------------------------------------------
        // update_entreprise
        // body: nom, adresse, telephone, email, forme_juridique
        // ---------------------------------------------------
        case 'update_entreprise':
            $data = json_decode();
            if (!$data) throw new Exception("Données manquantes");
            $stmt = $conn->prepare("UPDATE entreprise SET nom = :nom, adresse = :adresse, telephone = :telephone, email = :email, site_web = :site, ninea = :ninea, rccm = :rccm, logo = :logo WHERE id = 1");
            $stmt->execute([
                ':nom' => $data['nom'] ?? '',
                ':adresse' => $data['adresse'] ?? '',
                ':telephone' => $data['telephone'] ?? '',
                ':email' => $data['email'] ?? '',
                //':site' => $data['site_web'] ?? '',
                //':ninea' => $data['ninea'] ?? '',
                //':rccm' => $data['rccm'] ?? '',
                //':logo' => $data['logo'] ?? ''
            ]);
            $response = ['success' => true, 'message' => 'Entreprise mise à jour'];
            break;

        // ---------------------------------------------------
        // CRUD Devises
        // create_devise (POST JSON)
        // update_devise (POST JSON)
        // delete_devise (GET id)
        // set_devise_base (GET id)
        // ---------------------------------------------------
        case 'create_devise':
            $data = json_decode();
            if (!$data) throw new Exception("Données manquantes");
            $stmt = $conn->prepare("INSERT INTO devises (code, nom, symbole, taux_par_defaut, actif, est_base, date_mise_a_jour) VALUES (:code, :nom, :symbole, :taux, :actif, 0, NOW())");
            $stmt->execute([
                ':code' => $data['code'] ?? '',
                ':nom' => $data['nom'] ?? '',
                ':symbole' => $data['symbole'] ?? '',
                ':taux' => $data['taux_par_defaut'] ?? 1,
                ':actif' => $data['actif'] ?? 1
            ]);
            $response = ['success' => true, 'message' => 'Devise créée'];
            break;

        case 'update_devise':
            $data = json_decode();
            if (!$data || !isset($data['id'])) throw new Exception("ID devise manquant");
            $stmt = $conn->prepare("UPDATE devises SET code=:code, nom=:nom, symbole=:symbole, taux_par_defaut=:taux, actif=:actif, date_mise_a_jour=NOW() WHERE id = :id");
            $stmt->execute([
                ':code' => $data['code'] ?? '',
                ':nom' => $data['nom'] ?? '',
                ':symbole' => $data['symbole'] ?? '',
                ':taux' => $data['taux_par_defaut'] ?? 1,
                ':actif' => $data['actif'] ?? 1,
                //':est_base' => $data['est_base'] ?? 0,
                ':id' => $data['id']
            ]);

            // si est_base fourni et == 1 => set others to 0 and mark param
            if (!empty($data['est_base']) && intval($data['est_base']) === 1) {
                $conn->exec("UPDATE devises SET est_base = 0");
                $stmt2 = $conn->prepare("UPDATE devises SET est_base = 1 WHERE id = :id");
                $stmt2->execute([':id' => $data['id']]);
                // store base in parametres_app
                $stmtp = $conn->prepare("UPDATE parametres_app SET valeur = :val WHERE cle = 'devise_base_id'");
                $stmtp->execute([':val' => $data['id']]);
                if ($stmtp->rowCount() === 0) {
                    $stmtp2 = $conn->prepare("INSERT INTO parametres_app (cle, valeur, `type`, updated_at) VALUES ('devise_base_id', :val, 'nombre', NOW())");
                    $stmtp2->execute([':val' => $data['id']]);
                }
            }

            $response = ['success' => true, 'message' => 'Devise mise à jour'];
            break;

        case 'delete_devise':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) throw new Exception("ID invalide");
            $stmt = $conn->prepare("DELETE FROM devises WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $response = ['success' => true, 'message' => 'Devise supprimée'];
            break;

        case 'set_devise_base':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id <= 0) throw new Exception("ID invalide");
            $conn->beginTransaction();
            $conn->exec("UPDATE devises SET est_base = 0");
            $stmt = $conn->prepare("UPDATE devises SET est_base = 1 WHERE id = :id");
            $stmt->execute([':id' => $id]);
            // save into parametres_app
            $stmtp = $conn->prepare("UPDATE parametres_app SET valeur = :val WHERE cle = 'devise_base_id'");
            $stmtp->execute([':val' => $id]);
            if ($stmtp->rowCount() === 0) {
                $stmtp2 = $conn->prepare("INSERT INTO parametres_app (cle, valeur, `type`, updated_at) VALUES ('devise_base_id', :val, 'nombre', NOW())");
                $stmtp2->execute([':val' => $id]);
            }
            $conn->commit();
            $response = ['success' => true, 'message' => 'Devise de base mise à jour'];
            break;

        // ---------------------------------------------------
        // CRUD TVA items (optionnel)
        // ---------------------------------------------------
        case 'create_tva':
            $data = json_decode();
            $stmt = $conn->prepare("INSERT INTO tva (nom, taux, actif, created_at) VALUES (:nom, :taux, :actif, NOW())");
            $stmt->execute([':nom' => $data['nom'] ?? '', ':taux' => $data['taux'] ?? 0, ':actif' => $data['actif'] ?? 1]);
            $response = ['success' => true, 'message' => 'TVA créée'];
            break;

        case 'update_tva_item':
            $data = json_decode();
            $stmt = $conn->prepare("UPDATE tva SET nom = :nom, taux = :taux, actif = :actif WHERE id = :id");
            $stmt->execute([':nom' => $data['nom'] ?? '', ':taux' => $data['taux'] ?? 0, ':actif' => $data['actif'] ?? 1, ':id' => $data['id']]);
            $response = ['success' => true, 'message' => 'TVA mise à jour'];
            break;

        case 'delete_tva':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $stmt = $conn->prepare("DELETE FROM tva WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $response = ['success' => true, 'message' => 'TVA supprimée'];
            break;
        case 'check':
    $produit_id = $_GET['produit_id'] ?? 0;
    $depot_id = $_GET['depot_id'] ?? 0;

    $stmt = $conn->prepare("SELECT quantite FROM stock_depot WHERE produit_id=? AND depot_id=?");
    $stmt->execute([$produit_id, $depot_id]);
    $stock = $stmt->fetchColumn() ?? 0;

    respond(["success" => true, "stock" => (float)$stock]);
    break;


        default:
            $response = ['success' => false, 'message' => 'Action inconnue'];
            break;
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
