<?php
require_once __DIR__ . "/../../config/db.php";

// Vérifier la page demandée
$page = $_GET['page'] ?? '';

$page = $_GET['page'] ?? '';

$typeCodeMap = [
    'caisse_especes' => 'ESPECES',
    'caisse_banque'  => 'BANQUE',
    'caisse_mobile'  => 'MOBILE',
];

if (!isset($typeCodeMap[$page])) {
    die("❌ Type de caisse inconnu.");
}

$type_code = $typeCodeMap[$page];

$stmt = $conn->prepare("
    SELECT 
        ct.id              AS caisse_type_id,
        ct.solde_actuel,
        tc.libelle         AS type_libelle,
        tc.code            AS type_code,
        c.id               AS caisse_id,
        c.nom              AS caisse_nom
    FROM caisse_types ct
    JOIN types_caisse tc ON tc.id = ct.type_caisse_id
    JOIN caisses c       ON c.id = ct.caisse_id
    WHERE tc.code = ? AND c.actif = 1
    LIMIT 1
");
$stmt->execute([$type_code]);
$caisseType = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$caisseType) {
    die("❌ Aucune caisse active pour ce type.");
}


$stmt = $conn->prepare("
    SELECT oc.*, u.nom AS user_name, mp.libelle AS mode_paiement
    FROM operations_caisse oc
    JOIN modes_paiement mp ON mp.id= oc.mode_paiement_id
    JOIN utilisateurs u ON oc.utilisateur_id = u.id
    WHERE oc.caisse_type_id = ?
    ORDER BY oc.date_operation DESC
");
$stmt->execute([$caisseType['caisse_type_id']]);
$operations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container-fluid px-2">
  <h1 class="mt-4">
    <?= htmlspecialchars($caisseType['caisse_nom'], ENT_QUOTES, 'UTF-8') ?>
    <small class="text-muted"> - <?= htmlspecialchars($caisseType['type_libelle'], ENT_QUOTES, 'UTF-8') ?></small>
</h1>

<div class="mb-3">
    Solde :
    <strong><?= number_format($caisseType['solde_actuel'], 2, ',', ' ') ?> FCFA</strong>
</div>

  <div class="mb-3">
    <button class="btn btn-success btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#modalAjouterOperation"
        data-caisse-type="<?= $caisseType['caisse_type_id'] ?>">Ajouter opération
  </button>

    <a class="btn btn-secondary btn-sm" href="/eaglesuite/index.php?page=tresorerie">Retour</a>
  </div>

  <div class="card-body">
    <div class="row mb-3">
  <div class="col-md-3">
    <label for="minDate" class="form-label">Du :</label>
    <input type="date" id="minDate" class="form-control">
  </div>
  <div class="col-md-3">
    <label for="maxDate" class="form-label">Au :</label>
    <input type="date" id="maxDate" class="form-control">
  </div>
  
</div>


  <table id="caisseTable" class="table table-bordered table-striped datatable">
    <thead class="table-dark">
      <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Montant</th>
        <th>Mode</th>
        <th>Référence</th>
        <th>Utilisateur</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($operations as $op): ?>
        <tr>
          <td data-order="<?= htmlspecialchars($op['date_operation'], ENT_QUOTES, 'UTF-8') ?>">
            <?= date('d/m/Y H:i', strtotime($op['date_operation'])) ?></td>
          <td><?= htmlspecialchars(ucfirst($op['type_operation'], ENT_QUOTES, 'UTF-8')) ?></td>
          <td><?= number_format($op['montant'],2,',',' ') ?> FCFA</td>
          <td><?= htmlspecialchars($op['mode_paiement'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($op['reference_table'], ENT_QUOTES, 'UTF-8' . ' #' . $op['reference_id'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($op['user_name'], ENT_QUOTES, 'UTF-8' ?? '-') ?></td>
          <td><a href="/eaglesuite/api/caisses/imprimer.php?id=<?= $op['id'] ?>" 
              target="_blank" class="btn btn-success btn-sm">
              <i class="fa fa-print"></i></a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
 </div>
</div>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function() {
  var table = $('#caisseTable').DataTable({
    responsive: true,
    language: { url: "/eaglesuite/public/js/fr-FR.json" }
  });

  // helper : parse des chaînes date en objet Date (supporte dd/mm/YYYY, YYYY-MM-DD, avec ou sans heure)
  function parseDateStringToDate(dateStr) {
    if (!dateStr) return null;
    dateStr = String(dateStr).trim();

    // supprime espaces multiples et nbsp
    dateStr = dateStr.replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();

    // séparer date et heure si existantes
    var parts = dateStr.split(' ');
    var datePart = parts[0];
    var timePart = parts.length > 1 ? parts.slice(1).join(' ') : '';

    // ----- format dd/mm/YYYY (slash) -----
    if (datePart.indexOf('/') !== -1) {
      var d = datePart.split('/');
      if (d.length >= 3) {
        var day = parseInt(d[0], 10);
        var month = parseInt(d[1], 10) - 1;
        var year = parseInt(d[2], 10);
        var hours = 0, mins = 0;
        if (timePart) {
          var tm = timePart.split(':');
          hours = parseInt(tm[0], 10) || 0;
          mins = parseInt(tm[1], 10) || 0;
        }
        return new Date(year, month, day, hours, mins, 0);
      }
    }

    // ----- format YYYY-MM-DD (dash) -----
    if (datePart.indexOf('-') !== -1) {
      var d2 = datePart.split('-');
      if (d2.length >= 3) {
        // si premier fragment a length 4 -> on suppose YYYY-MM-DD
        if (d2[0].length === 4) {
          var year2 = parseInt(d2[0], 10);
          var month2 = parseInt(d2[1], 10) - 1;
          var day2 = parseInt(d2[2], 10);
          var hours2 = 0, mins2 = 0;
          if (timePart) {
            var tm2 = timePart.split(':');
            hours2 = parseInt(tm2[0], 10) || 0;
            mins2 = parseInt(tm2[1], 10) || 0;
          }
          return new Date(year2, month2, day2, hours2, mins2, 0);
        } else {
          // possiblement dd-mm-YYYY (rare) -> essayer malgré tout
          var day3 = parseInt(d2[0], 10);
          var month3 = parseInt(d2[1], 10) - 1;
          var year3 = parseInt(d2[2], 10);
          return new Date(year3, month3, day3);
        }
      }
    }

    // fallback : Date.parse (essaie)
    var t = Date.parse(dateStr);
    return isNaN(t) ? null : new Date(t);
  }

  // Filtre DataTables
  $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    // Ne s'applique qu'à notre tableau (optionnel)
    if (settings.nTable.id !== 'caisseTable') return true;

    var min = $('#minDate').val(); // YYYY-MM-DD ou ""
    var max = $('#maxDate').val();

    // récupérer la valeur affichée de la colonne date (data[0])
    var raw = data[0] || '';

    // si la cellule a un data-order (valeur ISO fournie côté serveur), privilégier cette valeur
    try {
      var cellNode = table.cell(dataIndex, 0).node();
      if (cellNode) {
        var dataOrder = $(cellNode).data('order'); // undefined si absent
        if (dataOrder) raw = dataOrder;
      }
    } catch (e) {
      // ignore
    }

    var rowDate = parseDateStringToDate(raw);
    if (!rowDate) return true; // si date invalide, on ne filtre pas

    var minDate = min ? new Date(min + 'T00:00:00') : null;
    var maxDate = max ? new Date(max + 'T23:59:59') : null;

    if ((minDate && rowDate < minDate) || (maxDate && rowDate > maxDate)) {
      return false;
    }
    return true;
  });

  // déclencheurs
  $('#searchInput').on('keyup', function() { table.search(this.value).draw(); });
  $('#minDate, #maxDate').on('change', function() { table.draw(); });
});
</script>
