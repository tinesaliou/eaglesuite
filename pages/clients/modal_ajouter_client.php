<div class="modal fade" id="ajouterClientModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="/eaglesuite/api/clients/ajouter.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Ajouter un client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nom</label>
          <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Téléphone</label>
          <input type="text" name="telephone" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Adresse</label>
          <input type="text" name="adresse" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">Type</label>
          <select name="type" class="form-select">
            <option value="Particulier">Particulier</option>
            <option value="Entreprise">Entreprise</option>
            <option value="Passager">Passager</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Soumis à la taxe ?</label>
          <select name="exonere" class="form-select">
            <option value="0">Oui</option>
            <option value="1">Non (Exonéré)</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>
