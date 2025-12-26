<div class="modal fade" id="modalEditClient" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog ">
    <div class="modal-content">
      <form method="POST" action="/eaglesuite/api/clients/modifier.php">
         <div class="modal-header bg-warning">
          <h5 class="modal-title">Modifier Client</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          
          <input type="hidden" name="idClient" id="edit-idClient">

          <div class="mb-4">
            <label for="edit-nom" class="form-label">Nom</label>
            <input type="text" class="form-control" name="nom" id="edit-nom" required>
          </div>

          <div class="mb-4">
            <label for="edit-adresse" class="form-label">Adresse</label>
            <input type="text" class="form-control" name="adresse" id="edit-adresse">
          </div>

          <div class="mb-4">
            <label for="edit-telephone" class="form-label">Téléphone</label>
            <input type="text" class="form-control" name="telephone" id="edit-telephone">
          </div>

          <div class="mb-4">
            <label for="edit-email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="edit-email">
          </div>

          <div class="mb-4">
            <label for="edit-type" class="form-label">Type</label>
            <select class="form-select" name="type" id="edit-type">
              <option value="Entreprise">Entreprise</option>
              <option value="Particulier">Particulier</option>
              <option value="Passager">Passager</option>
            </select>
          </div>

          <div class="form-check">
            <input type="checkbox" class="form-check-input" name="exonere" id="edit-exonere" value="1">
            <label class="form-check-label" for="edit-exonere">Exonéré de taxe ?</label>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>
