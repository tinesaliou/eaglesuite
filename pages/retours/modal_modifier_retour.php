<!-- Modal Modifier Retour -->
<div class="modal fade" id="modalModifier" tabindex="-1" aria-labelledby="modalModifierLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="formModifierRetour" method="post" action="/eaglesuite/api/retours/modifier_retour.php">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title"><i class="fa fa-edit"></i> Modifier Retour</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="modifier_id">

            <div class="row mb-3">
                <!-- Type de retour -->
                <div class="col-md-4">
                    <label>Type</label>
                    <select class="form-control" name="type_retour" id="modifier_type" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="client">Client</option>
                        <option value="fournisseur">Fournisseur</option>
                    </select>
                </div>

                <!-- Sélection client/fournisseur -->
                <div class="col-md-4">
                    <label id="label_client_fournisseur">Client</label>
                    <select class="form-control" name="acteur_id" id="modifier_acteur" required>
                        <option value="">-- Sélectionner --</option>
                        <?php
                        // Clients
                        $clients = $conn->query("SELECT idClient AS id, nom FROM clients")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($clients as $c) {
                            echo "<option value='client-{$c['id']}' data-type='client'>{$c['nom']}</option>";
                        }
                        // Fournisseurs
                        $fournisseurs = $conn->query("SELECT id AS id, nom FROM fournisseurs")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($fournisseurs as $f) {
                            echo "<option value='fournisseur-{$f['id']}' data-type='fournisseur'>{$f['nom']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Dépôt -->
                <div class="col-md-4">
                    <label>Dépôt</label>
                    <select class="form-control" name="depot_id" id="modifier_depot" required>
                        <option value="">-- Sélectionner --</option>
                        <?php
                        $depots = $conn->query("SELECT id, nom FROM depots")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($depots as $d) {
                            echo "<option value='{$d['id']}'>{$d['nom']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Date -->
            <div class="mb-3">
                <label>Date Retour</label>
                <input type="datetime-local" class="form-control" name="date_retour" id="modifier_date" required>
            </div>

            <!-- Raison -->
            <div class="mb-3">
                <label>Raison</label>
                <textarea class="form-control" name="raison" id="modifier_raison" rows="2"></textarea>
            </div>

            <!-- Produits -->
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="tableModifierProduits">
                    <thead class="table-light">
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Produits injectés en AJAX -->
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-secondary" id="btnAjouterProduitModifier">
                    <i class="fa fa-plus"></i> Ajouter Produit
                </button>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Changement du label et filtrage des options en fonction du type
document.getElementById("modifier_type").addEventListener("change", function() {
    const type = this.value;
    const select = document.getElementById("modifier_acteur");
    const label = document.getElementById("label_client_fournisseur");

    if (type === "client") {
        label.textContent = "Client";
    } else if (type === "fournisseur") {
        label.textContent = "Fournisseur";
    }

    // Affiche uniquement les options correspondantes
    Array.from(select.options).forEach(opt => {
        if (opt.value === "") return; // garder la valeur vide
        opt.hidden = !opt.value.startsWith(type);
    });

    // Reset sélection
    select.value = "";
});
</script>
