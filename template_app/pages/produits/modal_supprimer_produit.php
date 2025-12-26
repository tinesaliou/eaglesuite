<!-- Modal Supprimer Produit -->
<div class="modal fade" id="modalSupprimerProduit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirmation de suppression</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Voulez-vous vraiment supprimer le produit <strong id="supprimer_nom"></strong> ?</p>
      </div>
      <div class="modal-footer">
        <form method="POST" action="supprimer_produit.php">
          <input type="hidden" name="id" id="supprimer_id">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>
