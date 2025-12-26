<!-- Modal Supprimer Retour -->
<div class="modal fade" id="modalSupprimer" tabindex="-1" aria-labelledby="modalSupprimerLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="/{{TENANT_DIR}}/api/retours/supprimer_retour.php">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fa fa-trash"></i> Supprimer Retour</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="supprimer_id">
          <p>Voulez-vous vraiment supprimer ce retour ?</p>
          <p class="text-danger"><small>⚠️ Cette action est irréversible.</small></p>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Supprimer</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>
