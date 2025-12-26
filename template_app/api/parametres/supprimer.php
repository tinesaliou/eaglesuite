  <div class="container-fluid ">
      <h1 class="mt-4">Paramétres </h1>
      <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a></li>
          <li class="breadcrumb-item active">Paramétres </li>
      </ol>

      <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fa fa-cog"></i> Paramétres</span>
            </div>

           <div class="card-body">

                <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#entreprise">Entreprise</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#unites">Unités</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#devises">Devises</button></li>
                  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tva">Taux TVA</button></li>
                </ul>

             <div class="tab-content mt-3">
             <!-- ENTREPRISE -->
                <div class="tab-pane fade show active" id="entreprise">
                  <form id="formEntreprise" enctype="multipart/form-data">
                    <div class="mb-3">
                      <label>Nom de l'entreprise</label>
                      <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars(getSetting($conn,'entreprise','nom') ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label>Adresse</label>
                      <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars(getSetting($conn,'entreprise','adresse') ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label>Téléphone</label>
                      <input type="number" name="telephone" class="form-control" value="<?= htmlspecialchars(getSetting($conn,'entreprise','telephone') ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label>Email</label>
                      <input type="text" name="email" class="form-control" value="<?= htmlspecialchars(getSetting($conn,'entreprise','email') ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label>Site web</label>
                      <input type="text" name="site_web" class="form-control" value="<?= htmlspecialchars(getSetting($conn,'entreprise','site_web') ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label>NINEA</label>
                      <input type="text" name="ninea" class="form-control" value="<?= htmlspecialchars(getSetting($conn,'entreprise','ninea') ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label>RCCM</label>
                      <input type="text" name="rccm" class="form-control" value="<?= htmlspecialchars(getSetting($conn,'entreprise','rccm') ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label>Logo</label>
                      <input type="file" name="logo" class="form-control">
                      <?php if ($logo = getSetting($conn,'entreprise','logo')): ?>
                        <img src="/assets/uploads/<?= $logo ?>" alt="logo" style="height:60px;margin-top:8px;">
                      <?php endif; ?>
                    </div>
                    <button class="btn btn-primary">Enregistrer</button>
                  </form>
                </div>

              <!-- UNITES -->
                <div class="tab-pane fade" id="unites">
                  <button class="btn btn-sm btn-success mb-2" data-bs-toggle="modal" data-bs-target="#modalSetting" data-group="unite">Ajouter unité</button>
                  <div id="listUnites"></div>
                </div>

                <!-- DEVISES -->
                <div class="tab-pane fade" id="devises">
                  <button class="btn btn-sm btn-success mb-2" data-bs-toggle="modal" data-bs-target="#modalSetting" data-group="devise">Ajouter devise</button>
                  <div id="listDevises"></div>
                </div>

                <!-- TVA -->
                <div class="tab-pane fade" id="tva">
                  <button class="btn btn-sm btn-success mb-2" data-bs-toggle="modal" data-bs-target="#modalSetting" data-group="tva">Ajouter taux TVA</button>
                  <div id="listTva"></div>
                </div>
            </div>
         </div>
      </div>
  

<?php include __DIR__ . '/modal_parametre.php'; ?>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>
