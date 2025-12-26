<?php
// pages/trésorerie/trésorerie.php
require_once __DIR__ . "/../../config/db.php";
$title = "Trésorerie";

// Récupérer toutes les caisses
$caisses = $conn->query("
    SELECT 
        ct.id,
        tc.code,
        tc.libelle AS nom,
        ct.solde_actuel
    FROM caisse_types ct
    INNER JOIN types_caisse tc ON ct.type_caisse_id = tc.id
    ORDER BY ct.id
")->fetchAll(PDO::FETCH_ASSOC);

// --- FILTRE ---
$date_debut  = $_GET['date_debut'] ?? '';
$date_fin    = $_GET['date_fin'] ?? '';
$caisse_id   = $_GET['caisse_id'] ?? '';

// base de la requête
$sql = "
    SELECT oc.id, oc.date_operation, oc.type_operation, oc.montant, 
    (oc.montant / d.taux_par_defaut) AS montant_devise, oc.description,d.symbole AS symbole,
           oc.reference_table, oc.reference_id,
           c.nom AS caisse_nom,
           CASE 
            WHEN oc.reference_table = 'ventes' THEN (SELECT v.numero FROM ventes v WHERE v.id = oc.reference_id)
            WHEN oc.reference_table = 'achats' THEN (SELECT a.numero FROM achats a WHERE a.id = oc.reference_id)
            ELSE NULL
          END AS numero_piece,

           CASE 
            WHEN oc.reference_table = 'ventes' THEN CONCAT(
                'Vente : ',
                (SELECT GROUP_CONCAT(CONCAT(vd.quantite, 'x ', p.nom) SEPARATOR ', ')
                  FROM ventes_details vd 
                  JOIN produits p ON vd.produit_id = p.id
                  WHERE vd.vente_id = oc.reference_id)
            )
            WHEN oc.reference_table = 'achats' THEN CONCAT(
                'Achat : ',
                (SELECT GROUP_CONCAT(CONCAT(ad.quantite, 'x ', p.nom) SEPARATOR ', ')
                  FROM achats_details ad
                  JOIN produits p ON ad.produit_id = p.id
                  WHERE ad.achat_id = oc.reference_id)
            )
            WHEN oc.reference_table = 'creances_clients' THEN CONCAT(
              'Créance_client : ', 
              (SELECT GROUP_CONCAT(v.numero SEPARATOR ', ')
                  FROM ventes v 
                  JOIN creances_clients cc ON cc.vente_id = v.id
                  WHERE cc.id = oc.reference_id)
            )
            WHEN oc.reference_table = 'dettes_fournisseurs' THEN CONCAT(
              'Dette_fournisseur : ', 
              (SELECT GROUP_CONCAT(a.numero SEPARATOR ', ')
                  FROM achats a 
                  JOIN dettes_fournisseurs df ON df.achat_id = a.id
                  WHERE df.id = oc.reference_id)
            )
        
            ELSE oc.description
          END AS designation

    FROM operations_caisse oc
    JOIN devises d ON oc.devise_id= d.id
    JOIN caisse_types ct ON oc.caisse_type_id = ct.id
    JOIN caisses c ON ct.caisse_id= c.id
    WHERE 1=1
";

$params = [];
if (!empty($date_debut)) {
    $sql .= " AND DATE(oc.date_operation) >= ? ";
    $params[] = date('Y-m-d', strtotime($date_debut));
}
if (!empty($date_fin)) {
    $sql .= " AND DATE(oc.date_operation) <= ? ";
    $params[] = date('Y-m-d', strtotime($date_fin));
}
if (!empty($caisse_id)) {
    $sql .= " AND oc.caisse_type_id = ? ";
    $params[] = $caisse_id;
}

$sql .= " ORDER BY oc.date_operation ASC, oc.id ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$ops = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h1 class="mt-4">Trésorerie</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/{{TENANT_DIR}}/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Trésorerie</li>
    </ol>

    <!-- Résumé caisses -->
    <div class="row mb-3">
        <?php foreach ($caisses as $c): ?>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></h5>
                <p class="card-text display-6"><?= number_format($c['solde_actuel'],2,',',' ') ?> FCFA</p>
                <div class="mt-auto">
                  <!-- <a href="/{{TENANT_DIR}}/index.php?page=caisse_<:?= strtolower($c['type']) ?>" class="btn btn-outline-primary btn-sm">Voir la caisse</a> -->
                   <?php
                    $pageMap = [
                          'ESPECES'   => 'caisse_especes',
                          'BANQUE'   => 'caisse_banque',
                          'MOBILE' => 'caisse_mobile'
                                ];
                    $page = $pageMap[$c['code']] ?? 'tresorerie';
                    
                    ?><a href="/{{TENANT_DIR}}/index.php?page=<?= $page ?>" 
                      class="btn btn-outline-primary btn-sm"> Voir la caisse</a>
                  
                  <button class="btn btn-secondary btn-sm btnTransfer" 
                          data-from="<?= $c['id'] ?>" 
                          data-bs-toggle="modal" 
                          data-bs-target="#modalTransfer">Transférer
                  </button>
                </div>
              </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filtres -->
    <div class="card mb-3 shadow-sm">
      <div class="card-header"><i class="fa fa-filter"></i> Filtrer les opérations</div>
      <div class="card-body">
        <form method="get" class="row g-3">
          <input type="hidden" name="page" value="tresorerie">
          <div class="col-md-3">
            <label class="form-label">Date début</label>
            <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut, ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Date fin</label>
            <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin, ENT_QUOTES, 'UTF-8') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Caisse</label>
            <select name="caisse_id" class="form-select">
              <option value="">Toutes</option>
              <?php foreach ($caisses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($caisse_id==$c['id'])?'selected':'' ?>>
                  <?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Filtrer</button>
            <a href="/{{TENANT_DIR}}/index.php?page=tresorerie" class="btn btn-outline-secondary">Réinitialiser</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Tableau -->
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fa fa-book"></i> Cahier Recettes & Dépenses</span>
        <div>
          <button class="btn btn-success btn-sm" onclick="exportTableToExcel('tresorerieTable')">Export Excel</button>
          <button class="btn btn-danger btn-sm" onclick="window.print()">🖨️ PDF / Imprimer</button>
        </div>
      </div>
      <div class="card-body table-responsive">
        <table id="tresorerieTable" class="table table-bordered table-striped">
          <thead class="table-dark">
            <tr>
              <th>Date</th>
              <th>N° Pièce</th>
              <th>Désignation</th>
              <th>Montant Devise </th>
              <th>Recettes (CFA)</th>
              <th>Dépenses (CFA)</th>
              <th>Solde (CFA)</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $soldes = [];
            foreach ($ops as $op): 
              $caisseKey = $op['caisse_nom'];

              if (!isset($soldes[$caisseKey])) {
                  $soldes[$caisseKey] = 0;
              }

              if ($op['type_operation'] === 'entree') {
                  $soldes[$caisseKey] += $op['montant'];
              } else {
                  $soldes[$caisseKey] -= $op['montant'];
              }

            ?>
              <tr>
                <td><?= date('d/m/Y', strtotime($op['date_operation'])) ?></td>
                <td><?= htmlspecialchars($op['numero_piece'], ENT_QUOTES, 'UTF-8' ?? $op['id']) ?></td>
                <td><?= htmlspecialchars($op['designation'], ENT_QUOTES, 'UTF-8' ?? '-') ?></td>
                <td>
                  <?= number_format($op['montant_devise'],2,',',' ') . ' ' . htmlspecialchars($op['symbole'], ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td class="text-success">
                  <?= ($op['type_operation'] === 'entree') ? number_format($op['montant'],2,',',' ') : '' ?>
                </td>
                <td class="text-danger">
                  <?= ($op['type_operation'] === 'sortie') ? number_format($op['montant'],2,',',' ') : '' ?> 
                </td>
                <td><strong><?= number_format($soldes[$caisseKey],2,',',' ') ?></strong></td>
              </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
      </div>
    </div>
</div>

<?php include __DIR__ . "/modal_ajouter_operation.php"; ?>
<?php include __DIR__ . "/modal_transfer.php"; ?>
<?php include __DIR__ . "/../../includes/layout_end.php"; ?>


<script>
$(document).ready(function() {
  $('#tresorerieTable').DataTable({
    ordering: false,
    responsive: true,
    language: { url: "/{{TENANT_DIR}}/public/js/fr-FR.json" }
});
});

</script>

<script>

document.addEventListener('DOMContentLoaded', function() {
  // préremplir transfert
  document.querySelectorAll('.btnTransfer').forEach(btn=>{
    btn.addEventListener('click', ()=> {
      document.getElementById('transfer_from').value = btn.dataset.from;
    });
  });
});

// Export Excel (rapide)
function exportTableToExcel(tableID, filename = 'Cahier_Tresorerie.xls'){
  let dataType = 'application/vnd.ms-excel';
  let tableSelect = document.getElementById(tableID);
  let tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
  let downloadLink = document.createElement("a");
  document.body.appendChild(downloadLink);
  downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
  downloadLink.download = filename;
  downloadLink.click();
  document.body.removeChild(downloadLink);
}
</script>
