<!-- modal_invoice_pay.php -->
<div class="modal fade" id="modalPay" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/eaglesuite/admin/actions.php">
        <input type="hidden" name="action" value="pay_facture">
        <input type="hidden" name="facture_id" id="pay_facture_id">
        <div class="modal-header">
          <h5 class="modal-title">Payer la facture</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <p>Montant : <strong id="pay_montant"></strong></p>

          <div class="mb-3">
            <label class="form-label">Mode de paiement</label>
            <select name="mode" class="form-select" required>
              <option value="Mobile Money">Mobile Money</option>
              <option value="Orange Money">Orange Money</option>
              <option value="Virement">Virement</option>
              <option value="Espèces">Espèces</option>
            </select>
          </div>

          <div class="mb-2">
            <label class="form-label">Référence transaction</label>
            <input name="reference" class="form-control">
          </div>

        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Annuler</button>
          <button class="btn btn-success">Marquer comme payé</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('modalPay')?.addEventListener('show.bs.modal', function(ev){
  // set the hidden input when showing (handled in calling code)
});
$('#modalPay').on('shown.bs.modal', function(){
  // set hidden id from button
  const id = $('#pay_facture_id').val();
});
</script>
