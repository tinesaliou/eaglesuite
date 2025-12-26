<div class="modal fade" id="modalAjouterUtilisateur" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="/{{TENANT_DIR}}/api/utilisateurs/ajouter.php">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Ajouter un utilisateur</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Entreprise ID</label>
            <input type="number" name="entreprise_id" class="form-control" value="1">
          </div>

          <div class="mb-3">
            <label>Rôle</label>
            <select name="role_id" class="form-control" required>
                <option value="">-- Choisir un rôle --</option>
                <?php
                $roles = $conn->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($roles as $r) {
                $selected = (isset($utilisateur['role_id']) && $utilisateur['role_id'] == $r['id']) ? "selected" : "";
                echo "<option value='{$r['id']}' $selected>{$r['nom']}</option>";
                }
                ?>
            </select>
        </div>

          <div class="mb-3">
            <label>Statut</label>
            <select name="actif" class="form-select">
              <option value="1">Actif</option>
              <option value="0">Inactif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
</div>
