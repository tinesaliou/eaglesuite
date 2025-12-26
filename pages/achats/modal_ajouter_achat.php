<?php
include __DIR__ . '/../../config/db.php';

$produits = $conn->query("SELECT id, reference, nom, prix_achat FROM produits ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$depots   = $conn->query("SELECT id, nom FROM depots ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$devises  = $conn->query("SELECT id, code, symbole, taux_par_defaut FROM devises WHERE actif=1 ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);

$produitsOptionsHtml = '';
foreach ($produits as $p) {
    $label = htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8');
    if (!empty($p['reference'])) {
        $label .= ' (' . htmlspecialchars($p['reference'], ENT_QUOTES, 'UTF-8') . ')';
    }
    $produitsOptionsHtml .= '<option value="'.htmlspecialchars($p['id'], ENT_QUOTES, 'UTF-8').'" data-prix="'.htmlspecialchars($p['prix_achat'], ENT_QUOTES, 'UTF-8').'">'.$label.'</option>';
}

$depotsOptionsHtml = '';
foreach ($depots as $d) {
    $depotsOptionsHtml .= '<option value="'.htmlspecialchars($d['id'], ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($d['nom'], ENT_QUOTES, 'UTF-8').'</option>';
}

$devisesOptionsHtml = '';
foreach ($devises as $dev) {
    $devisesOptionsHtml .= '<option value="'.htmlspecialchars($dev['id'], ENT_QUOTES, 'UTF-8').'" 
                              data-taux="'.htmlspecialchars($dev['taux_par_defaut'], ENT_QUOTES, 'UTF-8').'" 
                              data-code="'.htmlspecialchars($dev['code'], ENT_QUOTES, 'UTF-8').'" 
                              data-symbole="'.htmlspecialchars($dev['symbole'], ENT_QUOTES, 'UTF-8').'">'
                            .$dev['code'].' ('.$dev['symbole'].')</option>';
}
?>

<<!-- Modal Ajouter achat -->
<div class="modal fade" id="ajouterAchatModal" tabindex="-1" aria-labelledby="ajouterAchatLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="formAjouterAchat" method="POST" action="/eaglesuite/api/achats/ajouter.php">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="ajouterAchatLabel">Ajouter un achat</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <!-- Client / Date / Devise -->
          <div class="row g-3 mb-3">
            <div class="col-md-3">
              <label class="form-label">Fournisseur</label>
              <select name="fournisseur_id" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <?php
                  $fournisseurs = $conn->query("SELECT id, nom, exonere FROM fournisseurs ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($fournisseurs as $f) {
                      echo '<option value="'.htmlspecialchars($f['id'], ENT_QUOTES, 'UTF-8').'" data-exonere="'.htmlspecialchars($f['exonere'], ENT_QUOTES, 'UTF-8').'">'
                          .htmlspecialchars($f['nom'], ENT_QUOTES, 'UTF-8').'</option>';
                  }
                ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Date</label>
              <input type="datetime-local" name="date_achat" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
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
              <select name="mode_paiement" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <?php
                  $mode_paiement = $conn->query("SELECT id, code, libelle FROM modes_paiement ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($mode_paiement as $mp) {
                      echo '<option value="'.htmlspecialchars($mp['id'], ENT_QUOTES, 'UTF-8').'">' .htmlspecialchars($mp['libelle'], ENT_QUOTES, 'UTF-8').'</option>';
                  }
                ?>
              </select>
            </div>
          </div>

          <input type="hidden" name="statut" id="statutAchat" value="Payé">
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
/* ------------ CONFIG / ETAT ------------ */
const produitsOptions = `<?= $produitsOptionsHtml ?>`;
const depotsOptions   = `<?= $depotsOptionsHtml ?>`;

let produitIndex = 1;                 // prochaine ligne
let deviseTaux   = 1;                 // nombre FCFA pour 1 unité de la devise sélectionnée
let deviseCode   = "CFA";             // code affiché (ex: USD)
 
/* ------------ HELPERS SÉCURISÉS ------------ */
const $qs = (sel, scope=document) => scope.querySelector(sel);
const $qsa = (sel, scope=document) => Array.from(scope.querySelectorAll(sel));

/* ------------ INIT ------------ */
document.addEventListener('DOMContentLoaded', () => {
  const selectDevise = document.getElementById('deviseSelect');
  const tauxInput    = document.getElementById('tauxChange');
  // si selectDevise existe — connecter changement de devise
  if (selectDevise) {
    const onDeviseChange = () => {
      const opt = selectDevise.options[selectDevise.selectedIndex];
      deviseTaux = parseFloat(opt?.dataset?.taux) || 1;   // taux = FCFA pour 1 unité devise
      deviseCode = opt?.dataset?.code || '';
      if (tauxInput) tauxInput.value = deviseTaux;
      // mettre à jour prix/des sous-totaux existants
      // parcourir tous les conteneurs 'ligneProduitX' présents
      $qsa('[id^="ligneProduit"]').forEach(line => {
        const idx = line.id.replace('ligneProduit','');
        mettreAJourPrix(idx);
      });
      calculerTotal();
    };
    selectDevise.addEventListener('change', onDeviseChange);
    // déclenchement initial
    onDeviseChange();
  }

  // recalcul si fournisseur change (pour taxe/exonération)
  const fournisseurSelect = document.querySelector('select[name="fournisseur_id"]');
  if (fournisseurSelect) fournisseurSelect.addEventListener('change', calculerTotal);

  // init ligne 0 si présent
  setTimeout(()=> {
    if ($qs('#ligneProduit0')) {
      mettreAJourPrix(0);
      mettreAJourStock(0);
    }
    calculerTotal();
  }, 50);
});

/* ------------ AJOUT / SUPPRESSION LIGNES ------------ */
function ajouterLigneProduit() {
  const idx = produitIndex++;
  const html = `
  <div class="row mb-2 align-items-center" id="ligneProduit${idx}">
    <div class="col-md-3">
      <select name="produits[${idx}][id]" class="form-control produitSelect" onchange="mettreAJourPrix(${idx})">
        <option value="">-- Sélectionner --</option>
        ${produitsOptions}
      </select>
    </div>
    <div class="col-md-2">
      <select name="produits[${idx}][depot_id]" class="form-control depotSelect" onchange="mettreAJourStock(${idx})">
        ${depotsOptions}
      </select>
    </div>
    <div class="col-md-1">
      <input type="number" name="produits[${idx}][quantite]" class="form-control quantiteInput" min="1" value="1"
             oninput="calculerLigne(${idx})" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="produits[${idx}][prix_unitaire]" class="form-control prixInput" id="prixUnitaire${idx}" readonly>
    </div>
    <div class="col-md-2">
      <input type="text" class="form-control sousTotal" id="sousTotal${idx}" readonly>
    </div>
    <div class="col-auto">
      <span id="stockDispo${idx}" class="badge bg-secondary">Stock actuel : -</span>
    </div>
    <div class="col-auto">
      <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigneProduit(${idx})">🗑️</button>
    </div>
  </div>`;
  document.getElementById("produitsContainer").insertAdjacentHTML("beforeend", html);
}

function supprimerLigneProduit(i) {
  const el = document.getElementById("ligneProduit" + i);
  if (el) el.remove();
  calculerTotal();
}

/* ------------ PRIX & STOCK ------------ */
/* Met à jour le prix affiché de la ligne selon le produit + devise (base prix en FCFA) */
function mettreAJourPrix(index) {
  try {
    const select = document.querySelector(`#ligneProduit${index} .produitSelect`);
    const prixInput = document.getElementById('prixUnitaire' + index);
    if (!select || !prixInput) return;
    // basePrice en FCFA stocké dans data-prix
    const basePriceFCFA = parseFloat(select.options[select.selectedIndex]?.dataset?.prix) || 0;
    // AFFICHAGE en devise sélectionnée : prix_devise = basePriceFCFA / deviseTaux
    const prixDevise = deviseTaux > 0 ? (basePriceFCFA / deviseTaux) : basePriceFCFA;
    prixInput.value = prixDevise.toFixed(2);
    // garder basePrice en attribute utile si besoin
    prixInput.dataset.baseFcfa = basePriceFCFA;
    calculerLigne(index);
    // mettre à jour stock d'affichage
    mettreAJourStock(index);
  } catch (err) {
    console.error("mettreAJourPrix error:", err);
  }
}

/* Récupère le stock pour (produit, depot) et met à jour le badge (affichage seulement) */
function mettreAJourStock(index) {
  try {
    const produitSelect = document.querySelector(`#ligneProduit${index} .produitSelect`);
    const depotSelect   = document.querySelector(`#ligneProduit${index} .depotSelect`);
    const stockSpan     = document.getElementById('stockDispo' + index);
    if (!produitSelect || !depotSelect || !stockSpan) return;

    const produitId = produitSelect.value;
    const depotId   = depotSelect.value;
    if (!produitId || !depotId) {
      stockSpan.innerText = 'Stock actuel : -';
      stockSpan.className = 'badge bg-secondary';
      return;
    }

    // Appel API (ton endpoint) — adapte l'URL si besoin
    fetch(`/eaglesuite/api/achats/get_stock.php?produit_id=${encodeURIComponent(produitId)}&depot_id=${encodeURIComponent(depotId)}`)
      .then(r => r.json())
      .then(json => {
        const s = (json && typeof json.stock !== 'undefined') ? Number(json.stock) : null;
        if (s === null) {
          stockSpan.innerText = 'Stock actuel : ?';
          stockSpan.className = 'badge bg-secondary';
        } else {
          stockSpan.innerText = 'Stock actuel : ' + s;
          stockSpan.dataset.stock = s; // pour lecture si besoin
          stockSpan.className = 'badge bg-info';
        }
      })
      .catch(() => {
        stockSpan.innerText = 'Stock actuel : ?';
        stockSpan.className = 'badge bg-secondary';
      });
  } catch (err) {
    console.error("mettreAJourStock error:", err);
  }
}

/* ------------ CALCUL LIGNE & TOTAUX ------------ */
/* Pour ACHAT : on n'applique PAS de blocage si quantité > stock (on affiche seulement) */
function calculerLigne(index) {
  try {
    const qInput = document.querySelector(`#ligneProduit${index} .quantiteInput`);
    const prixInput = document.getElementById('prixUnitaire' + index);
    const stInput = document.getElementById('sousTotal' + index);
    if (!qInput || !prixInput || !stInput) return;

    let q = parseFloat(qInput.value || 0);
    const prix = parseFloat(prixInput.value || 0); // prix déjà affiché en devise choisie
    const st = q * prix;
    stInput.value = isFinite(st) ? st.toFixed(2) : '0.00';

    calculerTotal();
  } catch (err) {
    console.error("calculerLigne error:", err);
  }
}

/* Calcul des totaux : travaille en devise affichée (totalDevise), puis convertit en CFA */
function calculerTotal() {
  try {
    // total en devise sélectionnée (les sousTotal sont affichés en devise)
    let totalDevise = 0;
    $qsa('.sousTotal').forEach(el => { totalDevise += parseFloat(el.value || 0); });

    const remisePct = parseFloat(document.getElementById('remise')?.value || 0);

    // taxe selon le fournisseur (exonere attribute) : on lit le select by name
    const fournisseurSel = document.querySelector('select[name="fournisseur_id"]');
    const exonere = fournisseurSel?.options[fournisseurSel.selectedIndex]?.getAttribute('data-exonere') ?? "1";
    const taxeRate = (exonere === "0") ? 0.18 : 0; // 18% si soumis, sinon 0
    const taxeDevise = totalDevise * taxeRate;

    let totalTTCDevise = totalDevise + taxeDevise;
    // appliquer remise %
    totalTTCDevise = totalTTCDevise - (totalTTCDevise * (remisePct / 100));

    // conversion vers FCFA : 1 unité devise = deviseTaux FCFA
    const totalTTC_CFA = totalTTCDevise * deviseTaux;

    // Mettre à jour champs visible
    const totalBrutInput = document.getElementById('totalBrutInput');
    const taxeInput = document.getElementById('taxe');
    const totalTTCNetInput = document.getElementById('totalTTCNetInput');

    if (totalBrutInput) totalBrutInput.value = totalDevise.toFixed(2) + ' ' + (deviseCode || '');
    if (taxeInput) taxeInput.value = taxeDevise.toFixed(2); // montant taxe en devise
    if (totalTTCNetInput) totalTTCNetInput.value = totalTTCDevise.toFixed(2) + ' ' + (deviseCode || '');

    // hidden pour backend (devise & CFA)
    const hiddenDevise = document.getElementById('hiddenTotalDevise');
    const hiddenCFA    = document.getElementById('hiddenTotalCFA');
    if (hiddenDevise) hiddenDevise.value = totalTTCDevise.toFixed(2);
    if (hiddenCFA)    hiddenCFA.value = totalTTC_CFA.toFixed(2);

    // si tu as un champ 'hiddenTotal' attendu (ancien) on peut aussi le remplir en CFA
    const hiddenOld = document.getElementById('hiddenTotal');
    if (hiddenOld) hiddenOld.value = totalTTC_CFA.toFixed(2);

    // mise à jour du reste à payer si visible
    calculerReste();
  } catch (err) {
    console.error("calculerTotal error:", err);
  }
}

function calculerReste() {
  try {
    const totalDeviseInput = document.getElementById("totalTTCNetInput");
    const totalDevise = parseFloat((totalDeviseInput?.value || '').replace(/[^\d.-]/g,'')) || 0;
    const montantVerse = parseFloat(document.getElementById("montantVerse")?.value || 0);
    const reste = totalDevise - montantVerse;
    const field = document.getElementById("resteAPayer");
    if (field) {
      field.value = reste.toFixed(2);
      field.classList.toggle("is-invalid", reste > 0);
      field.classList.toggle("is-valid", reste <= 0);
    }
    const statutField = document.getElementById("statutAchat");
    if (statutField) statutField.value = (reste > 0) ? "Impayé" : "Payé";
  } catch (err) {
    console.error("calculerReste error:", err);
  }
}
</script>
