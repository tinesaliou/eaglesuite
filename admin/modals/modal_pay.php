<div class="modal fade" id="modalPay" tabindex="-1">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="/eaglesuite/admin/actions.php">
        <input type="hidden" name="action" value="pay_facture">
        <input type="hidden" name="id" id="pay_facture_id">

        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">Paiement Mobile Money</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <label class="form-label">Méthode</label>
          <select class="form-select mb-3" name="moyen" required>
            <option value="">Choisir...</option>
            <option value="orange_money">Orange Money</option>
            <option value="wave">Wave</option>
            <option value="free_money">Free Money</option>
          </select>

          <label class="form-label">Numéro téléphone</label>
          <input name="telephone" class="form-control mb-3" required>

          <label class="form-label">Code de transaction / Ref OM</label>
          <input name="reference" class="form-control">

        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
          <button class="btn btn-success">Confirmer Paiement</button>
        </div>
      </form>
    </div>
  </div>
</div>
