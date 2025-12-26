<div class="modal fade" id="modalModifierDepot" tabindex="-1">
  <div class="modal-dialog">
    <form action="../../api/depots/modifier.php" method="post">
      <input type="hidden" name="id" id="editDepotId">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Modifier un dépôt</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" id="editDepotNom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Description</label>
            <textarea name="description" id="editDepotDescription" class="form-control"></textarea>
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
    const editButtons = document.querySelectorAll(".btnEditDepot");
    editButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("editDepotId").value = this.dataset.id;
            document.getElementById("editDepotNom").value = this.dataset.nom;
            document.getElementById("editDepotDescription").value = this.dataset.description;
        });
    });
});
</script>
