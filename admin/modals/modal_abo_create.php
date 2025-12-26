<!-- modal_abo_create.php -->
<div class="modal fade" id="modalCreateAbo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/eaglesuite/admin/actions.php">
        <input type="hidden" name="action" value="create_abonnement">

        <div class="modal-header">
          <h5 class="modal-title">Créer un abonnement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label">Client (ID)</label>
              <input name="client_id" class="form-control" type="number" required>
              <div class="form-text">ID du client depuis Clients SaaS</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Type d’abonnement</label>
              <select name="type" class="form-select" required>
                <option value="mensuel">Mensuel</option>
                <option value="annuel">Annuel</option>
                <option value="enterprise">Enterprise</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Prix d’acquisition (F CFA)</label>
              <input name="prix_acquisition" class="form-control" type="number" min="0" step="1" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Montant maintenance mensuelle (F CFA)</label>
              <input name="prix_maintenance" class="form-control" type="number" min="0" step="1" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Auto-renew</label>
              <select name="auto_renew" class="form-select">
                <option value="1">Oui</option>
                <option value="0" selected>Non</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Date début</label>
              <input name="date_debut" class="form-control" type="date" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Date fin</label>
              <input name="date_fin" class="form-control" type="date" required>
            </div>

            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" class="form-control"></textarea>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Annuler</button>
          <button class="btn btn-success">Créer l'abonnement</button>
        </div>
      </form>
    </div>
  </div>
</div>
