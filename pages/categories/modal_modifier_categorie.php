<div class="modal fade" id="modalModifierCategorie" tabindex="-1">
  <div class="modal-dialog">
    <form action="../../api/categories/modifier.php" method="post">
      <input type="hidden" name="id" id="editCategorieId">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Modifier une catégorie</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" id="editCategorieNom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Description</label>
            <textarea name="description" id="editCategorieDescription" class="form-control"></textarea>
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
    const editButtons = document.querySelectorAll(".btnEditCategorie");
    editButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("editCategorieId").value = this.dataset.id;
            document.getElementById("editCategorieNom").value = this.dataset.nom;
            document.getElementById("editCategorieDescription").value = this.dataset.description;
        });
    });
});
</script>
