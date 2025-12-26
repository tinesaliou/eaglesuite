<div class="modal fade" id="modalModifierUnite" tabindex="-1">
  <div class="modal-dialog">
    <form action="/{{TENANT_DIR}}/api/unites/modifier.php" method="post">
      <input type="hidden" name="id" id="editUniteId">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Modifier une unité</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" id="editUniteNom" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-warning" type="submit">Modifier</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const editButtons = document.querySelectorAll(".btnEditUnite");
    editButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("editUniteId").value = this.dataset.id;
            document.getElementById("editUniteNom").value = this.dataset.nom;
        });
    });
});
</script>
