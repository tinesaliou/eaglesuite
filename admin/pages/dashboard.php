<?php
require_once __DIR__ . '/../init.php';
require_admin();

// Statistiques
$totalClients = $masterPdo->query("SELECT COUNT(*) FROM clients_saas")->fetchColumn();
$actifs       = $masterPdo->query("SELECT COUNT(*) FROM clients_saas WHERE statut='actif'")->fetchColumn();
$suspendus    = $masterPdo->query("SELECT COUNT(*) FROM clients_saas WHERE statut='suspendu'")->fetchColumn();
$expirant     = $masterPdo->query("SELECT COUNT(*) FROM clients_saas WHERE expiration <= DATE_ADD(CURDATE(), INTERVAL 5 DAY)")->fetchColumn();

// Top 5 clients récents
$topClients = $masterPdo->query("
    SELECT societe, subdomain, date_creation 
    FROM clients_saas 
    ORDER BY date_creation DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Stats abonnements (pour le graphique)
$stats = $masterPdo->query("
    SELECT DATE_FORMAT(date_creation,'%Y-%m') AS mois, COUNT(*) AS total
    FROM clients_saas
    GROUP BY mois
    ORDER BY mois ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Total paiements
$totalPayments = $masterPdo->query("SELECT COUNT(*) FROM saas_payments")->fetchColumn();

// Somme totale
$totalRevenue  = $masterPdo->query("
    SELECT SUM(montant) FROM saas_payments
")->fetchColumn() ?: 0;

// Revenue 30 jours
$revenue30  = $masterPdo->query("
    SELECT SUM(montant) FROM saas_payments
    WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetchColumn() ?: 0;

// Factures impayées
$unpaid = $masterPdo->query("
    SELECT COUNT(*) FROM saas_factures WHERE statut != 'payé'
")->fetchColumn();

/* ============================
    DERNIERS PAIEMENTS  
   ============================ */
$recent = $masterPdo->query("
    SELECT p.*, c.societe, c.subdomain
    FROM saas_payments p
    JOIN clients_saas c ON c.id = p.client_id
    ORDER BY p.date_creation DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

/* ============================
    STATS REVENUS (GRAPH)
   ============================ */
$chart = $masterPdo->query("
    SELECT DATE_FORMAT(date_creation,'%Y-%m') AS mois, SUM(montant) AS total
    FROM saas_payments
    GROUP BY mois
    ORDER BY mois ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-2 mt-4">
 <div class="container-fluid px-2">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="fw-bold mb-4">Tableau de bord SaaS - EagleSuite</h3>
      <div class="small-muted">Vue synthétique — Dernière mise à jour: <?= date("d/m/Y H:i") ?></div>
    </div>

   
    <!-- ======================= CARDS ======================= -->
    <div class="row g-3">
        
        <div class="col-md-3">
            <div class="card shadow-sm border-0 dashboard-card">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill text-primary fs-1"></i>
                    <h3 class="mt-2 fw-bold"><?= $totalClients ?></h3>
                    <p class="text-muted mb-0">Clients Total</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 dashboard-card">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle-fill text-success fs-1"></i>
                    <h3 class="mt-2 fw-bold"><?= $actifs ?></h3>
                    <p class="text-muted mb-0">Actifs</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 dashboard-card">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-1"></i>
                    <h3 class="mt-2 fw-bold"><?= $expirant ?></h3>
                    <p class="text-muted mb-0">Expire bientôt</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 dashboard-card">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle-fill text-danger fs-1"></i>
                    <h3 class="mt-2 fw-bold"><?= $suspendus ?></h3>
                    <p class="text-muted mb-0">Suspendus</p>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================= SEPARATOR ======================= -->
    <hr class="my-4">

    <div class="row g-3">

        <div class="col-md-3">
            <div class="card shadow-sm border-0 stat-card">
                <div class="card-body text-center">
                    <i class="bi bi-credit-card text-primary fs-1"></i>
                    <h3 class="mt-2 fw-bold"><?= $totalPayments ?></h3>
                    <p class="text-muted mb-0">Paiements reçus</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 stat-card">
                <div class="card-body text-center">
                    <i class="bi bi-cash-coin text-success fs-1"></i>
                    <h3 class="mt-2 fw-bold"><?= number_format($totalRevenue,0,',',' ') ?> F</h3>
                    <p class="text-muted mb-0">Total encaissé</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 stat-card">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow text-warning fs-1"></i>
                    <h3 class="mt-2 fw-bold"><?= number_format($revenue30,0,',',' ') ?> F</h3>
                    <p class="text-muted mb-0">30 derniers jours</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 stat-card">
                <div class="card-body text-center">
                    <i class="bi bi-receipt-cutoff text-danger fs-1"></i>
                    <h3 class="mt-2 fw-bold"><?= $unpaid ?></h3>
                    <p class="text-muted mb-0">Factures impayées</p>
                </div>
            </div>
        </div>
    </div>


    <!-- ======================= SEPARATOR ======================= -->
    <hr class="my-4">

    <!-- ======================= GRAPH + TOP CLIENTS ======================= -->
    <div class="row g-3">

        <!-- Graphique -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Évolution des inscriptions</div>
                <div class="card-body">
                    <canvas id="chartInscriptions" height="140"></canvas>
                </div>
            </div>
        </div>

        <!-- Top clients -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Nouveaux Clients</div>
                <div class="card-body">

                    <?php if (empty($topClients)): ?>
                        <p class="text-muted">Aucun client pour le moment.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($topClients as $cl): ?>
                            <li class="list-group-item">
                                <strong><?= htmlspecialchars($cl['societe']) ?></strong><br>
                                <small class="text-muted"><?= $cl['subdomain'] ?> — <?= $cl['date_creation'] ?></small>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
 </div>

 <hr class="my-4">

    <!-- ====== GRAPH + TOP CLIENTS ====== -->
    <div class="row g-3">

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Revenus Mensuels</div>
                <div class="card-body">
                    <canvas id="chartRevenue" height="140"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Top Payeurs</div>
                <div class="card-body">

                    <?php
                    $top = $masterPdo->query("
                        SELECT c.societe, c.subdomain, SUM(montant) total
                        FROM saas_payments p
                        JOIN clients_saas c ON c.id=p.client_id
                        GROUP BY p.client_id
                        ORDER BY total DESC
                        LIMIT 5
                    ")->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($top as $t):
                    ?>
                        <div class="mb-2">
                            <b><?= htmlspecialchars($t['societe']) ?></b><br>
                            <small class="text-muted">
                                <?= $t['subdomain'] ?> — <?= number_format($t['total'],0,',',' ') ?> F CFA
                            </small>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>

    </div>

    <hr class="my-4">

    <!-- ====== TABLE DES PAIEMENTS ====== -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">Derniers paiements</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Réf</th>
                        <th>Méthode</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recent as $p): ?>
                    <tr>
                        <td>
                            <b><?= htmlspecialchars($p['societe']) ?></b><br>
                            <small class="text-muted"><?= $p['subdomain'] ?></small>
                        </td>
                        <td><?= number_format($p['montant'],0,',',' ') ?> F</td>
                        <td><?= $p['transaction_ref'] ?></td>
                        <td><?= strtoupper($p['methode']) ?></td>
                        <td><?= $p['date_creation'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>


<!-- ======================= STYLE ======================= -->
<style>
.dashboard-card {
    transition: transform .2s ease, box-shadow .2s ease;
    border-radius: 12px;
}
.dashboard-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.15);
}

.stat-card {
    transition: .2s ease;
    border-radius: 12px;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
</style>


<!-- ======================= GRAPH SCRIPT ======================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('chartInscriptions');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($stats, 'mois')) ?>,
        datasets: [{
            label: 'Inscriptions',
            data: <?= json_encode(array_column($stats, 'total')) ?>,
            borderWidth: 3,
            tension: 0.35,
            borderColor: '#ff8c00',
            backgroundColor: 'rgba(255,140,0,.15)',
            fill: true,
        }]
    },
    options: {
        plugins: { legend: { display: false }},
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<script>
new Chart(document.getElementById('chartRevenue'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($chart,'mois')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($chart,'total')) ?>,
            borderColor: '#007bff',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            backgroundColor: 'rgba(0,123,255,0.15)'
        }]
    },
    options: {
        plugins: { legend: { display: false }},
        scales: { y: { beginAtZero: true } }
    }
});
</script>