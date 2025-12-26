<form method="post" action="ajax/update_caisse.php">
<input type="hidden" name="id" id="edit_id">

<div class="modal fade" id="modalModifierCaisse">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-warning">
        <h5>Modifier caisse</h5>
      </div>

      <div class="modal-body">
        <input name="code" id="edit_code" class="form-control mb-2" required>
        <input name="nom" id="edit_nom" class="form-control mb-2" required>

        <select name="type" id="edit_type" class="form-select mb-2">
          <option value="especes">Espèces</option>
          <option value="mobile_money">Mobile Money</option>
          <option value="banque">Banque</option>
        </select>

        <input name="solde_initial" id="edit_solde" type="number" class="form-control">
      </div>

      <div class="modal-footer">
        <button class="btn btn-warning">Mettre à jour</button>
      </div>

    </div>
  </div>
</div>
</form>
