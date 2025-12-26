<?php
require_once __DIR__ . "/../../config/db.php";

require_once __DIR__ . '/../../config/check_access.php';
$title = "Rapports Achats";

// Liste des fournisseurs
$fournisseurs = $conn->query("SELECT id, nom FROM fournisseurs ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
  <h1 class="mt-4">Rapports</h1>
  <ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/{{TENANT_DIR}}/index.php?page=dashboard">Tableau de bord</a></li>
    <li class="breadcrumb-item active">Rapports</li>
  </ol>

  <div class="card shadow p-4 rounded-3">
    <h4 class="mb-3"><i class="fa fa-truck me-2"></i>Rapports - Achats</h4>

    <form id="formReportsAchat" class="row g-3 align-items-end">
      <!-- Type de rapport -->
      <div class="col-md-3">
        <label class="form-label fw-semibold">Type de rapport</label>
        <select name="type_rapport" id="type_rapport" class="form-select" required>
          <option value="">-- Sélectionner --</option>
          <option value="bon_commande_fournisseur">Bon de commande fournisseur</option>
          <option value="bon_reception">Bon de réception</option>
          <option value="facture_fournisseur">Facture fournisseur</option>
          <option value="journal_achats">Journal des achats</option>
          <option value="etat_fournisseurs">État des fournisseurs</option>
          <option value="fiche_fournisseur">Fiche fournisseur</option>
        </select>
      </div>

      <!-- Date de début -->
      <div class="col-md-3">
        <label class="form-label fw-semibold">Du</label>
        <input type="date" name="date_debut" id="date_debut" class="form-control">
      </div>

      <!-- Date de fin -->
      <div class="col-md-3">
        <label class="form-label fw-semibold">Au</label>
        <input type="date" name="date_fin" id="date_fin" class="form-control">
      </div>

      <!-- Fournisseur -->
      <div class="col-md-3">
        <label class="form-label fw-semibold">Fournisseur</label>
        <select name="fournisseur_id" id="fournisseur_id" class="form-select">
          <option value="">-- Tous les fournisseurs --</option>
          <?php foreach($fournisseurs as $f): ?>
            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nom'], ENT_QUOTES, 'UTF-8') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Boutons -->
      <div class="col-md-4 d-flex gap-2 mt-3">
        <button type="button" class="btn btn-outline-primary w-50" onclick="imprimerRapport('pdf')">
          <i class="fa fa-file-pdf"></i> PDF
        </button>
        <button type="button" class="btn btn-outline-success w-50" onclick="imprimerRapport('xls')">
          <i class="fa fa-file-excel"></i> Excel
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function imprimerRapport(format) {
    const type = document.getElementById('type_rapport').value; // doit correspondre à $_POST['type_rapport']
    if (!type) {
      alert("Veuillez sélectionner un type de rapport !");
      return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/{{TENANT_DIR}}/pages/rapports/export_report_achats.php';
    form.target = '_blank';

    // Paramètres à envoyer
    const params = { type_rapport: type, format: format };
    const debut = document.getElementById('date_debut').value;
    const fin = document.getElementById('date_fin').value;
    const fournisseur = document.getElementById('fournisseur_id').value;

    if (debut) params.date_debut = debut;
    if (fin) params.date_fin = fin;
    if (fournisseur) params.fournisseur_id = fournisseur;

    for (const key in params) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = params[key];
      form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
  }

 
</script>
