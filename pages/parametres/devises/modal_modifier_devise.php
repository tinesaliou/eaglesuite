<div class="modal fade" id="modalModifierDevise" tabindex="-1">
  <div class="modal-dialog">
    <form action="/eaglesuite/api/devises/modifier.php" method="post">
      <input type="hidden" name="id" id="editDeviseId">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Modifier une devise</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Code</label>
            <input type="text" name="code" id="editDeviseCode" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" id="editDeviseNom" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Symbole</label>
            <input type="text" name="symbole" id="editDeviseSymbole" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Taux</label>
            <input type="number" name="taux" step="0.01" id="editDeviseTaux" class="form-control" required>
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
    const editButtons = document.querySelectorAll(".btnEditDevise");
    editButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("editDeviseId").value = this.dataset.id;
            document.getElementById("editDeviseCode").value = this.dataset.code;
            document.getElementById("editDeviseNom").value = this.dataset.nom;
            document.getElementById("editDeviseSymbole").value = this.dataset.symbole;
            document.getElementById("editDeviseTaux").value = this.dataset.taux;
        });
    });
});
</script>
