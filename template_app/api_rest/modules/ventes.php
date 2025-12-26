<?php
// rest_api/modules/ventes.php
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
    $file = __DIR__ . '/../logs/ventes_debug.log';
    file_put_contents($file, date('[Y-m-d H:i:s] ') . print_r($data, true) . "\n", FILE_APPEND);
}

try {
    switch ($action) {

        // ============================================================
        // 1️⃣ LISTE
        // ============================================================
        case 'list':
            $q = $_GET['q'] ?? '';
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = max(10, intval($_GET['perPage'] ?? 50));
            $offset = ($page - 1) * $perPage;

            $query = "
                SELECT v.*, c.nom AS client_nom, d.symbole AS devise_symbole, d.code AS devise_code
                FROM ventes v
                LEFT JOIN clients c ON c.idClient = v.client_id
                LEFT JOIN devises d ON d.id = v.devise_id
            ";

            if ($q !== '') {
                $query .= " WHERE v.numero LIKE :q OR c.nom LIKE :q ";
            }

            $query .= " ORDER BY v.date_vente DESC LIMIT :offset, :limit";

            $stmt = $conn->prepare($query);
            if ($q !== '') $stmt->bindValue(':q', "%$q%");
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->execute();

            respond(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        // ============================================================
        // 2️⃣ GET
        // ============================================================
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) respond(["success" => false, "message" => "ID manquant"], 400);

            $stmt = $conn->prepare("
                SELECT v.*, c.nom AS client_nom, c.exonere, d.symbole AS devise_symbole, d.code AS devise_code
                FROM ventes v
                LEFT JOIN clients c ON c.idClient = v.client_id
                LEFT JOIN devises d ON d.id = v.devise_id
                WHERE v.id = ?
            ");
            $stmt->execute([$id]);
            $vente = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$vente) respond(["success" => false, "message" => "Vente introuvable"], 404);

            $stmt = $conn->prepare("
                SELECT vd.*, p.nom AS produit_nom
                FROM ventes_details vd
                LEFT JOIN produits p ON p.id = vd.produit_id
                WHERE vd.vente_id = ?
            ");
            $stmt->execute([$id]);
            $vente['details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            respond(["success" => true, "data" => $vente]);
            break;

        // ============================================================
        // 3️⃣ CREATE
        // ============================================================
        case 'create':
            $data = input_json();
            log_debug(['action' => 'create', 'payload' => $data]);

            if (empty($data['client_id']) || empty($data['produits']) || !is_array($data['produits'])) {
                respond(["success" => false, "message" => "Données incomplètes"], 400);
            }

            $conn->beginTransaction();

            try {
                // Génération du numéro
                $annee = date("Y");
                $mois = date("m");
                $stmt = $conn->prepare("SELECT COUNT(*) FROM ventes WHERE YEAR(date_vente)=? AND MONTH(date_vente)=?");
                $stmt->execute([$annee, $mois]);
                $ordre = str_pad($stmt->fetchColumn() + 1, 2, "0", STR_PAD_LEFT);
                $numero = "FAC-{$annee}/{$mois}-{$ordre}";

                // Données
                $client_id = intval($data['client_id']);
                $produits = $data['produits'];
                $taux_change = floatval($data['taux_change'] ?? 1);
                $remise = floatval($data['remise'] ?? 0);
                $montant_verse_devise = floatval($data['montant_verse'] ?? 0);
                $mode_paiement = $data['mode_paiement'] ?? 'Espèces';
                $type_vente = $data['type_vente'] ?? 'Comptant';
                $commentaire = $data['commentaire'] ?? '';
                $devise_id = intval($data['devise_id'] ?? 1);
                $entreprise_id = 1;

                // Vérif client exonéré
                $stmt = $conn->prepare("SELECT exonere FROM clients WHERE idClient = ?");
                $stmt->execute([$client_id]);
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

                    if ($qte > $stock) {
                        $conn->rollBack();
                        respond([
                            "success" => false,
                            "message" => "Stock insuffisant pour le produit ID $pid (stock dispo : $stock, demandé : $qte)"
                        ], 400);
                    }

                    $stmt = $conn->prepare("SELECT prix_vente FROM produits WHERE id=?");
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

                // ✅ Si montant_verse_devise > montant_devise → on limite le montant_verse
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
                    INSERT INTO ventes (
                        numero, client_id, date_vente, totalHT, taxe, remise, totalTTC,
                        montant_verse, reste_a_payer, type_vente, mode_paiement, statut,
                        commentaire, devise_id, montant_devise, taux_change, entreprise_id,
                        utilisateur_id, created_at
                    )
                    VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $numero, $client_id, $totalHT, $taxe, $remise, $totalTTC, $montant_verse,
                    $reste_a_payer, $type_vente, $mode_paiement, $statut, $commentaire,
                    $devise_id, $montant_devise, $taux_change, $entreprise_id, $utilisateur_id
                ]);

                $vente_id = $conn->lastInsertId();

                // Détails vente + maj stock
                foreach ($produits as $p) {
                    $pid = intval($p['id']);
                    $qte = intval($p['quantite']);
                    $depot_id = intval($p['depot_id'] ?? 0);

                    $stmt = $conn->prepare("SELECT prix_vente FROM produits WHERE id=?");
                    $stmt->execute([$pid]);
                    $prix = floatval($stmt->fetchColumn() ?? 0);

                    $conn->prepare("
                        INSERT INTO ventes_details (vente_id, produit_id, quantite, prix_unitaire, depot_id)
                        VALUES (?, ?, ?, ?, ?)
                    ")->execute([$vente_id, $pid, $qte, $prix, $depot_id]);

                    $conn->prepare("
                        UPDATE stock_depot SET quantite = quantite - ? WHERE produit_id=? AND depot_id=?
                    ")->execute([$qte, $pid, $depot_id]);

                    $conn->prepare("
                        INSERT INTO mouvements_stock (produit_id, depot_source_id, quantite, type, reference_table, reference_id, date_mouvement)
                        VALUES (?, ?, ?, 'vente', 'ventes', ?, NOW())
                    ")->execute([$pid, $depot_id, $qte, $vente_id]);
                }

                // Créance client
                $statut_creance = ($reste_a_payer > 0) ? "En cours" : "Soldé";
                $conn->prepare("
                    INSERT INTO creances_clients (vente_id, client_id, montant_total, montant_paye, reste_a_payer, statut, date_creation)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ")->execute([$vente_id, $client_id, $totalTTC, $montant_verse, $reste_a_payer, $statut_creance]);

                // Opération caisse
                if ($montant_verse > 0 && $caisse_id) {
                    $conn->prepare("
                        INSERT INTO operations_caisse (caisse_id, type_operation, montant, devise_id, mode_paiement, reference_table, reference_id, date_operation)
                        VALUES (?, 'entree', ?, ?, ?, 'ventes', ?, NOW())
                    ")->execute([$caisse_id, $montant_verse, $devise_id, $mode_paiement, $vente_id]);

                    $conn->prepare("UPDATE caisses SET solde_actuel = solde_actuel + ? WHERE id=?")
                         ->execute([$montant_verse, $caisse_id]);
                }

                $conn->commit();
                respond(["success" => true, "message" => "Vente enregistrée avec succès", "id" => $vente_id]);
            } catch (Exception $e) {
                $conn->rollBack();
                log_debug(['create_error' => $e->getMessage()]);
                respond(["success" => false, "message" => "Erreur création vente: " . $e->getMessage()], 500);
            }
            break;

        // ============================================================
        // 4️⃣ ANNULATION
        // ============================================================
        case 'cancel':
            $data = input_json();
            $id = intval($data['id'] ?? 0);
            if ($id <= 0) respond(["success" => false, "message" => "ID manquant"], 400);

            $conn->beginTransaction();
            try {
                $conn->prepare("UPDATE ventes SET annule=1, statut='Annulé' WHERE id=?")->execute([$id]);

                $stmt = $conn->prepare("SELECT produit_id, quantite, depot_id FROM ventes_details WHERE vente_id=?");
                $stmt->execute([$id]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                    $conn->prepare("
                        UPDATE stock_depot SET quantite = quantite + ? WHERE produit_id=? AND depot_id=?
                    ")->execute([$r['quantite'], $r['produit_id'], $r['depot_id']]);
                }

                $conn->prepare("UPDATE creances_clients SET statut='Annulé' WHERE vente_id=?")->execute([$id]);

                $conn->commit();
                respond(["success" => true, "message" => "Vente annulée"]);
            } catch (Exception $e) {
                $conn->rollBack();
                log_debug(['cancel_error' => $e->getMessage()]);
                respond(["success" => false, "message" => "Erreur annulation: " . $e->getMessage()], 500);
            }
            break;

            // ============================================================
        // 5️⃣ TICKET
        // ============================================================
        case 'ticket':
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) respond(["success" => false, "message" => "ID manquant"], 400);

            $stmt = $conn->prepare("
                 SELECT v.*, c.nom AS client_nom, c.telephone AS client_tel,e.logo AS entreprise_logo, e.nom AS entreprise_nom,e.adresse AS entreprise_adresse,
                e.telephone AS entreprise_telephone,e.email AS entreprise_email
                FROM ventes v
                LEFT JOIN clients c ON c.idClient = v.client_id
                LEFT JOIN entreprise e ON e.id = v.entreprise_id
                WHERE v.id = ?
            ");
            $stmt->execute([$id]);
            $v = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($v['entreprise_logo'])) {
                $v['entreprise_logo'] = '/public/' . ltrim($v['entreprise_logo'], '/');
            }
            if (!$v) respond(["success" => false, "message" => "Vente introuvable"], 404);

            $stmt = $conn->prepare("
                SELECT vd.*, p.nom AS produit_nom
                FROM ventes_details vd
                LEFT JOIN produits p ON p.id = vd.produit_id
                WHERE vd.vente_id = ?
            ");
            $stmt->execute([$id]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            respond(["success" => true, "data" => ["vente" => $v, "details" => $details]]);
            break;

        default:
            respond(["success" => false, "message" => "Action inconnue"], 400);
    }

} catch (Exception $e) {
    log_debug(['global_error' => $e->getMessage()]);
    respond(["success" => false, "message" => "Erreur serveur: " . $e->getMessage()], 500);
}
