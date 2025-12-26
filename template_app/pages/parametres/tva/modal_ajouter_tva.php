<div class="modal fade" id="modalAjouterTva" tabindex="-1">
  <div class="modal-dialog">
    <form action="/{{TENANT_DIR}}/api/tva/ajouter.php" method="post">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Ajouter un TVA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Taux</label>
            <input type="number" name="taux"  step ="0.001"class="form-control" required>
          </div>
        </div>
        
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </div>
    </form>
  </div>
</div>
