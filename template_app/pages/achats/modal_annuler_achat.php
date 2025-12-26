<!-- Modal Annuler Achat -->
<div class="modal fade" id="modalAnnulerAchat" tabindex="-1" aria-labelledby="modalAnnulerAchatLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="/{{TENANT_DIR}}/pages/achats/annuler_achat.php">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalAnnulerAchatLabel">Confirmer l'annulation de l'achat</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="achatIdAnnuler">
          <p>Êtes-vous sûr de vouloir <strong class="text-danger">annuler cet achat</strong> ?</p>
          <p class="small text-muted">Cette action va réduire le stock correspondant.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non, fermer</button>
          <button type="submit" class="btn btn-danger">Oui, annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>


