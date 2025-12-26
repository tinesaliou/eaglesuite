<!-- admin/modal_client_edit.php -->
<div class="modal fade" id="modalEditClient" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/eaglesuite/admin/actions.php">
        <input type="hidden" name="action" value="update_client_saas">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-header">
          <h5 class="modal-title">Modifier client</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Société</label><input name="societe" id="edit_societe" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Sous-domaine</label><input name="subdomain" id="edit_subdomain" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Pack</label><input name="pack" id="edit_pack" class="form-control"></div>
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
