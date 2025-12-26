<?php
require_once __DIR__ . "/../../config/db.php";
$title = "CRM — Tableau de bord";

/* ---- KPIs ---- */

// Total clients
$total_clients = $conn->query("SELECT COUNT(*) FROM clients")->fetchColumn();

// Opportunités actives
$total_opps = $conn->query("SELECT COUNT(*) FROM crm_opportunites WHERE etat NOT IN ('gagnee','perdue')")->fetchColumn();

// Valeur pipeline pondérée
$pipeline_value = $conn->query("
    SELECT SUM(montant * (probabilite/100)) 
    FROM crm_opportunites
")->fetchColumn() ?: 0;

// Tâches en retard
$tasks_late = $conn->query("
    SELECT COUNT(*) 
    FROM crm_taches 
    WHERE statut='en_cours' 
    AND date_echeance < CURDATE()
")->fetchColumn();

/* ---- Graphiques ---- */
$opps_status = $conn->query("SELECT etat, COUNT(*) nb FROM crm_opportunites GROUP BY etat")->fetchAll(PDO::FETCH_ASSOC);
$opps_prob   = $conn->query("SELECT probabilite, COUNT(*) nb FROM crm_opportunites GROUP BY probabilite")->fetchAll(PDO::FETCH_ASSOC);
$clients_type = $conn->query("SELECT type, COUNT(*) nb FROM clients GROUP BY type")->fetchAll(PDO::FETCH_ASSOC);
$ca_mensuel = $conn->query("
    SELECT DATE_FORMAT(date_vente,'%Y-%m') mois, d.symbole AS symbole,SUM(totalTTC) total
    FROM ventes
    JOIN devises d ON ventes.devise_id = d.id
    GROUP BY DATE_FORMAT(date_vente,'%Y-%m')
    ORDER BY mois ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* ---- Interactions ---- */
$interactions = $conn->query("
    SELECT i.*, c.nom as client
    FROM crm_interactions i
    JOIN clients c ON c.idClient=i.client_id
    ORDER BY i.date_interaction DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

/* ---- Top clients ---- */
$top_clients = $conn->query("
    SELECT c.nom, COUNT(v.id) as nb_ventes, d.symbole AS symbole ,SUM(v.totalTTC) as total
    FROM ventes v
    JOIN devises d ON v.devise_id = d.id
    JOIN clients c ON c.idClient=v.client_id
    GROUP BY c.idClient
    ORDER BY total DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.crm-kpi {
    border-radius: 16px;
    padding: 22px;
    background: #fff;
    box-shadow: 0 2px 14px rgba(0,0,0,0.06);
    transition: 0.2s;
}
.crm-kpi:hover {
    transform: translateY(-4px);
}
.crm-kpi h6 {
    font-size: .85rem;
    color: #6c757d;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.crm-kpi h2 {
    font-weight: 700;
    font-size: 2rem;
    margin: 0;
}

.card-modern {
    border-radius: 16px !important;
    border: none !important;
    box-shadow: 0 2px 14px rgba(0,0,0,0.06);
}

canvas {
    max-height: 270px !important;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid #f1f3f4;
    padding: 14px 8px;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>

<div class="container-fluid px-3">
     <h1 class="mt-4">CRM - Tableau de bord</h1>
    <p class="text-muted mb-4">Vue d’ensemble de votre relation client.</p>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">CRM</li>
    </ol>

    <!-- KPI Modernes -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="crm-kpi">
                <h6>Clients</h6>
                <h2><?= $total_clients ?></h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="crm-kpi">
                <h6>Opportunités actives</h6>
                <h2><?= $total_opps ?></h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="crm-kpi">
                <h6>Pipeline pondéré</h6>
                <h2><?= number_format($pipeline_value,0,',',' ') ?> FCFA</h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="crm-kpi">
                <h6>Tâches en retard</h6>
                <h2><?= $tasks_late ?></h2>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card card-modern">
                <div class="card-header fw-bold">Opportunités par statut</div>
                <div class="card-body"><canvas id="chartOppStatus"></canvas></div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card card-modern">
                <div class="card-header fw-bold">Probabilité des opportunités</div>
                <div class="card-body"><canvas id="chartOppProb"></canvas></div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card card-modern">
                <div class="card-header fw-bold">Répartition des clients</div>
                <div class="card-body"><canvas id="chartClientType"></canvas></div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card card-modern">
                <div class="card-header fw-bold">Chiffre d’affaires mensuel</div>
                <div class="card-body"><canvas id="chartCA"></canvas></div>
            </div>
        </div>
    </div>

    <!-- TOP Clients -->
    <div class="card card-modern mt-4 mb-4">
        <div class="card-header fw-bold">Top 10 Clients</div>
        <div class="card-body">
            <table id="tableTopClients" class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr><th>Client</th><th>Ventes</th><th>Total</th></tr>
                </thead>
                <tbody>
                <?php foreach ($top_clients as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $c['nb_ventes'] ?></td>
                        <td><?= number_format($c['total'],0,',',' ') ?> <?=$c['symbole'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Interactions -->
    <div class="card card-modern mb-5">
        <div class="card-header fw-bold">10 Dernières interactions</div>
        <div class="card-body">
            <ul class="list-group">
                <?php foreach ($interactions as $i): ?>
                <li class="list-group-item">
                    <b><?= htmlspecialchars($i['client'], ENT_QUOTES, 'UTF-8') ?></b> — 
                    <?= htmlspecialchars($i['sujet'], ENT_QUOTES, 'UTF-8') ?>
                    <br>
                    <small class="text-muted"><?= $i['date_interaction'] ?></small>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<!-- ChartJS -->
<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
 <script src="/eaglesuite/public/vendor/chartjs/chart.umd.js"></script>
<script>
new Chart(document.getElementById('chartOppStatus'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($opps_status,'etat')) ?>,
        datasets: [{ data: <?= json_encode(array_column($opps_status,'nb')) ?> }]
    }
});

new Chart(document.getElementById('chartOppProb'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($opps_prob,'probabilite')) ?>,
        datasets: [{ data: <?= json_encode(array_column($opps_prob,'nb')) ?> }]
    }
});

new Chart(document.getElementById('chartClientType'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($clients_type,'type')) ?>,
        datasets: [{ data: <?= json_encode(array_column($clients_type,'nb')) ?> }]
    }
});

new Chart(document.getElementById('chartCA'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($ca_mensuel,'mois')) ?>,
        datasets: [{
            label: 'CA',
            data: <?= json_encode(array_column($ca_mensuel,'total')) ?>,
            borderWidth: 3,
            tension: .3
        }]
    }
});

//$(document).ready(() => $('#tableTopClients').DataTable());
</script>
<script>
$(document).ready(function(){
    $('#tableCRMClients').DataTable({
        pageLength: 25,
        language: {
            url: "/eaglesuite/public/js/fr-FR.json"
        }
    });
});
</script>
