<!-- modal_invoice_create.php -->
<div class="modal fade" id="modalCreateInvoice" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/eaglesuite/admin/actions.php">
        <input type="hidden" name="action" value="create_facture">
        <div class="modal-header">
          <h5 class="modal-title">Créer une facture</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Client (ID)</label>
              <input name="client_id" class="form-control" type="number" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Montant (F CFA)</label>
              <input name="montant" class="form-control" type="number" min="0" required>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
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
