<div class="modal fade" id="modalModifierEntreprise" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="/{{TENANT_DIR}}/api/entreprise/modifier.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" id="editEntrepriseId">
      <div class="modal-content">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title">Modifier l'entreprise</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
       
        <div class="modal-body">
          <div class="row g-3">
            
            <!-- Nom -->
            <div class="col-md-6">
              <label class="form-label">Nom</label>
              <input type="text" name="nom" id="editEntrepriseNom" class="form-control" required>
            </div>

            <!-- Adresse -->
            <div class="col-md-6">
              <label class="form-label">Adresse</label>
              <input type="text" name="adresse" id="editEntrepriseAdresse" class="form-control" required>
            </div>

            <!-- Téléphone -->
            <div class="col-md-6">
              <label class="form-label">Téléphone</label>
              <input type="text" name="telephone" id="editEntrepriseTelephone" class="form-control" required>
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" id="editEntrepriseEmail" class="form-control">
            </div>

            <!-- Site Web -->
            <div class="col-md-6">
              <label class="form-label">Site web</label>
              <input type="text" name="site_web" id="editEntrepriseSite_web" class="form-control">
            </div>

            <!-- NINEA -->
            <div class="col-md-3">
              <label class="form-label">NINEA</label>
              <input type="text" name="ninea" id="editEntrepriseNinea" class="form-control">
            </div>

            <!-- RCCM -->
            <div class="col-md-3">
              <label class="form-label">RCCM</label>
              <input type="text" name="rccm" id="editEntrepriseRccm" class="form-control">
            </div>

            <!-- Logo -->
            <div class="col-md-6">
              <label class="form-label">Logo</label>
              <input type="file" name="logo" id="editEntrepriseLogo" class="form-control" accept="image/*">
              
              <!-- Vignette du logo -->
              <div class="mt-2" id="previewLogoContainer" style="display:none;">
                <img id="previewLogo" src="" alt="Logo actuel" style="max-height: 80px; border:1px solid #ccc; padding:3px; border-radius:5px;">
              </div>
              
              <!-- Stocker l'ancien logo -->
              <input type="hidden" name="old_logo" id="oldLogo">
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-warning" type="submit">Modifier</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const editButtons = document.querySelectorAll(".btnEditEntreprise");

    editButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            // Remplir les champs
            document.getElementById("editEntrepriseId").value = this.dataset.id;
            document.getElementById("editEntrepriseNom").value = this.dataset.nom;
            document.getElementById("editEntrepriseAdresse").value = this.dataset.adresse;
            document.getElementById("editEntrepriseTelephone").value = this.dataset.telephone;
            document.getElementById("editEntrepriseEmail").value = this.dataset.email;
            document.getElementById("editEntrepriseSite_web").value = this.dataset.site_web;
            document.getElementById("editEntrepriseNinea").value = this.dataset.ninea;
            document.getElementById("editEntrepriseRccm").value = this.dataset.rccm;

            // Affichage du logo
            const logo = this.dataset.logo;
            if (logo) {
                document.getElementById("previewLogo").src = "/eaglesuite/public/" + logo;
                document.getElementById("previewLogoContainer").style.display = "block";
                document.getElementById("oldLogo").value = logo;
            } else {
                document.getElementById("previewLogoContainer").style.display = "none";
                document.getElementById("oldLogo").value = "";
            }
        });
    });
});
</script>
