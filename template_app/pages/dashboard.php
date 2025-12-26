<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';
// Vérification connexion
if (!isset($conn)) {
    die("Erreur : connexion à la base de données impossible.");
}

// Ventes aujourd’hui
$stmt = $conn->query("SELECT COALESCE(SUM(totalTTC),0) AS total FROM ventes WHERE DATE(date_vente)=CURDATE()");
$ventesJour = $stmt->fetchColumn();

// Ventes du mois
$stmt = $conn->query("SELECT COALESCE(SUM(totalTTC),0) AS total FROM ventes WHERE YEAR(date_vente)=YEAR(CURDATE()) AND MONTH(date_vente)=MONTH(CURDATE())");
$ventesMois = $stmt->fetchColumn();

// Achats du mois
$stmt = $conn->query("SELECT COALESCE(SUM(totalTTC),0) FROM achats WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())");
$achatsMois = $stmt->fetchColumn();

// Clients
$stmt = $conn->query("SELECT COUNT(*) FROM clients");
$clients = $stmt->fetchColumn();

// Produits
$stmt = $conn->query("SELECT COUNT(*) FROM produits");
$produits = $stmt->fetchColumn();

// Solde caisses
$stmt = $conn->query("SELECT COALESCE(SUM(solde_actuel),0) FROM caisses");
$caissesSolde = $stmt->fetchColumn();

