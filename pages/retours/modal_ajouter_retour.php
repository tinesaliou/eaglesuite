<?php
// modal_ajouter_retour.php
require_once __DIR__ . '/../../config/db.php';

// Données pour selects
$produits = $conn->query("SELECT id, reference, nom, prix_vente FROM produits ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$depots   = $conn->query("SELECT id, nom FROM depots ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$clients  = $conn->query("SELECT idClient, nom FROM clients ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$fournisseurs = $conn->query("SELECT id, nom FROM fournisseurs ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);

// Options produits
$produitsOptionsHtml = '';
foreach ($produits as $p) {
    $label = htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8');
    if (!empty($p['reference'])) {
        $label .= ' (' . htmlspecialchars($p['reference'], ENT_QUOTES, 'UTF-8') . ')';
    }
    $produitsOptionsHtml .= '<option value="'.htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8').'" data-prix="'.htmlspecialchars($p['prix_vente'], ENT_QUOTES, 'UTF-8').'">'
        . $label . '</option>';
}

// Options dépôts
$depotsOptionsHtml = '';
foreach ($depots as $d) {
    $depotsOptionsHtml .= '<option value="'.htmlspecialchars($d['id'], ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($d['nom'], ENT_QUOTES, 'UTF-8').'</option>';
}

// Options clients
$clientsOptionsHtml = '';
foreach ($clients as $c) {
    $clientsOptionsHtml .= '<option value="'.htmlspecialchars($c['idClient'], ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8').'</option>';
}

// Options fournisseurs
$fournisseursOptionsHtml = '';
foreach ($fournisseurs as $f) {
    $fournisseursOptionsHtml .= '<option value="'.htmlspecialchars($f['id'], ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($f['nom'], ENT_QUOTES, 'UTF-8').'</option>';
}
?>

<div class="modal fade" id="ajouterRetourModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <form id="formAjouterRetour" method="post" action="/eaglesuite/api/retours/ajouter.php">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fa fa-rotate-left"></i> Ajouter un retour</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">Type de retour</label>
              <select name="type_retour" id="typeRetour" class="form-select" required>
                <option value="">-- Sélectionner --</option>
                <option value="client">Retour Client</option>
                <option value="fournisseur">Retour Fournisseur</option>
              </select>
            </div>

            <div class="col-md-3" id="blocClient" style="display:none;">
              <label class="form-label">Client</label>
              <select name="client_id" class="form-select">
                <option value="">-- Sélectionner --</option>
                <?= $clientsOptionsHtml ?>
              </select>
            </div>

            <div class="col-md-3" id="blocFournisseur" style="display:none;">
              <label class="form-label">Fournisseur</label>
              <select name="fournisseur_id" class="form-select">
                <option value="">-- Sélectionner --</option>
                <?= $fournisseursOptionsHtml ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Dépôt</label>
              <select name="depot_id" class="form-select" required>
                <option value="">-- Sélectionner --</option>
                <?= $depotsOptionsHtml ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Date du retour</label>
              <input type="datetime-local" name="date_retour" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label>Raison du retour</label>
            <textarea name="raison" class="form-control" rows="2" required></textarea>
          </div>

          <div class="mb-3">
            <label>Produits retournés</label>
            <table class="table table-sm table-bordered align-middle" id="tableProduitsRetour">
              <thead class="table-light">
                <tr>
                  <th>Produit</th>
                  <th>Quantité</th>
                  <th>Prix unitaire</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <select name="produits[0][id]" class="form-select" required>
                      <option value="">-- Produit --</option>
                      <?= $produitsOptionsHtml ?>
                    </select>
                  </td>
                  <td><input type="number" name="produits[0][quantite]" class="form-control" min="1" required></td>
                  <td><input type="number" name="produits[0][prix]" class="form-control" step="0.01"></td>
                  <td><button type="button" class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash"></i></button></td>
                </tr>
              </tbody>
            </table>

            <button type="button" class="btn btn-sm btn-secondary" id="btnAddRow">
              <i class="fa fa-plus"></i> Ajouter une ligne
            </button>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Afficher Client ou Fournisseur selon le type
document.getElementById('typeRetour').addEventListener('change', function(){
  let type = this.value;
  document.getElementById('blocClient').style.display = (type === 'client') ? 'block' : 'none';
  document.getElementById('blocFournisseur').style.display = (type === 'fournisseur') ? 'block' : 'none';
});

// JS ajout/suppression ligne produit
document.addEventListener('click', function(e){
  if (e.target && (e.target.id === 'btnAddRow' || e.target.closest('#btnAddRow'))) {
    const tbody = document.querySelector('#tableProduitsRetour tbody');
    const idx = tbody.querySelectorAll('tr').length;
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>
        <select name="produits[${idx}][id]" class="form-select" required>
          <option value="">-- Produit --</option>
          <?= addslashes($produitsOptionsHtml) ?>
        </select>
      </td>
      <td><input type="number" name="produits[${idx}][quantite]" class="form-control" min="1" required></td>
      <td><input type="number" name="produits[${idx}][prix]" class="form-control" step="0.01"></td>
      <td><button type="button" class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
  }

  if (e.target && (e.target.classList.contains('btn-remove') || e.target.closest('.btn-remove'))) {
    const btn = e.target.closest('.btn-remove');
    const tr = btn.closest('tr');
    if (tr) tr.remove();
  }
});
</script>
