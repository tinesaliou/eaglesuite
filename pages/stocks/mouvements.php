<?php
require_once __DIR__ . "/../../config/db.php";

$title = "Mouvements de stock";

// --- Filtres ---
$date_debut = $_GET['date_debut'] ?? '';
$date_fin   = $_GET['date_fin'] ?? '';
$type       = $_GET['type'] ?? '';
$produit_id = $_GET['produit_id'] ?? '';

$sql = "
    SELECT m.*, 
           p.nom AS produit, 
           ds.nom AS depot_source,
           dd.nom AS depot_dest,
           u.nom AS utilisateur
    FROM mouvements_stock m
    JOIN produits p ON p.id = m.produit_id
    LEFT JOIN depots ds ON ds.id = m.depot_source_id
    LEFT JOIN depots dd ON dd.id = m.depot_dest_id
    LEFT JOIN utilisateurs u ON u.id = m.utilisateur_id
    WHERE 1=1
";

$params = [];

if ($date_debut) {
    $sql .= " AND DATE(m.date_mouvement) >= ? ";
    $params[] = $date_debut;
}
if ($date_fin) {
    $sql .= " AND DATE(m.date_mouvement) <= ? ";
    $params[] = $date_fin;
}
if ($type) {
    $sql .= " AND m.type = ? ";
    $params[] = $type;
}
if ($produit_id) {
    $sql .= " AND m.produit_id = ? ";
    $params[] = $produit_id;
}

$sql .= " ORDER BY m.date_mouvement DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$mouvements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Produits pour filtres
$produits = $conn->query("SELECT id, nom FROM produits ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">

    <h1 class="mt-4">Mouvements de stock</h1>

    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Mouvements</li>
    </ol>

    <!-- FILTRES -->
    <div class="card mb-3 shadow-sm">
        <div class="card-header"><i class="fa fa-filter"></i> Filtrer</div>
        <div class="card-body">
            <form method="get" class="row g-3">

                <input type="hidden" name="page" value="mouvements">

                <div class="col-md-3">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_debut" value="<?= $date_debut ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_fin" value="<?= $date_fin ?>" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Produit</label>
                    <select name="produit_id" class="form-select">
                        <option value="">Tous</option>
                        <?php foreach ($produits as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($p['id']==$produit_id?'selected':'') ?>>
                                <?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Type mouvement</label>
                    <select name="type" class="form-select">
                        <option value="">Tous</option>
                        <?php
                        $types = ['achat','vente','retour','annulation_achat','annulation_vente'];
                        foreach($types as $t):
                        ?>
                        <option value="<?= $t ?>" <?= ($t==$type?'selected':'') ?>>
                            <?= ucfirst($t) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-12 d-flex justify-content-end pt-2">
                    <button class="btn btn-primary me-2">Rechercher</button>
                    <a href="/eaglesuite/index.php?page=mouvements" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLEAU -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa fa-exchange-alt"></i> Historique des mouvements</span>
            <div>
                <button class="btn btn-success btn-sm" onclick="exportTableToExcel('mouvementsTable')"> Export Excel</button>
                <button class="btn btn-danger btn-sm" onclick="window.print()">🖨️ PDF / Imprimer</button>
            </div>
        </div>

        <div class="card-body table-responsive">
            <table id="mouvementsTable" class="table table-bordered table-striped datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th>Dépôt source</th>
                        <th>Dépôt destination</th>
                        <th>Quantité</th>
                        <th>Utilisateur</th>
                        <th>Référence</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mouvements as $m): ?>
                    <tr>
                        <td><?= date("d/m/Y H:i", strtotime($m['date_mouvement'])) ?></td>
                        <td><?= htmlspecialchars($m['produit'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge bg-info"><?= $m['type'] ?></span></td>
                        <td><?= $m['depot_source'] ?: '-' ?></td>
                        <td><?= $m['depot_dest'] ?: '-' ?></td>
                        <td><?= $m['quantite'] ?></td>
                        <td><?= htmlspecialchars($m['utilisateur'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= $m['reference_table'] 
                                ? $m['reference_table']." #".$m['reference_id']
                                : '-'
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>

<script>
$(document).ready(function() {
    $('#mouvementsTable').DataTable({
        responsive: true,
        language: { url: "/eaglesuite/public/js/fr-FR.json" }
    });
});

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
