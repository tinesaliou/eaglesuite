<?php echo 'Rapports PDF'; ?><?php
require_once __DIR__ . "/../../config/db.php";
$title = "Rapports Ventes";

// Liste des fournisseurs
$clients = $conn->query("SELECT idClient, nom FROM clients ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
  <h1 class="mt-4">Rapports</h1>
  <ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a></li>
    <li class="breadcrumb-item active">Rapports</li>
  </ol>

  <div class="card shadow p-4 rounded-3">
    <h4 class="mb-3"><i class="fa fa-shopping-cart"></i>Rapports - Ventes</h4>

    <form id="formReportsVente" class="row g-3 align-items-end">
      <!-- Type de rapport -->
      <div class="col-md-3">
        <label class="form-label fw-semibold">Type de rapport</label>
    <select name="type_rapport" id="type_rapport" class="form-select" required>
        <option value="">-- Sélectionner --</option>
        <option value="journal_ventes">Journal des ventes</option>
        <option value="etat_clients_impayes">Clients impayés</option>
        <option value="recu_paiement_client">Reçus de paiements</option>
        <option value="fiche_client">Fiche client</option>
        <option value="avoir_client">Avoirs des clients</option>
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
      <div class="col-md-3" >
        <label class="form-label fw-semibold">Client</label>
        <select name="client_id" id="client_id" class="form-select">
          <option value="">-- Tous les clients --</option>
          <?php foreach($clients as $c): ?>
            <option value="<?= $c['idClient'] ?>"><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></option>
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
    form.action = '/eaglesuite/pages/rapports/export_report_ventes.php';
    form.target = '_blank';

    // Paramètres à envoyer
    const params = { type_rapport: type, format: format };
    const debut = document.getElementById('date_debut').value;
    const fin = document.getElementById('date_fin').value;
    const client = document.getElementById('client_id').value;

    if (debut) params.date_debut = debut;
    if (fin) params.date_fin = fin;
    if (client) params.client_id = client;

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
