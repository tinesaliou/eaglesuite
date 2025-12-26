<?php
include __DIR__ . '/../../config/db.php';

$produits = $conn->query("SELECT id, nom, reference, prix_vente, image FROM produits ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$depots = $conn->query("SELECT id, nom FROM depots ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$devises = $conn->query("SELECT id, code, symbole, taux_par_defaut FROM devises WHERE actif=1 ORDER BY code DESC")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT COALESCE(SUM(totalTTC),0) AS total FROM ventes WHERE DATE(date_vente)=CURDATE()");
$ventesJour = $stmt->fetchColumn();

$produitsOptionsHtml = '';
foreach ($produits as $p) {
    $label = htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8');
    if (!empty($p['reference'])) {
        $label .= ' (' . htmlspecialchars($p['reference'], ENT_QUOTES, 'UTF-8') . ')';
    }
    $produitsOptionsHtml .= '<option value="'.htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8').'" data-prix="'.htmlspecialchars($p['prix_vente'], ENT_QUOTES, 'UTF-8').'">'
        . $label . '</option>';
}

$depotsOptionsHtml = '';
foreach ($depots as $d) {
    $depotsOptionsHtml .= '<option value="'.htmlspecialchars($d['id'], ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($d['nom'], ENT_QUOTES, 'UTF-8').'</option>';
}

$devisesOptionsHtml = '';
foreach ($devises as $dev) {
    $devisesOptionsHtml .= '<option value="'.htmlspecialchars($dev['id'], ENT_QUOTES, 'UTF-8').'" data-taux="'.htmlspecialchars($dev['taux_par_defaut'], ENT_QUOTES, 'UTF-8').'" data-code="'.htmlspecialchars($dev['code'], ENT_QUOTES, 'UTF-8').'" data-symbole="'.htmlspecialchars($dev['symbole'], ENT_QUOTES, 'UTF-8').'">'.$dev['code'].' ('.$dev['symbole'].')</option>';
}
?>

<!-- Modal Ajouter Vente -->
<div class="modal fade" id="ajouterVenteModal" tabindex="-1" aria-labelledby="ajouterVenteLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="formAjouterVente" method="POST" action="/eaglesuite/api/ventes/ajouter.php">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="ajouterVenteLabel">Ajouter une vente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <!-- Client / Date / Devise -->
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label">Client</label>
              <select name="client_id" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <?php
                  $clients = $conn->query("SELECT idClient, nom, exonere FROM clients ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($clients as $c) {
                      echo '<option value="'.htmlspecialchars($c['idClient'], ENT_QUOTES, 'UTF-8').'" data-exonere="'.htmlspecialchars($c['exonere'], ENT_QUOTES, 'UTF-8').'">'
                          .htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8').'</option>';
                  }
                ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Date</label>
              <input type="datetime-local" name="date_vente" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
            </div>

            <div class="col-md-3">
              <label class="form-label">Devise</label>
              <select name="devise_id" id="deviseSelect" class="form-control" required>
                <?= $devisesOptionsHtml ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Taux de change</label>
              <input type="number" step="0.0001" name="taux_change" id="tauxChange" class="form-control" readonly>
            </div>
          </div>

          <!-- Lignes produits -->
          <div id="produitsContainer">
            <div class="row mb-2 align-items-center" id="ligneProduit0">
              <div class="col-md-3">
                <select name="produits[0][id]" class="form-control produitSelect" onchange="mettreAJourPrix(0)">
                  <option value="">-- Produit --</option>
                  <?= $produitsOptionsHtml ?>
                </select>
              </div>
              <div class="col-md-2">
                <select name="produits[0][depot_id]" class="form-control depotSelect" onchange="mettreAJourStock(0)">
                  <?= $depotsOptionsHtml ?>
                </select>
              </div>
              <div class="col-md-1">
                <input type="number" name="produits[0][quantite]" class="form-control quantiteInput" min="1" value="1"
                       oninput="calculerLigne(0)" required>
              </div>
              <div class="col-md-2">
                <input type="number" step="0.01" name="produits[0][prix_unitaire]" class="form-control prixInput" id="prixUnitaire0" readonly>
              </div>
              <div class="col-md-2">
                <input type="text" class="form-control sousTotal" id="sousTotal0" readonly>
              </div>
              <div class="col-auto">
                <span id="stockDispo0" class="badge bg-info">Stock : -</span>
              </div>
              <div class="col-auto">
                <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigneProduit(0)">🗑️</button>
              </div>
            </div>
          </div>

          <div class="mt-2 mb-3">
            <button type="button" class="btn btn-success btn-sm" onclick="ajouterLigneProduit()">➕ Ajouter un produit</button>
          </div>

          <!-- Totaux -->
          <div class="row g-3 align-items-end">
            <div class="col-md-3">
              <label>Total HT</label>
              <input type="text" id="totalBrutInput" class="form-control" readonly value="0">
            </div>
            <div class="col-md-3">
              <label>Taxe</label>
              <input type="text" id="taxe" name="taxe" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label>Remise (%)</label>
              <input type="number" id="remise" name="remise" class="form-control" value="0" min="0" step="0.01" oninput="calculerTotal()">
            </div>
            <div class="col-md-3">
              <label>Total TTC (après remise)</label>
              <input type="text" id="totalTTCNetInput" class="form-control" readonly value="0">
              <input type="hidden" id="hiddenTotalDevise" name="total_devise" value="0">
              <input type="hidden" id="hiddenTotalCFA" name="total_cfa" value="0">
            </div>
          </div>

          <!-- Paiement -->
          <div class="row g-3 align-items-end mt-3">
            <div class="col-md-3">
              <label>Montant versé</label>
              <input type="number" id="montantVerse" name="montant_verse" class="form-control" value="0" min="0" step="0.01" oninput="calculerReste()">
            </div>
            <div class="col-md-3">
              <label>Reste à payer</label>
              <input type="text" id="resteAPayer" class="form-control is-invalid" readonly value="0">
            </div>
            <div class="col-md-3">
              <label>Mode Paiement</label>
              <select name="mode_paiement" class="form-control">
                <option>Espèces</option>
                <option>Virement</option>
                <option>Chèque</option>
                <option>Mobile Money</option>
              </select>
            </div>
          </div>

          <input type="hidden" name="statut" id="statutVente" value="Payé">

          <div class="mt-3">
           <div class="col-md-3">
            <label>Total vente jour</label>
           <input type="number" name="vente_jour" class="form-control" value="<?= $ventesJour ?>" readonly>
          </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const produitsOptions = `<?= $produitsOptionsHtml ?>`;
const depotsOptions = `<?= $depotsOptionsHtml ?>`;

let produitIndex = 1;
let deviseTaux = 1;
let deviseCode = "CFA";

document.addEventListener('DOMContentLoaded', () => {
  const selectDevise = document.getElementById('deviseSelect');
  const tauxInput = document.getElementById('tauxChange');
  selectDevise.addEventListener('change', () => {
    const opt = selectDevise.options[selectDevise.selectedIndex];
    deviseTaux = parseFloat(opt.getAttribute('data-taux')) || 1;
    deviseCode = opt.getAttribute('data-code');
    tauxInput.value = deviseTaux;
    document.querySelectorAll('.produitSelect').forEach((el,i)=>mettreAJourPrix(i));
    calculerTotal();
  });
  selectDevise.dispatchEvent(new Event('change'));
});

function ajouterLigneProduit() {
  const idx = produitIndex++;
  const html = `
  <div class="row mb-2 align-items-center" id="ligneProduit${idx}">
    <div class="col-md-3">
      <select name="produits[${idx}][id]" class="form-control produitSelect" onchange="mettreAJourPrix(${idx})">
        <option value="">-- Produit --</option>${produitsOptions}
      </select>
    </div>
    <div class="col-md-2">
      <select name="produits[${idx}][depot_id]" class="form-control depotSelect" onchange="mettreAJourStock(${idx})">
        ${depotsOptions}
      </select>
    </div>
    <div class="col-md-1">
      <input type="number" name="produits[${idx}][quantite]" class="form-control quantiteInput" min="1" value="1" oninput="calculerLigne(${idx})">
    </div>
    <div class="col-md-2">
      <input type="number" step="0.01" name="produits[${idx}][prix_unitaire]" class="form-control prixInput" id="prixUnitaire${idx}" readonly>
    </div>
    <div class="col-md-2">
      <input type="text" class="form-control sousTotal" id="sousTotal${idx}" readonly>
    </div>
    <div class="col-auto">
      <span id="stockDispo${idx}" class="badge bg-info">Stock : -</span>
    </div>
    <div class="col-auto">
      <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigneProduit(${idx})">🗑️</button>
    </div>
  </div>`;
  document.getElementById('produitsContainer').insertAdjacentHTML('beforeend', html);
}

function supprimerLigneProduit(i){document.getElementById('ligneProduit'+i)?.remove();calculerTotal();}

function mettreAJourPrix(i){
  const sel=document.querySelector(`#ligneProduit${i} .produitSelect`);
  if(!sel)return;
  const prixCFA=parseFloat(sel.options[sel.selectedIndex]?.getAttribute('data-prix'))||0;
  const prixDevise=prixCFA/deviseTaux;
  document.getElementById('prixUnitaire'+i).value=prixDevise.toFixed(2);
  calculerLigne(i);
  mettreAJourStock(i);
}
function mettreAJourStock(index) {
  const produitSelect = document.querySelector(`#ligneProduit${index} .produitSelect`);
  const depotSelect = document.querySelector(`#ligneProduit${index} .depotSelect`);
  const stockSpan = document.getElementById('stockDispo' + index);
  if (!produitSelect || !depotSelect || !stockSpan) return;

  const produitId = produitSelect.value;
  const depotId = depotSelect.value;
  if (!produitId || !depotId) {
    stockSpan.innerText = "Stock : -";
    return;
  }

  fetch(`/eaglesuite/api/ventes/get_stock.php?produit_id=${encodeURIComponent(produitId)}&depot_id=${encodeURIComponent(depotId)}`)
    .then(r => r.json())
    .then(json => {
      const s = (json && json.stock !== undefined) ? json.stock : 0;
      stockSpan.innerText = "Stock : " + s;
    })
    .catch(() => stockSpan.innerText = "Stock : ?");
}

/* function calculerLigne(i){
  const q=parseFloat(document.querySelector(`#ligneProduit${i} .quantiteInput`)?.value||0);
  const p=parseFloat(document.getElementById('prixUnitaire'+i)?.value||0);
  document.getElementById('sousTotal'+i).value=(q*p).toFixed(2);
  calculerTotal();
} */

  function calculerLigne(index) {
  const qInput = document.querySelector(`#ligneProduit${index} .quantiteInput`);
  const prixInput = document.getElementById("prixUnitaire" + index);
  const stInput = document.getElementById("sousTotal" + index);
  const stockSpan = document.getElementById("stockDispo" + index);

  let q = parseFloat(qInput?.value || 0);
  const prix = parseFloat(prixInput?.value || 0);

  // Vérification du stock dispo
  let stockMax = null;
  if (stockSpan && stockSpan.innerText.includes("Stock :")) {
    const parts = stockSpan.innerText.split(":");
    if (parts[1]) {
      stockMax = parseFloat(parts[1].trim());
    }
  }

  if (stockMax !== null && !isNaN(stockMax) && q > stockMax) {
    alert("❌ La quantité demandée (" + q + ") dépasse le stock disponible (" + stockMax + ").");
    q = stockMax;
    qInput.value = stockMax; // correction auto
  }

  const st = q * prix;
  if (stInput) stInput.value = st.toFixed(2);

  calculerTotal();
}


function calculerTotal(){
  let totalDeviseHT = 0;
  document.querySelectorAll('.sousTotal').forEach(el=>{
    totalDeviseHT += parseFloat(el.value)||0;
  });

  // 1. Récupération client & exonération
  const clientSelect = document.querySelector('select[name="client_id"]');
  const option = clientSelect?.options[clientSelect.selectedIndex];
  const exonere = option ? option.getAttribute('data-exonere') : "1"; // par défaut exonéré

  // 2. Calcul taxe
  let taxeMontant = 0;
  if (exonere === "0") {
    // soumis TVA
    taxeMontant = totalDeviseHT * 0.18;
  } else {
    // exonéré
    taxeMontant = 0;
  }

  // 3. Remise
  const remise = parseFloat(document.getElementById('remise').value||0);
  let totalBrut = totalDeviseHT + taxeMontant;
  let totalNet = totalBrut - (totalBrut*remise/100);

  // 4. Conversion CFA
  const totalCFA = totalNet * deviseTaux;

  // 5. MAJ affichage
  document.getElementById('totalBrutInput').value = totalDeviseHT.toFixed(2)+" "+deviseCode;
  document.getElementById('taxe').value = taxeMontant.toFixed(2)+" "+deviseCode;
  document.getElementById('totalTTCNetInput').value = totalNet.toFixed(2)+" "+deviseCode;

  // champs cachés
  document.getElementById('hiddenTotalDevise').value = totalNet.toFixed(2);
  document.getElementById('hiddenTotalCFA').value = totalCFA.toFixed(2);

  calculerReste();
}


function calculerReste(){
  const total=parseFloat(document.getElementById('hiddenTotalDevise').value||0);
  const verse=parseFloat(document.getElementById('montantVerse').value||0);
  const reste=total-verse;
  const champ=document.getElementById('resteAPayer');
  champ.value=reste.toFixed(2)+" "+deviseCode;
  const statut=document.getElementById('statutVente');
  if(reste>0){champ.classList.add("is-invalid");champ.classList.remove("is-valid");statut.value="Impayé";}
  else{champ.classList.add("is-valid");champ.classList.remove("is-invalid");statut.value="Payé";}
}


</script>
