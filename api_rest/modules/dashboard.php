<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/middleware.php'; //  sécurité ajoutée

$db = new Database();
$conn = $db->getConnection();

$action = $_GET['action'] ?? 'kpis';
//$user = require_auth(); // 🔒 vérifie que l’utilisateur est authentifié

try {
    switch ($action) {

        //  1 Indicateurs principaux
        case 'kpis':
            $stmt = $conn->query("SELECT COALESCE(SUM(totalTTC),0) AS total FROM ventes WHERE DATE(date_vente)=CURDATE()");
            $ventesJour = (float)$stmt->fetchColumn();

            $stmt = $conn->query("SELECT COALESCE(SUM(totalTTC),0) AS total FROM ventes WHERE YEAR(date_vente)=YEAR(CURDATE()) AND MONTH(date_vente)=MONTH(CURDATE())");
            $ventesMois = (float)$stmt->fetchColumn();

            $stmt = $conn->query("SELECT COUNT(*) FROM produits");
            $produits = (int)$stmt->fetchColumn();

            $stmt = $conn->query("SELECT COUNT(*) FROM clients");
            $clients = (int)$stmt->fetchColumn();

            respond([
                "success" => true,
                "data" => [
                    "ventes_jour" => $ventesJour,
                    "ventes_mois" => $ventesMois,
                    "produits_total" => $produits,
                    "clients_total" => $clients,
                ]
            ]);
            break;

        //  2 Ventes mensuelles sur 12 mois
        case 'sales_months':
            $stmt = $conn->query("
                SELECT DATE_FORMAT(date_vente, '%Y-%m') AS mois, 
                       COALESCE(SUM(totalTTC),0) AS total
                FROM ventes
                WHERE date_vente >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                GROUP BY mois
                ORDER BY mois ASC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $labels = array_column($rows, 'mois');
            $values = array_map('floatval', array_column($rows, 'total'));

            respond([
                "success" => true,
                "labels" => $labels,
                "values" => $values
            ]);
            break;

        //  3 Top produits vendus
        case 'top_products':
            $stmt = $conn->query("
                SELECT p.id, p.nom, COALESCE(SUM(vd.quantite),0) AS qte
                FROM ventes_details vd
                INNER JOIN produits p ON p.id = vd.produit_id
                GROUP BY p.id, p.nom
                ORDER BY qte DESC
                LIMIT 10
            ");
            respond([
                "success" => true,
                "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);
            break;

        //  4 Produits sous seuil d’alerte
        case 'stock_alerts':
            $stmt = $conn->query("
                SELECT p.id, p.nom, 
                       COALESCE(SUM(sd.quantite),0) AS stock_total, 
                       p.seuil_alerte
                FROM produits p
                LEFT JOIN stock_depot sd ON sd.produit_id = p.id
                GROUP BY p.id
                HAVING stock_total <= p.seuil_alerte
                ORDER BY stock_total ASC
            ");
            respond([
                "success" => true,
                "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);
            break;

        //  Caisses & dernières opérations
        case 'cash_summary':
            $out = [];

            //  Liste des caisses
            $stmt = $conn->query("
                SELECT id, nom, solde_actuel 
                FROM caisses 
                ORDER BY nom
            ");
            $out['caisses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //  Dernières opérations
            $stmt = $conn->query("
                SELECT oc.id, oc.type_operation, oc.montant, oc.date_operation, 
                       c.nom AS caisse
                FROM operations_caisse oc
                LEFT JOIN caisses c ON c.id = oc.caisse_id
                ORDER BY oc.date_operation DESC
                LIMIT 5
            ");
            $out['recent_ops'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            respond([
                "success" => true,
                "data" => $out
            ]);
            break;

        default:
            respond([
                "success" => false,
                "message" => "Action inconnue"
            ], 400);
    }

} catch (Exception $e) {
    respond([
        "success" => false,
        "message" => "Erreur serveur : " . $e->getMessage()
    ], 500);
}
