<form method="post" action="ajax/save_caisse.php">
<div class="modal fade" id="modalAjouterCaisse">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5>Ajouter une caisse</h5>
      </div>

      <div class="modal-body">
        <input name="code" class="form-control mb-2" placeholder="Code" required>
        <input name="nom" class="form-control mb-2" placeholder="Nom" required>

        <select name="type" class="form-select mb-2">
          <option value="especes">Espèces</option>
          <option value="mobile_money">Mobile Money</option>
          <option value="banque">Banque</option>
        </select>

        <input name="solde_initial" type="number" class="form-control" placeholder="Solde initial">
      </div>

      <div class="modal-footer">
        <button class="btn btn-success">Enregistrer</button>
      </div>

    </div>
  </div>
</div>
</form>
