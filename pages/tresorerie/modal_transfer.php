<!-- modal_transfer.php -->
<div class="modal fade" id="modalTransfer" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="formTransfer" method="post" action="/eaglesuite/api/caisse/transfer.php" class="modal-content">
      <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title">Transférer entre caisses</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label>De</label>
          <select name="from_caisse_id" id="transfer_from" class="form-select" required>
            <?php foreach($caisses as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Vers</label>
          <select name="to_caisse_id" class="form-select" required>
            <?php foreach($caisses as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Montant</label>
          <input type="number" step="0.01" name="montant" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Commentaire</label>
          <textarea name="commentaire" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" type="submit">Transférer</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const formTransfer = document.getElementById("formTransfer");
  const selectFrom   = document.getElementById("transfer_from");
  const selectTo     = formTransfer.querySelector("select[name='to_caisse_id']");

  formTransfer.addEventListener("submit", function(e) {
    if (selectFrom.value === selectTo.value) {
      e.preventDefault();
      alert("⚠️ La caisse source et la caisse destination doivent être différentes.");
      return false;
    }
  });
});
</script>
