<?php
// rest_api/modules/finances.php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth/middleware.php';

$action = $_GET['action'] ?? 'creances';

switch($action) {
    case 'creances':
        $stmt = $conn->query("SELECT c.*, cl.nom AS client FROM creances_clients c LEFT JOIN clients cl ON c.client_id=cl.idClient ORDER BY c.date_creation DESC");
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    case 'regler_creance':
        $user = require_auth();
        $d = input_json();
        // attendre: creance_id, montant_paye, caisse_id, mode_paiement
        if (empty($d['creance_id']) || !isset($d['montant_paye'])) respond(["success"=>false,"message"=>"creance_id et montant_paye requis"],400);
        try {
            $conn->beginTransaction();
            $ins = $conn->prepare("INSERT INTO paiements_creances (creance_id, montant, date_paiement, utilisateur_id) VALUES (?, ?, NOW(), ?)");
            $ins->execute([$d['creance_id'], $d['montant_paye'], $user['id']]);
            // mettre à jour creance
            $upd = $conn->prepare("UPDATE creances_clients SET montant_paye = montant_paye + ?, statut = CASE WHEN montant_paye + ? >= montant_total THEN 'Soldé' ELSE 'En cours' END WHERE id = ?");
            $upd->execute([$d['montant_paye'], $d['montant_paye'], $d['creance_id']]);
            // enregistrer operation caisse si fournie
            if (!empty($d['caisse_id'])) {
                $conn->prepare("INSERT INTO operations_caisse (caisse_id, type_operation, montant, mode_paiement, reference_table, reference_id, description, utilisateur_id, date_operation) VALUES (?, 'entree', ?, ?, 'creances_clients', ?, ?, ?, NOW())")
                    ->execute([$d['caisse_id'], $d['montant_paye'], $d['mode_paiement'] ?? 'Espèces', $d['creance_id'], $d['description'] ?? null, $user['id']]);
                $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel + ? WHERE id = ?")->execute([$d['montant_paye'], $d['caisse_id']]);
            }
            $conn->commit();
            respond(["success"=>true]);
        } catch (Exception $e) {
            $conn->rollBack();
            respond(["success"=>false,"message"=>$e->getMessage()],500);
        }
        break;

    case 'dettes':
        $stmt = $conn->query("SELECT d.*, f.nom AS fournisseur FROM dettes_fournisseurs d LEFT JOIN fournisseurs f ON d.fournisseur_id=f.idFournisseur ORDER BY d.date_creation DESC");
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    default:
        respond(["success"=>false,"message"=>"Action inconnue"],400);
}
