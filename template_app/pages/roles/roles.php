<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';

// Charger les rôles
$roles = $conn->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Charger toutes les permissions par rôle
$permissions = [];
foreach ($roles as $r) {
    $stmt = $conn->prepare("
        SELECT p.*
        FROM role_permissions rp
        INNER JOIN permissions p ON rp.permission_id = p.id
        WHERE rp.role_id = ?
    ");
    $stmt->execute([$r['id']]);
    $permissions[$r['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>


<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fa fa-key"></i> Rôles & Permissions</span>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterRole">
      <i class="fa fa-plus"></i> Ajouter un rôle
    </button>
  </div>
  <div class="card-body">
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
         
          <th>Nom du rôle</th>
          <th>Permissions</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($roles as $r): ?>
          <tr>
            
            <td><?= htmlspecialchars($r['nom'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <?php foreach ($permissions[$r['id']] as $p): ?>
                <span class="badge bg-success" title="<?= htmlspecialchars($p['code'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                <?php endforeach; ?>
            </td>
            <td>
              <button 
                class="btn btn-warning btn-sm btnEditRole" 
                data-id="<?= $r['id'] ?>" 
                data-nom="<?= htmlspecialchars($r['nom'], ENT_QUOTES, 'UTF-8') ?>" 
                data-description="<?= htmlspecialchars($r['description'], ENT_QUOTES, 'UTF-8') ?>" 
                data-permissions='<?= json_encode(array_column($permissions[$r["id"]], "code")) ?>'
                data-bs-toggle="modal" 
                data-bs-target="#modalModifierRole">
                <i class="fa fa-edit"></i>
                </button>


              <button class="btn btn-danger btn-sm btnDeleteRole" data-id="<?= $r['id'] ?>" data-nom="<?= htmlspecialchars($r['nom'], ENT_QUOTES, 'UTF-8') ?>" data-bs-toggle="modal" data-bs-target="#modalDeleteRole">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__."/modal_ajouter_role.php"; ?>
<?php include __DIR__."/modal_modifier_role.php"; ?>
<?php include __DIR__."/modal_supprimer_role.php"; ?>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>
