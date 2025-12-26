<div class="modal fade" id="modalDeleteUtilisateur" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="/{{TENANT_DIR}}/api/utilisateurs/supprimer.php">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Supprimer Utilisateur</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="supprimer_id">
          <p>⚠️ Voulez-vous vraiment supprimer l’utilisateur : 
            <strong id="supprimer_nom"></strong> ?
          </p>
          <p class="text-muted"><small>Cette action est irréversible.</small></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </div>
      </form>
    </div>
  </div>
</div>
