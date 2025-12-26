<div class="modal fade" id="modalModifierRole" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="/eaglesuite/api/roles/modifier.php" class="modal-content">
      <input type="hidden" name="id" id="editRoleId">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Modifier un rôle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Nom du rôle</label>
          <input type="text" name="nom" id="editRoleNom" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Description</label>
          <textarea name="description" id="editRoleDesc" class="form-control"></textarea>
        </div>
        <div class="mb-3">
          <label>Permissions</label><br>
          <?php
          // Charger toutes les permissions disponibles pour afficher la checklist
          $perms = $conn->query("SELECT * FROM permissions ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);
          foreach ($perms as $p): ?>
            <label>
              <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($p['code'], ENT_QUOTES, 'UTF-8') ?>" class="perm-checkbox" id="perm_<?= $p['code'] ?>">
              <?= htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8' ?? $p['code']) ?>
            </label><br>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-warning">Modifier</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script>
// Remplir le modal Modifier avec les données du rôle
document.querySelectorAll('.btnEditRole').forEach(btn => {
  btn.addEventListener('click', () => {
    // 1. Remplir les champs du rôle
    document.getElementById('editRoleId').value = btn.dataset.id;
    document.getElementById('editRoleNom').value = btn.dataset.nom;
    document.getElementById('editRoleDesc').value = btn.dataset.description;

    // 2. Réinitialiser toutes les cases
    document.querySelectorAll('#modalModifierRole input[type=checkbox]').forEach(cb => {
      cb.checked = false;
    });

    // 3. Récupérer les permissions envoyées (JSON)
    let permissions = [];
    try {
      permissions = btn.dataset.permissions ? JSON.parse(btn.dataset.permissions) : [];
    } catch (e) {
      console.error("Erreur parsing permissions JSON", e);
      permissions = [];
    }

    // 4. Cocher les cases correspondant aux permissions du rôle
    permissions.forEach(code => {
      let cb = document.querySelector(`#modalModifierRole input[value="${code}"]`);
      if (cb) cb.checked = true;
    });
  });
});
</script>
