<!-- modal_ajouter_operation.php -->
<div class="modal fade" id="modalAjouterOperation" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="formAjouterOp" method="post" action="/eaglesuite/api/caisses/operation_add.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Ajouter opération (<span id="op_caisse_label"></span>)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="caisse_id" id="op_caisse_id">
        <div class="mb-2">
          <label>Type</label>
          <select name="type" class="form-select" required>
            <option value="entree">Entrée</option>
            <option value="sortie">Sortie</option>
          </select>
        </div>
        <div class="mb-2">
          <label>Montant</label>
          <input type="number" step="0.01" name="montant" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Mode paiement</label>
          <select name="mode_paiement" class="form-select">
            <option>Espèces</option>
            <option>Virement</option>
            <option>Chèque</option>
            <option>Mobile Money</option>
          </select>
        </div>
        <div class="mb-2">
          <label>Référence (vente/achat/...)</label>
          <input type="text" name="reference_table" class="form-control" placeholder="Ex: ventes">
          <input type="hidden" name="reference_id" value="">
        </div>
        <div class="mb-2">
          <label>Commentaire</label>
          <textarea name="commentaire" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </form>
  </div>
</div>
