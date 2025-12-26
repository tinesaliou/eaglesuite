<div class="modal fade" id="modalAddOpportunity" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="formAddOpportunity">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nouvelle opportunité</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="action" value="add_opportunity">
                    <input type="hidden" name="client_id" value="<?= $client['idClient'] ?>">

                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label">Titre *</label>
                            <input type="text" name="titre" class="form-control" required placeholder="Ex: Achat gros matériel">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Montant</label>
                            <input type="number" name="montant" class="form-control" step="0.01" min="0" value="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Devise</label>
                            <select name="devise_id" class="form-select">
                                <option value="">-- Aucune --</option>

                                <?php
                                $dev = $conn->query("SELECT id, code FROM devises")->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($dev as $d):
                                ?>
                                    <option value="<?= $d['id'] ?>"><?= $d['code'] ?></option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="col-md-4">
                          <label class="form-label">Étape</label>
                          <select name="etat" class="form-select">
                              <?php
                              $stages = $conn->query("SELECT slug, nom FROM crm_stages WHERE active=1 ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
                              foreach ($stages as $s):
                              ?>
                                  <option value="<?= $s['slug'] ?>"><?= $s['nom'] ?></option>
                              <?php endforeach; ?>
                          </select>
                      </div>

                        <div class="col-md-4">
                            <label class="form-label">Probabilité (%)</label>
                            <input type="number" name="probabilite" class="form-control" min="0" max="100" value="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Date clôture prévue</label>
                            <input type="date" name="date_cloture_prevue" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Utilisateur assigné</label>
                            <select name="utilisateur_id" class="form-select">
                                <option value="">-- Aucun --</option>
                                <?php
                                $users = $conn->query("SELECT id, nom FROM utilisateurs")->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($users as $u):
                                ?>
                                    <option value="<?= $u['id'] ?>"><?= $u['nom'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control"></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button class="btn btn-success">Créer l’opportunité</button>
                </div>

            </form>

        </div>
    </div>
</div>
