<!-- admin/modal_client_delete.php -->
<div class="modal fade" id="modalDeleteClient" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="/eaglesuite/admin/actions.php">
        <input type="hidden" name="action" value="delete_client_saas">
        <input type="hidden" name="id" id="delete_client_id">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Supprimer</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Confirmer la suppression du client <strong id="delete_client_sub"></strong> ?</p>
          <p class="small text-muted">Cela supprimera la ligne master. Si tu veux aussi supprimer la base du tenant, coche l'option sur le formulaire (ajoute-la si nécessaire).</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Annuler</button>
          <button class="btn btn-danger">Supprimer</button>
        </div>
      </form>
    </div>
  </div>
</div>
