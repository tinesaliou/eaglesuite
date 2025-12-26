<!-- modal_abo_delete.php -->
<div class="modal fade" id="modalDeleteAbo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/eaglesuite/admin/actions.php">
        <input type="hidden" name="action" value="delete_abonnement">
        <input type="hidden" name="id" id="delete_abo_id">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Supprimer abonnement</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Confirmer la suppression de l'abonnement ?</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Annuler</button>
          <button class="btn btn-danger">Supprimer</button>
        </div>
      </form>
    </div>
  </div>
</div>
