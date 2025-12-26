<!-- admin/modal_client_create.php -->
<div class="modal fade" id="modalCreateClient" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/eaglesuite/admin/actions.php">
        <input type="hidden" name="action" value="create_client_saas">
        <div class="modal-header">
          <h5 class="modal-title">Créer un client SaaS</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Société</label><input name="societe" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Sous-domaine</label><input name="subdomain" class="form-control" required placeholder="ex: client1"></div>
            <div class="col-md-4"><label class="form-label">Pack</label>
              <select name="pack" class="form-select">
                <option value="full">Full</option>
                <option value="desktop">Desktop</option>
                <option value="mobile">Mobile</option>
              </select>
            </div>
            <div class="col-md-4"><label class="form-label">Abonnement</label>
              <select name="abonnement" class="form-select">
                <option value="mensuel">Mensuel</option>
                <option value="annuel">Annuel</option>
              </select>
            </div>
            <div class="col-md-4"><label class="form-label">Durée (jours)</label>
              <input type="number" name="duration_days" class="form-control" value="30" min="1"></div>
            <div class="col-md-6"><label class="form-label">Email admin</label><input name="email" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Telephone</label><input name="telephone" class="form-control"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Annuler</button>
          <button class="btn btn-success">Créer</button>
        </div>
      </form>
    </div>
  </div>
</div>
