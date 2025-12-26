<div class="modal fade" id="modalSetting" tabindex="-1">
  <div class="modal-dialog">
    <form id="formSetting" class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Ajouter</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="groupe" id="setting_groupe" />
        <div class="mb-3">
          <label>Clé / Nom</label>
          <input name="cle" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Valeur (ou taux)</label>
          <input name="valeur" class="form-control" required>
        </div>
        <div class="form-check">
          <input type="checkbox" name="actif" class="form-check-input" checked> Actif
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('modalSetting').addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  var group = button.getAttribute('data-group');
  document.getElementById('setting_groupe').value = group;
  document.querySelector('#modalSetting .modal-title').textContent = 'Ajouter ' + group;
});
</script>
