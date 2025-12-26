<!-- modal_abo_edit.php -->
<div class="modal fade" id="modalEditAbo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/eaglesuite/admin/actions.php">
        <input type="hidden" name="action" value="update_abonnement">
        <input type="hidden" name="id" id="edit_abo_id">
        <div class="modal-header">
          <h5 class="modal-title">Modifier l'abonnement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Type</label>
              <select id="edit_abo_type" name="type" class="form-select" required>
                <option value="mensuel">Mensuel</option>
                <option value="annuel">Annuel</option>
                <option value="enterprise">Enterprise</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Prix (F CFA)</label>
              <input id="edit_abo_prix" name="prix" class="form-control" type="number" min="0" step="1" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Date début</label>
              <input id="edit_abo_debut" name="date_debut" class="form-control" type="date" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Date fin</label>
              <input id="edit_abo_fin" name="date_fin" class="form-control" type="date" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Auto-renew</label>
              <select id="edit_abo_auto" name="auto_renew" class="form-select">
                <option value="1">Oui</option>
                <option value="0">Non</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea id="edit_abo_notes" name="notes" class="form-control" rows="3"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Annuler</button>
          <button class="btn btn-warning">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>
