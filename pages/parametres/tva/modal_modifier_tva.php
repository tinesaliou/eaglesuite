<div class="modal fade" id="modalModifierTva" tabindex="-1">
  <div class="modal-dialog">
    <form action="/eaglesuite/api/tva/modifier.php" method="post">
      <input type="hidden" name="id" id="editTvaId">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Modifier une TVA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" id="editTvaNom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Taux</label>
            <input type="number" name="taux" id="editTvaTaux" class="form-control" required>
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
    const editButtons = document.querySelectorAll(".btnEditTva");
    editButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("editTvaId").value = this.dataset.id;
            document.getElementById("editTvaNom").value = this.dataset.nom;
            document.getElementById("editTvaTaux").value = this.dataset.taux;
        });
    });
});
</script>
