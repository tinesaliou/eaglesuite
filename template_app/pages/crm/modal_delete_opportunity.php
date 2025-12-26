<div class="modal fade" id="modalDeleteOpportunity" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="/{{TENANT_DIR}}/pages/crm/actions.php">

                <input type="hidden" name="action" value="delete_opportunity">
                <input type="hidden" name="id" id="deleteOppId">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Supprimer l’opportunité</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Confirmer la suppression de :</p>
                    <strong id="delete-opp-name" class="text-danger"></strong>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </div>

            </form>

        </div>
    </div>
</div>
