<div class="modal fade" id="modalAjouterEntreprise" tabindex="-1" aria-labelledby="modalAjouterEntrepriseLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered"> <!-- modal-xl pour élargir -->
    <form action="/{{TENANT_DIR}}/api/entreprise/ajouter.php" method="post" enctype="multipart/form-data">
      <div class="modal-content shadow-lg rounded-4 border-0">
        
        <!-- HEADER -->
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold" id="modalAjouterEntrepriseLabel">
            <i class="fa fa-building me-2"></i> Ajouter une entreprise
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <!-- BODY -->
        <div class="modal-body p-4">
          <div class="row g-4">
            <!-- Nom -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nom de l'entreprise</label>
              <input type="text" name="nom" class="form-control form-control-lg" placeholder="Ex: eaglesuite Sen" required>
            </div>

            <!-- Adresse -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Adresse</label>
              <input type="text" name="adresse" class="form-control form-control-lg" placeholder="Rue - Ville - Pays" required>
            </div>

            <!-- Téléphones -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Téléphone</label>
              <input type="text" name="telephone" class="form-control form-control-lg" placeholder="+221 77 000 00 00" required>
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" name="email" class="form-control form-control-lg" placeholder="exemple@entreprise.com" required>
            </div>

            <!-- Site Web -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Site web</label>
              <input type="text" name="site_web" class="form-control form-control-lg" placeholder="www.monentreprise.sn">
            </div>

            <!-- NINEA -->
            <div class="col-md-3">
              <label class="form-label fw-semibold">NINEA</label>
              <input type="text" name="ninea" class="form-control form-control-lg" placeholder="Numéro NINEA" required>
            </div>

            <!-- RCCM -->
            <div class="col-md-3">
              <label class="form-label fw-semibold">RCCM</label>
              <input type="text" name="rccm" class="form-control form-control-lg" placeholder="Numéro RCCM" required>
            </div>

            <!-- Logo -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Logo de l'entreprise</label>
              <input type="file" name="logo" class="form-control form-control-lg" accept="image/*">
            </div>
          </div>
        </div>

        <!-- FOOTER -->
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="fa fa-times me-1"></i> Annuler
          </button>
          <button class="btn btn-primary btn-lg px-4" type="submit">
            <i class="fa fa-save me-1"></i> Enregistrer
          </button>
        </div>

      </div>
    </form>
  </div>
</div>
