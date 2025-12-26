<?php
require_once __DIR__ . "/../../../config/db.php";

$caisse_id = (int)$_GET['caisse_id'];

$types = $conn->prepare("
    SELECT 
        ct.id,
        tc.code,
        tc.libelle,
        ct.solde_initial,
        ct.solde_actuel
    FROM caisse_types ct
    JOIN types_caisse tc ON tc.id = ct.type_caisse_id
    WHERE ct.caisse_id = ?
");
$types->execute([$caisse_id]);
$types = $types->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="card">
  <div class="card-header d-flex justify-content-between">
    <span><i class="fa fa-layer-group"></i> Types de caisse</span>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addType">
      Ajouter
    </button>
  </div>

  <div class="card-body">
    <table class="table table-bordered">
        <thead class="table-light">
        <tr>
        <th>Type</th>
        <th>Solde initial</th>
        <th>Solde actuel</th>
        <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($types as $t): ?>
        <tr>
        <td><?= htmlspecialchars($t['libelle'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= number_format($t['solde_initial'],2,'.',' ') ?></td>
        <td><?= number_format($t['solde_actuel'],2,'.',' ') ?></td>
        <td>
            <button class="btn btn-sm btn-warning btnEditType"
                    data-id="<?= $t['id'] ?>"
                    data-solde="<?= $t['solde_initial'] ?>">
            ✏️
            </button>
        </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        </table>

  </div>
</div>

<?php
include "modal_add_type.php";
include "modal_edit_type.php";
?>
