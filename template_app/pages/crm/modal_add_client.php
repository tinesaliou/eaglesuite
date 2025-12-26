<!-- Modal : Ajouter un client CRM -->
<div class="modal fade" id="modalAddClient" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Ajouter un client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formAddClient">
                <div class="modal-body">

                    <div id="addClientAlert"></div>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Nom complet *</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Type client</label>
                            <select name="type" class="form-select">
                                <option value="Particulier">Particulier</option>
                                <option value="Entreprise">Entreprise</option>
                                <option value="Passager">Passager</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Statut</label>
                            <select name="statut" class="form-select">
                                <option value="Actif">Actif</option>
                                <option value="Inactif">Inactif</option>
                                <option value="Prospect">Prospect</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Origine</label>
                            <select name="origine" class="form-select">
                                <option value="">--</option>
                                <option>Facebook</option>
                                <option>WhatsApp</option>
                                <option>Référence</option>
                                <option>Site Web</option>
                                <option>Autre</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Secteur d'activité</label>
                            <input type="text" name="secteur" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-check">
                                <input type="checkbox" name="exonere" class="form-check-input">
                                <span class="form-check-label">Client exonéré ?</span>
                            </label>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary" type="submit">Créer le client</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('formAddClient').addEventListener('submit', async function(e){
    e.preventDefault();

    let formData = new FormData(this);
    formData.append("action", "create_client");

    let res = await fetch("/{{TENANT_DIR}}/pages/crm/actions.php", {
        method: "POST",
        body: formData
    });

    let data = await res.json();

    if(!data.success){
        document.getElementById('addClientAlert').innerHTML =
            `<div class="alert alert-danger">${data.error}</div>`;
        return;
    }

    // Success
    location.href = "/{{TENANT_DIR}}/index.php?page=crm_client&id=" + data.id;
});
</script>
