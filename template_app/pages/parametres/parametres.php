<div class="container-fluid ">
  <h1 class="mt-4">Paramètres</h1>
  <ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/{{TENANT_DIR}}/index.php?page=dashboard">Tableau de bord</a></li>
    <li class="breadcrumb-item active">Paramètres</li>
  </ol>

  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="fa fa-cog"></i> Paramètres</span>
    </div>

    <div class="card-body">

      <ul class="nav nav-tabs" id="settingsTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link " id="entreprise-tab" data-bs-toggle="tab" data-bs-target="#entreprise" type="button" role="tab">Entreprise</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="unites-tab" data-bs-toggle="tab" data-bs-target="#unites" type="button" role="tab">Unités</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="devises-tab" data-bs-toggle="tab" data-bs-target="#devises" type="button" role="tab">Devises</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tva-tab" data-bs-toggle="tab" data-bs-target="#tva" type="button" role="tab">Taux TVA</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="caisses-tab" data-bs-toggle="tab" data-bs-target="#caisses" type="button" role="tab">Caisses</button>
        </li>
      </ul>

      <div class="tab-content mt-3">
        <!-- ENTREPRISE -->
        <div class="tab-pane fade " id="entreprise" role="tabpanel" aria-labelledby="entreprise-tab">
          <?php include __DIR__ . '/entreprise/entreprise.php'; ?>
        </div>

        <!-- UNITES -->
        <div class="tab-pane fade show active" id="unites" role="tabpanel" aria-labelledby="unites-tab">
          <?php include __DIR__ . '/unites/unites.php'; ?>
        </div>

        <!-- DEVISES -->
        <div class="tab-pane fade" id="devises" role="tabpanel" aria-labelledby="devises-tab">
          <?php include __DIR__ . '/devises/devises.php'; ?>
        </div>

        <!-- TVA -->
        <div class="tab-pane fade" id="tva" role="tabpanel" aria-labelledby="tva-tab">
          <?php include __DIR__ . '/tva/tva.php'; ?>
        </div>
      </div>

      <div class="tab-pane fade" id="caisses" role="tabpanel" aria-labelledby="caisses-tab">
          <?php include __DIR__ . '/caisses/caisses.php'; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let settingsTabEl = document.querySelectorAll('#settingsTab button[data-bs-toggle="tab"]');

    // Récupère l'onglet actif depuis localStorage
    let activeTab = localStorage.getItem("activeSettingsTab");
    if (activeTab) {
        let triggerEl = document.querySelector(`#settingsTab button[data-bs-target="${activeTab}"]`);
        if (triggerEl) {
            let tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }

    // Sauvegarde l’onglet actif quand on change
    settingsTabEl.forEach(function (tabEl) {
        tabEl.addEventListener("shown.bs.tab", function (event) {
            let target = event.target.getAttribute("data-bs-target");
            localStorage.setItem("activeSettingsTab", target);
        });
    });
});


</script> 

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Récupérer paramètre "tab" de l’URL
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get("tab");

    if (tab) {
        const tabTrigger = document.querySelector(`[data-bs-target="#${tab}"]`);
        if (tabTrigger) {
            const bsTab = new bootstrap.Tab(tabTrigger);
            bsTab.show();
        }
    }
});
</script>



<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

