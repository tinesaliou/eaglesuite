<div class="modal fade" id="modalDeleteClient" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="/{{TENANT_DIR}}/api/clients/supprimer.php">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title text-danger">Supprimer Client</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="idClient" id="delete-idClient">
          <p>⚠️ Voulez-vous vraiment supprimer ce client : 
            <strong id="delete-client-name"></strong> ?
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
