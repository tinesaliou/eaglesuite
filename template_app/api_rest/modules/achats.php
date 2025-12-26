<?php
// rest_api/modules/achat.php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/middleware.php';

$db = new Database();
$conn = $db->getConnection();

$user = require_auth(); // Auth obligatoire
$utilisateur_id = intval($user['sub']);
$role_id = intval($user['role_id']);

if (!$conn) {
    respond(["success" => false, "message" => "Erreur de connexion BD"], 500);
}

$action = $_GET['action'] ?? 'list';

// === LOG DEBUG ===
function log_debug($data) {
    $file = __DIR__ . '/../logs/achats_debug.log';
    file_put_contents($file, date('[Y-m-d H:i:s] ') . print_r($data, true) . "\n", FILE_APPEND);
}

try {
    switch ($action) {

        // ============================================================
        // 1 LISTE
        // ============================================================
        case 'list':
            $q = $_GET['q'] ?? '';
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = max(10, intval($_GET['perPage'] ?? 50));
            $offset = ($page - 1) * $perPage;

            $query = "
                SELECT a.*, f.nom AS fournisseur_nom, d.symbole AS devise_symbole, d.code AS devise_code
                FROM achats a
                LEFT JOIN fournisseurs f ON f.id = a.fournisseur_id
                LEFT JOIN devises d ON d.id = a.devise_id
            ";

            if ($q !== '') {
                $query .= " WHERE a.numero LIKE :q OR f.nom LIKE :q ";
            }

            $query .= " ORDER BY a.date_achat DESC LIMIT :offset, :limit";

            $stmt = $conn->prepare($query);
            if ($q !== '') $stmt->bindValue(':q', "%$q%");
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->execute();

            respond(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        // ============================================================
        // 2 GET
        // ============================================================
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) respond(["success" => false, "message" => "ID manquant"], 400);

            $stmt = $conn->prepare("
                SELECT a.*, f.nom AS fournisseur_nom, f.exonere, d.symbole AS devise_symbole, d.code AS devise_code
                FROM achats a
                LEFT JOIN fournisseurs f ON f.id = a.fournisseur_id
                LEFT JOIN devises d ON d.id = a.devise_id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $achat = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$achat) respond(["success" => false, "message" => "Achat introuvable"], 404);

            $stmt = $conn->prepare("
                SELECT ad.*, p.nom AS produit_nom
                FROM achats_details ad
                LEFT JOIN produits p ON p.id = ad.produit_id
                WHERE ad.achat_id = ?
            ");
            $stmt->execute([$id]);
            $achat['details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            respond(["success" => true, "data" => $achat]);
            break;

        // ============================================================
        // 3 CREATE
        // ============================================================
        case 'create':
            $data = input_json();
            log_debug(['action' => 'create', 'payload' => $data]);

            if (empty($data['fournisseur_id']) || empty($data['produits']) || !is_array($data['produits'])) {
                respond(["success" => false, "message" => "Données incomplètes"], 400);
            }

            $conn->beginTransaction();

            try {
                // Génération du numéro
                $annee = date("Y");
                $mois = date("m");
                $stmt = $conn->prepare("SELECT COUNT(*) FROM achats WHERE YEAR(date_achat)=? AND MONTH(date_achat)=?");
                $stmt->execute([$annee, $mois]);
                $ordre = str_pad($stmt->fetchColumn() + 1, 2, "0", STR_PAD_LEFT);
                $numero = "BC-{$annee}/{$mois}-{$ordre}";

                // Données
                $fournisseur_id = intval($data['fournisseur_id']);
                $produits = $data['produits'];
                $taux_change = floatval($data['taux_change'] ?? 1);
                $remise = floatval($data['remise'] ?? 0);
                $montant_verse_devise = floatval($data['montant_verse'] ?? 0);
                $mode_paiement = $data['mode_paiement'] ?? 'Espèces';
                $type_achat = $data['type_achat'] ?? 'Comptant';
                //$commentaire = $data['commentaire'] ?? '';
                $devise_id = intval($data['devise_id'] ?? 1);
                $entreprise_id = 1;

                // Vérif client exonéré
                $stmt = $conn->prepare("SELECT exonere FROM fournisseurs WHERE id = ?");
                $stmt->execute([$fournisseur_id]);
                $exonere = ($stmt->fetchColumn() == 1);

                // Calcul total HT + stock
                $totalHT = 0;
                foreach ($produits as $p) {
                    $pid = intval($p['id']);
                    $qte = intval($p['quantite']);
                    $depot_id = intval($p['depot_id'] ?? 0);

                    $stmt = $conn->prepare("SELECT quantite FROM stock_depot WHERE produit_id=? AND depot_id=?");
                    $stmt->execute([$pid, $depot_id]);
                    $stock = intval($stmt->fetchColumn() ?? 0);

                    /* if ($qte > $stock) {
                        $conn->rollBack();
                        respond([
                            "success" => false,
                            "message" => "Stock insuffisant pour le produit ID $pid (stock dispo : $stock, demandé : $qte)"
                        ], 400);
                    } */

                    $stmt = $conn->prepare("SELECT prix_achat FROM produits WHERE id=?");
                    $stmt->execute([$pid]);
                    $prix = floatval($stmt->fetchColumn() ?? 0);
                    $totalHT += $prix * $qte;
                }

                // Taxes et totaux
                $taxe = $exonere ? 0 : ($totalHT * 0.18);
                $totalTTC = $totalHT + $taxe - $remise;

                // Conversion devise
                $montant_verse = $montant_verse_devise * $taux_change;
                $montant_devise = ($taux_change > 0) ? ($totalTTC / $taux_change) : $totalTTC;

                //  Si montant_verse_devise > montant_devise → on limite le montant_verse
                if ($montant_verse_devise > $montant_devise) {
                    $montant_verse = $montant_devise * $taux_change;
                }

                $reste_a_payer = $totalTTC - $montant_verse;
                $statut = ($reste_a_payer <= 0) ? "Payé" : "Impayé";

                // Caisse selon mode paiement
                $map_caisses = [
                    "Espèces" => 1,
                    "Banque" => 2,
                    "Mobile Money" => 3,
                    "Virement" => 2,
                    "Chèque" => 2,
                ];
                $caisse_id = $map_caisses[$mode_paiement] ?? 1;

                // Insertion principale
                $stmt = $conn->prepare("
                    INSERT INTO achats (
                        numero, fournisseur_id, date_achat, totalHT, taxe, remise, totalTTC,
                        montant_verse, reste_a_payer, type_achat, mode_paiement, statut,
                        devise_id, montant_devise, taux_change, entreprise_id,
                        utilisateur_id, created_at
                    )
                    VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $numero, $fournisseur_id, $totalHT, $taxe, $remise, $totalTTC, $montant_verse,
                    $reste_a_payer, $type_achat, $mode_paiement, $statut,
                    $devise_id, $montant_devise, $taux_change, $entreprise_id, $utilisateur_id
                ]);

                $achat_id = $conn->lastInsertId();

                // Détails achat + maj stock
                foreach ($produits as $p) {
                    $pid = intval($p['id']);
                    $qte = intval($p['quantite']);
                    $depot_id = intval($p['depot_id'] ?? 0);

                    $stmt = $conn->prepare("SELECT prix_achat FROM produits WHERE id=?");
                    $stmt->execute([$pid]);
                    $prix = floatval($stmt->fetchColumn() ?? 0);

                    $conn->prepare("
                        INSERT INTO achats_details (achat_id, produit_id, quantite, prix_unitaire, depot_id)
                        VALUES (?, ?, ?, ?, ?)
                    ")->execute([$achat_id, $pid, $qte, $prix, $depot_id]);

                    $conn->prepare("
                        UPDATE stock_depot SET quantite = quantite + ? WHERE produit_id=? AND depot_id=?
                    ")->execute([$qte, $pid, $depot_id]);

                    $conn->prepare("
                        INSERT INTO mouvements_stock (produit_id, depot_source_id, quantite, type, reference_table, reference_id, date_mouvement)
                        VALUES (?, ?, ?, 'achat', 'achats', ?, NOW())
                    ")->execute([$pid, $depot_id, $qte, $achat_id]);
                }

                // Dette Fournisseur
                $statut_dette = ($reste_a_payer > 0) ? "En cours" : "Soldé";
                $conn->prepare("
                    INSERT INTO dettes_fournisseurs (achat_id, fournisseur_id, montant_total, montant_paye, reste_a_payer, statut, date_creation)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ")->execute([$achat_id, $fournisseur_id, $totalTTC, $montant_verse, $reste_a_payer, $statut_dette]);

                // Opération caisse
                if ($montant_verse > 0 && $caisse_id) {
                    $conn->prepare("
                        INSERT INTO operations_caisse (caisse_id, type_operation, montant, devise_id, mode_paiement, reference_table, reference_id, date_operation)
                        VALUES (?, 'sortie', ?, ?, ?, 'achats', ?, NOW())
                    ")->execute([$caisse_id, $montant_verse, $devise_id, $mode_paiement, $achat_id]);

                    $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel - ? WHERE id=?")
                         ->execute([$montant_verse, $caisse_id]);
                }

                $conn->commit();
                respond(["success" => true, "message" => "Achat enregistrée avec succès", "id" => $achat_id]);
            } catch (Exception $e) {
                $conn->rollBack();
                log_debug(['create_error' => $e->getMessage()]);
                respond(["success" => false, "message" => "Erreur création achat: " . $e->getMessage()], 500);
            }
            break;

        // ============================================================
        // 4 ANNULATION
        // ============================================================
        case 'cancel':
            $data = input_json();
            $id = intval($data['id'] ?? 0);
            if ($id <= 0) respond(["success" => false, "message" => "ID manquant"], 400);

            $conn->beginTransaction();
            try {
                $conn->prepare("UPDATE achats SET annule=1, statut='Annulé' WHERE id=?")->execute([$id]);

                $stmt = $conn->prepare("SELECT produit_id, quantite, depot_id FROM achats_details WHERE achat_id=?");
                $stmt->execute([$id]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $conn->prepare("
                        UPDATE stock_depot SET quantite = quantite - ? WHERE produit_id=? AND depot_id=?
                    ")->execute([$r['quantite'], $r['produit_id'], $r['depot_id']]);
                }

                $conn->prepare("UPDATE dettes_fournisseurs SET statut='Annulé' WHERE achat_id=?")->execute([$id]);

                $conn->commit();
                respond(["success" => true, "message" => "Achat annulé"]);
            } catch (Exception $e) {
                $conn->rollBack();
                log_debug(['cancel_error' => $e->getMessage()]);
                respond(["success" => false, "message" => "Erreur annulation: " . $e->getMessage()], 500);
            }
            break;

            // ============================================================
        // 5 TICKET
        // ============================================================
        case 'ticket':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) respond(["success" => false, "message" => "ID manquant"], 400);

            $stmt = $conn->prepare("
                 SELECT a.*, f.nom AS fournisseur_nom, f.telephone AS fournisseur_tel,e.logo AS entreprise_logo, e.nom AS entreprise_nom,e.adresse AS entreprise_adresse,
                e.telephone AS entreprise_telephone,e.email AS entreprise_email
                FROM achats a
                LEFT JOIN fournisseurs f ON f.id = a.fournisseur_id
                LEFT JOIN entreprise e ON e.id = a.entreprise_id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $a = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($a['entreprise_logo'])) {
                $a['entreprise_logo'] = '/public/' . ltrim($a['entreprise_logo'], '/');
            }
            if (!$a) respond(["success" => false, "message" => "Achat introuvable"], 404);

            $stmt = $conn->prepare("
                SELECT ad.*, p.nom AS produit_nom
                FROM achats_details ad
                LEFT JOIN produits p ON p.id = ad.produit_id
                WHERE ad.achat_id = ?
            ");
            $stmt->execute([$id]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            respond(["success" => true, "data" => ["achat" => $v, "details" => $details]]);
            break;

        default:
            respond(["success" => false, "message" => "Action inconnue"], 400);
    }

} catch (Exception $e) {
    log_debug(['global_error' => $e->getMessage()]);
    respond(["success" => false, "message" => "Erreur serveur: " . $e->getMessage()], 500);
}
