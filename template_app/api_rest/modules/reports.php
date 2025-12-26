<?php
// rest_api/modules/reports.php
require_once __DIR__ . '/../config/headers.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../auth/middleware.php';

$action = $_GET['action'] ?? 'summary';

switch($action) {
    case 'summary':
        $date_debut = $_GET['date_debut'] ?? date('Y-m-01');
        $date_fin = $_GET['date_fin'] ?? date('Y-m-t');
        $ventes = $conn->prepare("SELECT COUNT(*) nb, COALESCE(SUM(totalTTC),0) total FROM ventes WHERE DATE(date_vente) BETWEEN ? AND ?");
        $ventes->execute([$date_debut, $date_fin]);
        $achats = $conn->prepare("SELECT COUNT(*) nb, COALESCE(SUM(totalTTC),0) total FROM achats WHERE DATE(date_achat) BETWEEN ? AND ?");
        $achats->execute([$date_debut, $date_fin]);
        $stocks_alert = $conn->query("SELECT * FROM produits WHERE stock_total <= seuil_alerte ORDER BY stock_total ASC")->fetchAll();

        respond([
            "success"=>true,
            "data"=>[
                "ventes"=>$ventes->fetch(),
                "achats"=>$achats->fetch(),
                "stocks_alert"=>$stocks_alert
            ]
        ]);
        break;

    case 'ventes':
        $date_debut = $_GET['date_debut'] ?? date('Y-m-d');
        $date_fin = $_GET['date_fin'] ?? date('Y-m-d');
        $stmt = $conn->prepare("SELECT v.*, c.nom as client FROM ventes v LEFT JOIN clients c ON v.client_id=c.idClient WHERE DATE(v.date_vente) BETWEEN ? AND ? ORDER BY v.date_vente DESC");
        $stmt->execute([$date_debut, $date_fin]);
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    case 'stocks':
        $stmt = $conn->query("SELECT p.id, p.nom, p.stock_total, p.seuil_alerte FROM produits p ORDER BY p.nom");
        respond(["success"=>true,"data"=>$stmt->fetchAll()]);
        break;

    default:
        respond(["success"=>false,"message"=>"Action inconnue"],400);
}
