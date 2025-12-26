

<!-- Modal Modifier Produit -->
<div class="modal fade" id="modalModifierProduit" tabindex="-1" aria-labelledby="modalModifierProduitLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formModifierProduit" method="POST" action="/eaglesuite/api/produits/modifier.php" enctype="multipart/form-data">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title" id="modalModifierProduitLabel">Modifier Produit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>

        <div class="modal-body row g-3">
          <!-- ID caché -->
          <input type="hidden" name="id" id="editProduitId">

          <!-- Nom -->
          <div class="col-md-6">
            <label for="editNomProduit" class="form-label">Nom du produit</label>
            <input type="text" class="form-control" name="nom" id="editNomProduit" required>
          </div>

          <!-- Référence -->
          <div class="col-md-6">
            <label for="editReferenceProduit" class="form-label">Référence</label>
            <input type="text" class="form-control" name="reference" id="editReferenceProduit">
          </div>

          <!-- Description -->
          <div class="col-12">
            <label for="editDescription" class="form-label">Description</label>
            <textarea class="form-control" name="description" id="editDescription" rows="2"></textarea>
          </div>

          <!-- Prix d'achat -->
          <div class="col-md-4">
            <label for="editPrixAchat" class="form-label">Prix d'achat</label>
            <input type="number" step="0.01" class="form-control" name="prix_achat" id="editPrixAchat">
          </div>

          <!-- Prix de vente -->
          <div class="col-md-4">
            <label for="editPrixVente" class="form-label">Prix de vente</label>
            <input type="number" step="0.01" class="form-control" name="prix_vente" id="editPrixVente">
          </div>

          <!-- Stock -->
          <div class="col-md-4">
            <label for="editStockTotal" class="form-label">Stock total</label>
            <input type="number" class="form-control" name="stock_total" id="editStockTotal" readonly>
          </div>

          <!-- Seuil alerte -->
          <div class="col-md-4">
            <label for="editSeuilAlerte" class="form-label">Seuil d'alerte</label>
            <input type="number" class="form-control" name="seuil_alerte" id="editSeuilAlerte" value="0" min="0">
          </div>

          <!-- Catégorie -->
          <div class="col-md-4">
            <label for="editCategorieId" class="form-label">Catégorie</label>
            <select class="form-select" name="categorie_id" id="editCategorieId">
              <option value="">-- Sélectionner --</option>
              <?php
                $categories = $conn->query("SELECT id, nom FROM categories ORDER BY nom ASC");
                foreach ($categories as $cat) {
                    echo '<option value="' . htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars($cat['nom'], ENT_QUOTES, 'UTF-8') . 
                        '</option>';
                }
              ?>
            </select>
          </div>

          <!-- Dépôt -->
          <div class="col-md-4">
            <label for="editDepotId" class="form-label">Dépôt</label>
            <select class="form-select" name="depot_id" id="editDepotId">
              <option value="">-- Sélectionner --</option>
              <?php
                $depots = $conn->query("SELECT id, nom FROM depots ORDER BY nom ASC");
                foreach ($depots as $d) {
                    echo '<option value="' . htmlspecialchars($d['id'], ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars($d['nom'], ENT_QUOTES, 'UTF-8') . 
                        '</option>';
                }
              ?>
            </select>
          </div>

          <!-- Unité -->
          <div class="col-md-4">
            <label for="editUniteId" class="form-label">Unité</label>
            <select class="form-select" name="unite_id" id="editUniteId">
              <option value="">-- Sélectionner --</option>
              <?php
                $unites = $conn->query("SELECT id, nom FROM unites ORDER BY nom ASC");
                foreach ($unites as $u) {
                    echo '<option value="' . htmlspecialchars($u['id'], ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') . 
                        '</option>';
                }
              ?>
            </select>
          </div>

          <!-- Image -->
          <div class="col-md-6">
            <label for="editImage" class="form-label">Image (nouvelle si besoin)</label>
            <input type="file" class="form-control" name="image" id="editImage" accept="image/*">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Enregistrer les modifications</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const editButtons = document.querySelectorAll(".btnEditProduit, [data-bs-target='#modalModifierProduit']");

    editButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("editProduitId").value = this.dataset.id;
            document.getElementById("editNomProduit").value = this.dataset.nom;
            document.getElementById("editReferenceProduit").value = this.dataset.reference;
            document.getElementById("editDescription").value = this.dataset.description || "";
            document.getElementById("editPrixAchat").value = this.dataset.prixachat;
            document.getElementById("editPrixVente").value = this.dataset.prixvente;
            document.getElementById("editStockTotal").value = this.dataset.stocktotal;
            document.getElementById("editSeuilAlerte").value = this.dataset.seuilalerte || 0;
            document.getElementById("editCategorieId").value = this.dataset.categorieid;

            document.getElementById("editDepotId").value = this.dataset.depotid;
            document.getElementById("editUniteId").value = this.dataset.uniteid;

        });
    });
});
</script>
