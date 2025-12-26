<?php
// Charger toutes les permissions disponibles
$allPermissions = $conn->query("SELECT * FROM permissions ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal fade" id="modalAjouterRole" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="post" action="/eaglesuite/api/roles/ajouter.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Ajouter un rôle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label>Nom du rôle</label>
          <input type="text" name="nom" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Description</label>
          <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
          <label>Permissions (cocher)</label>
          <div class="row">
            <?php foreach ($allPermissions as $perm): ?>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" 
                         name="permissions[]" 
                         value="<?= htmlspecialchars($perm['code'], ENT_QUOTES, 'UTF-8') ?>" 
                         id="perm_<?= $perm['id'] ?>">
                  <label class="form-check-label" for="perm_<?= $perm['id'] ?>">
                    <?= htmlspecialchars($perm['description'], ENT_QUOTES, 'UTF-8' ?: $perm['code']) ?>
                  </label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Ajouter</button>
      </div>
    </form>
  </div>
</div>
