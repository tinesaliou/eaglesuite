<!-- Modal Ajouter Produit -->
<div class="modal fade" id="modalAjouterProduit" tabindex="-1" aria-labelledby="modalAjouterProduitLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="/{{TENANT_DIR}}/api/produits/ajouter.php" method="post" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalAjouterProduitLabel">Ajouter un produit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body row g-3">
                <!-- Référence -->
                <div class="col-md-6">
                    <label for="reference" class="form-label">Référence</label>
                    <input type="text" name="reference" id="reference" class="form-control">
                </div>

                <!-- Nom -->
                <div class="col-md-6">
                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="nom" class="form-control" required>
                </div>

                <!-- Description -->
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                </div>

                <!-- Prix d'achat -->
                <div class="col-md-6">
                    <label for="prix_achat" class="form-label">Prix d'achat</label>
                    <input type="number" step="0.01" name="prix_achat" id="prix_achat" class="form-control">
                </div>

                <!-- Prix de vente -->
                <div class="col-md-6">
                    <label for="prix_vente" class="form-label">Prix de vente</label>
                    <input type="number" step="0.01" name="prix_vente" id="prix_vente" class="form-control">
                </div>

                <!-- Stock initial -->
                <div class="col-md-6">
                    <label for="stock_total" class="form-label">Stock initial</label>
                    <input type="number" name="stock_total" id="stock_total" class="form-control" value="0" min="0">
                </div>

                <!-- Seuil alerte -->
                <div class="col-md-6">
                    <label for="seuil_alerte" class="form-label">Seuil d'alerte</label>
                    <input type="number" name="seuil_alerte" id="seuil_alerte" class="form-control" value="0" min="0">
                </div>

                <!-- Catégorie -->
                <div class="col-md-6">
                    <label for="categorie_id" class="form-label">Catégorie</label>
                    <select name="categorie_id" id="categorie_id" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        <?php
                        $cats = $conn->query("SELECT * FROM categories ORDER BY nom ASC");
                        foreach ($cats as $c) {
                            echo "<option value='{$c['id']}'>" . htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Dépôt -->
                <div class="col-md-6">
                    <label for="depot_id" class="form-label">Dépôt</label>
                    <select name="depot_id" id="depot_id" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        <?php
                        $depots = $conn->query("SELECT * FROM depots ORDER BY nom ASC");
                        foreach ($depots as $d) {
                            echo "<option value='{$d['id']}'>" . htmlspecialchars($d['nom'], ENT_QUOTES, 'UTF-8') . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Unité -->
                <div class="col-md-6">
                    <label for="unite_id" class="form-label">Unité</label>
                    <select name="unite_id" id="unite_id" class="form-select">
                        <option value="">-- Sélectionner --</option>
                        <?php
                        $unites = $conn->query("SELECT * FROM unites ORDER BY nom ASC");
                        foreach ($unites as $u) {
                            echo "<option value='{$u['id']}'>" . htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Image -->
                <div class="col-md-6">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </form>
    </div>
</div>
