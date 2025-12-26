<form method="post" action="ajax/delete_caisse.php">
<input type="hidden" name="id" id="delete_id">

<div class="modal fade" id="modalDeleteCaisse">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-danger text-white">
        <h5>Supprimer caisse</h5>
      </div>

      <div class="modal-body">
        Confirmer la suppression de <strong id="delete_nom"></strong> ?
      </div>

      <div class="modal-footer">
        <button class="btn btn-danger">Supprimer</button>
      </div>

    </div>
  </div>
</div>
</form>
