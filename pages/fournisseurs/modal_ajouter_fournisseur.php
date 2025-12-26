<div class="modal fade" id="ajouterFournisseurModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="/eaglesuite/api/fournisseurs/ajouter.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Ajouter un Fournisseur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3"><label>Nom</label>
          <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="mb-3"><label>Téléphone</label>
          <input type="text" name="telephone" class="form-control">
        </div>
        <div class="mb-3"><label>Email</label>
          <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3"><label>Adresse</label>
          <textarea name="adresse" class="form-control"></textarea>
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
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>