// Ventes par mois
$stmt = $conn->query("
    SELECT DATE_FORMAT(date_vente, '%Y-%m') AS mois, 
                       COALESCE(SUM(totalTTC),0) AS total
                FROM ventes
                WHERE date_vente >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                GROUP BY mois
                ORDER BY mois ASC
");
$salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$salesLabels = array_column($salesData, 'mois');
$salesValues = array_column($salesData, 'total');

// Top produits
$stmt = $conn->query("
    SELECT p.id, p.nom, COALESCE(SUM(vd.quantite),0) AS qte
                FROM ventes_details vd
                INNER JOIN produits p ON p.id = vd.produit_id
                GROUP BY p.id, p.nom
                ORDER BY qte DESC
                LIMIT 10
");
$topProduits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Produits sous seuil
$stmt = $conn->query("SELECT p.id, p.reference, p.nom, sd.quantite AS stock_total, p.seuil_alerte FROM produits p INNER JOIN stock_depot sd ON sd.produit_id = p.id WHERE sd.quantite <= p.seuil_alerte");
$lowStocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Caisses + dernières opérations
$stmt = $conn->query("SELECT id, nom, solde_actuel FROM caisses");
$caisses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("
    SELECT oc.date_operation, oc.type_operation, oc.montant, oc.description, c.nom AS caisse
    FROM operations_caisse oc
    LEFT JOIN caisses c ON c.id = oc.caisse_id
    ORDER BY oc.date_operation DESC LIMIT 10
");
$recentOps = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Tableau de bord — EagleSuite";
?>


  <div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Tableau de bord</h3>
      <div class="small-muted">Vue synthétique — Dernière mise à jour: <?= date("d/m/Y H:i") ?></div>
    </div>

    <!-- KPI row -->
    <div class="row g-3 mb-3" id="kpiRow">
      <div class="col-12 col-md-3">
        <div class="card kpi-card"><div class="card-body"><h6>Ventes aujourd'hui</h6><h4><?= number_format($ventesJour,0,',',' ') ?> FCFA</h4></div></div>
      </div>
      <div class="col-12 col-md-3">
        <div class="card kpi-card"><div class="card-body"><h6>Ventes (mois)</h6><h4><?= number_format($ventesMois,0,',',' ') ?> FCFA</h4></div></div>
      </div>
      <div class="col-12 col-md-3">
        <div class="card kpi-card"><div class="card-body"><h6>Produits</h6><h4><?= $produits ?></h4></div></div>
      </div>
      <div class="col-12 col-md-3">
        <div class="card kpi-card"><div class="card-body"><h6>Clients</h6><h4><?= $clients ?></h4></div></div>
      </div>
    </div>

    <!-- Charts row -->
<div class="row g-3 mb-3">
  <div class="col-12 col-lg-8">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Évolution des ventes (12 derniers mois)</strong>
        <small class="text-muted">Montants TTC</small>
      </div>
      <div class="card-body">
        <canvas id="salesChart" class="card-chart" style="min-height: 300px;"></canvas>
        <div id="salesEmpty" class="text-center text-muted" style="display:none;">Aucune donnée disponible</div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-header"><strong>Top produits (quantité)</strong></div>
      <div class="card-body">
        <canvas id="topProductsChart" style="min-height: 300px;"></canvas>
        <div id="topEmpty" class="text-center text-muted" style="display:none;">Aucune donnée disponible</div>
      </div>
    </div>
  </div>
</div>


    <!-- Tables row -->
    <div class="row g-3">
      <div class="col-12 col-lg-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between">
            <strong>Produits en dessous du seuil</strong>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm" id="lowStockTable">
                <thead><tr><th>Réf</th><th>Produit</th><th>Stock</th><th>Seuil</th></tr></thead>
                <tbody>
                  <?php foreach($lowStocks as $p): ?>
                    <tr class="<?= $p['stock_total'] <= $p['seuil_alerte'] ? 'low-stock' : '' ?>">
                      <td><?= htmlspecialchars($p['reference'], ENT_QUOTES, 'UTF-8' ?? '—') ?></td>
                      <td><?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= $p['stock_total'] ?></td>
                      <td><?= $p['seuil_alerte'] ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between">
            <strong>Caisses / Opérations récentes</strong>
          </div>
          <div class="card-body">
            <?php foreach($caisses as $c): ?>
              <div><strong><?= htmlspecialchars($c['nom']) ?></strong> — Solde: <?= number_format($c['solde_actuel'],0,',',' ') ?> FCFA</div>
            <?php endforeach; ?>
            <hr>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Date</th><th>Caisse</th><th>Type</th><th>Montant</th><th>Note</th></tr></thead>
                <tbody>
                  <?php foreach($recentOps as $o): ?>
                    <tr>
                      <td><?= $o['date_operation'] ?></td>
                      <td><?= htmlspecialchars($o['caisse'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= htmlspecialchars($o['type_operation'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td><?= number_format($o['montant'],0,',',' ') ?></td>
                      <td><?= htmlspecialchars($o['description'], ENT_QUOTES, 'UTF-8' ?? '') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <?php include __DIR__ . "/../includes/layout_end.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const moisFR = ["janv.","févr.","mars","avr.","mai","juin","juil.","août","sept.","oct.","nov.","déc."];

const salesLabelsRaw = <?= json_encode($salesLabels) ?>;
const salesValues = <?= json_encode($salesValues) ?>;
const topLabels = <?= json_encode(array_column($topProduits,'nom')) ?>;
const topValues = <?= json_encode(array_column($topProduits,'qte')) ?>;

// 🔹 Formatte les mois (YYYY-MM → "nov. 2025")
const salesLabels = salesLabelsRaw.map(m => {
  if (!m) return '';
  const [y, mo] = m.split('-');
  return moisFR[parseInt(mo,10)-1] + ' ' + y;
});

// === VENTES MENSUELLES ===
if (salesValues.length === 0) {
  document.getElementById('salesEmpty').style.display = 'block';
} else {
  const ctx = document.getElementById('salesChart').getContext('2d');
  const gradient = ctx.createLinearGradient(0, 0, 0, 250);
  gradient.addColorStop(0, 'rgba(54,162,235,0.5)');
  gradient.addColorStop(1, 'rgba(54,162,235,0.05)');

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: salesLabels,
      datasets: [{
        label: 'Ventes TTC',
        data: salesValues,
        fill: true,
        borderColor: 'rgba(54,162,235,1)',
        backgroundColor: gradient,
        tension: 0.3,
        pointRadius: 4,
        pointBackgroundColor: 'rgba(54,162,235,1)',
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' F' } },
      },
      plugins: { legend: { display: false } }
    }
  });
}

// === TOP PRODUITS ===
if (topValues.length === 0) {
  document.getElementById('topEmpty').style.display = 'block';
} else {
  const ctx2 = document.getElementById('topProductsChart').getContext('2d');
  const gradient2 = ctx2.createLinearGradient(0, 0, 250, 0);
  gradient2.addColorStop(0, 'rgba(255,159,64,0.8)');
  gradient2.addColorStop(1, 'rgba(255,205,86,0.5)');

  new Chart(ctx2, {
    type: 'bar',
    data: {
      labels: topLabels,
      datasets: [{
        data: topValues,
        backgroundColor: gradient2,
        borderRadius: 6
      }]
    },
    options: {
      indexAxis: 'y',
      plugins: { legend: { display: false } },
      scales: {
        x: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() } },
        y: { ticks: { autoSkip: false } }
      }
    }
  });
}
</script>

