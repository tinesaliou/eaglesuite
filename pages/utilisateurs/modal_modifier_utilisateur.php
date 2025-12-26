<div class="modal fade" id="modalModifierUtilisateur" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="/eaglesuite/api/utilisateurs/modifier.php">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title">Modifier utilisateur</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">

          <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" id="edit_nom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" id="edit_email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Entreprise ID</label>
            <input type="number" name="entreprise_id" id="edit_entreprise" class="form-control">
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
            <select name="actif" id="edit_actif" class="form-select">
              <option value="1">Actif</option>
              <option value="0">Inactif</option>
            </select>
          </div>
          <p class="text-muted"><small>⚠️ Le mot de passe reste inchangé si vide.</small></p>
          <div class="mb-3">
            <label>Nouveau mot de passe (optionnel)</label>
            <input type="password" name="mot_de_passe" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Mettre à jour</button>
        </div>
      </form>
    </div>
  </div>
</div>
