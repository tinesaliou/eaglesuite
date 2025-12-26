<!-- Modal Annuler Vente -->
<div class="modal fade" id="modalAnnulerVente" tabindex="-1" aria-labelledby="modalAnnulerVenteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formAnnulerVente" method="POST" action="/eaglesuite/api/ventes/annuler.php">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalAnnulerVenteLabel">Annuler la vente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="annulerVenteId">
          <p class="fs-6">
            Êtes-vous sûr de vouloir <strong class="text-danger">annuler cette vente</strong> ?
            <br> Cette action est irréversible et le stock sera automatiquement réajusté dans le dépôt concerné.
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non, fermer</button>
          <button type="submit" class="btn btn-danger">Oui, annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>
