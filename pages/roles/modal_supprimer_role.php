<div class="modal fade" id="modalDeleteRole" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" action="/api/roles/supprimer.php" class="modal-content">
      <input type="hidden" name="id" id="deleteRoleId">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Supprimer un rôle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Voulez-vous vraiment supprimer le rôle <strong id="deleteRoleNom"></strong> ?</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Supprimer</button>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.btnDeleteRole').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('deleteRoleId').value = btn.dataset.id;
    document.getElementById('deleteRoleNom').textContent = btn.dataset.nom;
  });
});
</script>
