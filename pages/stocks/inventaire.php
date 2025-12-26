<?php
// pages/inventaire/inventaire.php
require_once __DIR__ . "/../../config/db.php";

//require_once __DIR__ . "/../../includes/check_auth.php";

//requirePermission("inventaire.view");

$title = "Inventaire par dépôt";

// filtres
$depot_id = $_GET['depot_id'] ?? '';

// récupérer dépôts pour filtre
$depots = $conn->query("SELECT id, nom FROM depots ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Charger les stocks (par dépôt)
$sql = "
  SELECT sd.id AS stock_id, sd.produit_id, sd.depot_id, sd.quantite AS qty_erp,
         p.nom AS produit_nom, p.reference AS produit_ref,
         d.nom AS depot_nom
  FROM stock_depot sd
  JOIN produits p ON p.id = sd.produit_id
  JOIN depots d ON d.id = sd.depot_id
  WHERE 1=1
";
$params = [];
if (!empty($depot_id)) {
    $sql .= " AND sd.depot_id = ? ";
    $params[] = $depot_id;
}
$sql .= " ORDER BY d.nom, p.nom";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="container-fluid">
    <h1 class="mt-4">Inventaire - par dépôt</h1>
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Inventaire</li>
    </ol>

    <!-- filtre -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="page" value="inventaire">
                <div class="col-md-4">
                    <label class="form-label">Dépôt</label>
                    <select name="depot_id" class="form-select">
                        <option value="">Tous les dépôts</option>
                        <?php foreach ($depots as $dep): ?>
                            <option value="<?= $dep['id'] ?>" <?= ($dep['id']==$depot_id)?'selected':'' ?>>
                                <?= htmlspecialchars($dep['nom'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8 text-end">
                    <button class="btn btn-primary">Filtrer</button>
                    <a href="/eaglesuite/index.php?page=inventaire" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- tableau -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-clipboard-list"></i> Situation des stocks </span>
            <div>
                <button id="exportExcel" class="btn btn-success btn-sm">Export Excel</button>
                <button id="exportPdf" class="btn btn-danger btn-sm">Export PDF</button>
            </div>
        </div>

        <div class="card-body table-responsive">
            <table id="inventaireTable" class="table table-bordered table-striped datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Dépôt</th>
                        <th>Réf</th>
                        <th>Produit</th>
                        <th>Stock ERP</th>
                        <th>Stock physique</th>
                        <th>Écart</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stocks as $s): ?>
                    <tr data-stock-id="<?= $s['stock_id'] ?>" data-produit-id="<?= $s['produit_id'] ?>" data-depot-id="<?= $s['depot_id'] ?>">
                        <td><?= htmlspecialchars($s['depot_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($s['produit_ref'], ENT_QUOTES, 'UTF-8' ?? '') ?></td>
                        <td><?= htmlspecialchars($s['produit_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-end qty-erp"><?= (int)$s['qty_erp'] ?></td>
                        <td class="text-end qty-physique">-</td>
                        <td class="text-end ecart">-</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary btn-ajuster" 
                                    data-stock-id="<?= $s['stock_id'] ?>"
                                    data-produit="<?= htmlspecialchars($s['produit_nom'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-depot="<?= htmlspecialchars($s['depot_nom'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-qty="<?= (int)$s['qty_erp'] ?>">
                                <i class="fa fa-edit"></i> Ajuster
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal d'ajustement -->
<div class="modal fade" id="modalAjuster" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="formAjuster" method="post" action="/eaglesuite/pages/inventaire/update_stock.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajuster le stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="stock_id" id="m_stock_id">
          <input type="hidden" name="produit_id" id="m_produit_id">
          <input type="hidden" name="depot_id" id="m_depot_id">
          <div class="mb-2">
              <label class="form-label">Produit</label>
              <div id="m_produit_nom" class="fw-bold"></div>
          </div>
          <div class="mb-2">
              <label class="form-label">Dépôt</label>
              <div id="m_depot_nom" class="fw-bold"></div>
          </div>

          <div class="mb-2">
              <label class="form-label">Stock actuel</label>
              <input type="number" id="m_qty_erp" class="form-control" readonly>
          </div>

          <div class="mb-2">
              <label class="form-label">Stock physique (saisir)</label>
              <input type="number" name="qty_physique" id="m_qty_physique" class="form-control" required>
          </div>

          <div class="mb-2">
              <label class="form-label">Motif / note (facultatif)</label>
              <textarea name="note" class="form-control" rows="2"></textarea>
          </div>

          <div class="mb-2">
              <label class="form-label">Type d'ajustement</label>
              <select name="type" class="form-select" required>
                  <option value="ajustement">Ajustement inventaire</option>
                  <option value="correction">Correction (enregistrement)</option>
              </select>
          </div>

          <div class="alert alert-info small">
            L'opération mettra à jour le stock et enregistrera un mouvement dans <code>mouvements_stock</code>.
          </div>

      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer l'ajustement</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function(){
    // DataTable
    var table = $('#inventaireTable').DataTable({
        responsive: true,
        language: { url: "/eaglesuite/public/js/fr-FR.json" },
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [4,6] }
        ]
    });

    // Export Excel (simple table -> dataURI)
    $('#exportExcel').on('click', function(){
        // fallback simple export
        let tableHtml = document.getElementById('inventaireTable').outerHTML;
        let filename = 'inventaire_depots.xls';
        let a = document.createElement('a');
        a.href = 'data:application/vnd.ms-excel,' + encodeURIComponent(tableHtml);
        a.download = filename;
        document.body.appendChild(a); a.click(); a.remove();
    });

    // Export PDF -> on peut ouvrir la page d'impression
    $('#exportPdf').on('click', function(){
        window.print();
    });

    // Ouvrir modal ajustement
    $('#inventaireTable').on('click', '.btn-ajuster', function(){
        var btn = $(this);
        var stockId = btn.data('stock-id');
        var produitNom = btn.data('produit');
        var depotNom = btn.data('depot');
        var qty = btn.data('qty');
        var tr = btn.closest('tr');
        var produitId = tr.data('produit-id');
        var depotId = tr.data('depot-id');

        $('#m_stock_id').val(stockId);
        $('#m_produit_id').val(produitId);
        $('#m_depot_id').val(depotId);
        $('#m_produit_nom').text(produitNom);
        $('#m_depot_nom').text(depotNom);
        $('#m_qty_erp').val(qty);
        $('#m_qty_physique').val(qty);

        // show modal
        var modal = new bootstrap.Modal(document.getElementById('modalAjuster'));
        modal.show();
    });

    // Mise à jour visuelle après validation (on laisse l'envoi classique POST vers update_stock.php)
    $('#formAjuster').on('submit', function(e){
       
    });

});
</script>
