<?php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth/middleware.php';

$db = new Database();
$conn = $db->getConnection();

$action = $_GET['action'] ?? 'list';

switch ($action) {

    // 🧾 Lister tous les produits avec stock total + stock par dépôt
    case 'list':
        $stmt = $conn->query("
            SELECT 
                p.id,
                p.nom,
                p.reference,
                p.description,
                p.prix_achat,
                p.prix_vente,
                p.image,
                c.nom AS categorie,
                p.categorie_id,
                u.nom AS unite,
                p.unite_id,
                COALESCE(SUM(sd.quantite), 0) AS stock_total
            FROM produits p
            LEFT JOIN categories c ON p.categorie_id = c.id
            LEFT JOIN unites u ON p.unite_id = u.id
            LEFT JOIN stock_depot sd ON sd.produit_id = p.id
            GROUP BY p.id
            ORDER BY p.nom
        ");
        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 🔹 Récupération du stock par dépôt
        $stmtDepot = $conn->prepare("
            SELECT d.nom AS depot, sd.produit_id, sd.quantite
            FROM stock_depot sd
            JOIN depots d ON d.id = sd.depot_id
        ");
        $stmtDepot->execute();
        $stocks = $stmtDepot->fetchAll(PDO::FETCH_ASSOC);

        // Regrouper les stocks par produit
        $stockByProduit = [];
        foreach ($stocks as $s) {
            $stockByProduit[$s['produit_id']][] = [
                'depot' => $s['depot'],
                'quantite' => (int)$s['quantite']
            ];
        }

        // Fusionner
        foreach ($produits as &$p) {
            $p['stocks_par_depot'] = $stockByProduit[$p['id']] ?? [];
        }

        respond(["success" => true, "data" => $produits]);
        break;

    // 🔍 Récupérer un produit par ID avec son stock total et les dépôts
    case 'get':
        $id = intval($_GET['id'] ?? 0);

        $stmt = $conn->prepare("
            SELECT 
                p.id,
                p.nom,
                p.reference,
                p.description,
                p.prix_achat,
                p.prix_vente,
                p.image,
                c.nom AS categorie,
                u.nom AS unite,
                COALESCE(SUM(sd.quantite), 0) AS stock_total
            FROM produits p
            LEFT JOIN categories c ON p.categorie_id = c.id
            LEFT JOIN unites u ON p.unite_id = u.id
            LEFT JOIN stock_depot sd ON sd.produit_id = p.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);

        // Récupération du détail par dépôt
        $stmtDepot = $conn->prepare("
            SELECT d.nom AS depot, sd.quantite
            FROM stock_depot sd
            JOIN depots d ON d.id = sd.depot_id
            WHERE sd.produit_id = ?
        ");
        $stmtDepot->execute([$id]);
        $produit['stocks_par_depot'] = $stmtDepot->fetchAll(PDO::FETCH_ASSOC);

        respond(["success" => true, "data" => $produit]);
        break;

    // ➕ Créer un produit (stock_total initial = 0)
    case 'create':
        $user = require_auth();
        $data = input_json();

        $stmt = $conn->prepare("
            INSERT INTO produits 
                (nom, reference, description, prix_achat, prix_vente, categorie_id, depot_id, image, unite_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ok = $stmt->execute([
            $data['nom'] ?? '',
            $data['reference'] ?? null,
            $data['description'] ?? null,
            $data['prix_achat'] ?? 0,
            $data['prix_vente'] ?? 0,
            $data['categorie_id'] ?? null,
            $data['depot_id'] ?? null,
            $data['image'] ?? null,
            $data['unite_id'] ?? null
        ]);

        respond(["success" => $ok, "message" => $ok ? "Produit ajouté avec succès" : "Erreur lors de l'ajout"]);
        break;

    // ✏️ Mettre à jour un produit (stock_total non modifié ici)
    case 'update':
        $user = require_auth();
        $data = input_json();
        $id = intval($data['id'] ?? 0);

        $fields = ['nom','reference','description','prix_achat','prix_vente','categorie_id','depot_id','image','unite_id'];
        $set = [];
        $values = [];
        foreach ($fields as $f) {
            if (isset($data[$f])) {
                $set[] = "$f = ?";
                $values[] = $data[$f];
            }
        }

        if (empty($set)) {
            respond(["success" => false, "message" => "Aucune donnée à mettre à jour"]);
        }

        $values[] = $id;
        $sql = "UPDATE produits SET " . implode(',', $set) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $ok = $stmt->execute($values);

        respond(["success" => $ok, "message" => $ok ? "Produit modifié avec succès" : "Échec de la mise à jour"]);
        break;

    // 🗑️ Supprimer un produit
    case 'delete':
        $user = require_auth();
        $id = intval($_GET['id'] ?? 0);

        // Supprimer d’abord les lignes dans stock_depot pour éviter les contraintes
        $conn->prepare("DELETE FROM stock_depot WHERE produit_id = ?")->execute([$id]);

        $stmt = $conn->prepare("DELETE FROM produits WHERE id = ?");
        $ok = $stmt->execute([$id]);
        respond(["success" => $ok, "message" => $ok ? "Produit supprimé" : "Erreur de suppression"]);
        break;

    default:
        respond(["success" => false, "message" => "Action inconnue"], 400);
}
