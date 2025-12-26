/*************************************
 *  POS – VARIABLES GLOBALES
 *************************************/
let cart = [];
let paiementSelectionne = null;

/*************************************
 *  AJOUT AU PANIER
 *************************************/
function addToCart(produit) {

    const exist = cart.find(p => p.id === produit.id);

    if (exist) {
        exist.qte++;
    } else {
        cart.push({
            id: produit.id,
            nom: produit.nom,
            prix_vente: parseFloat(produit.prix_vente),
            qte: 1
        });
    }

    renderCart();
}

/*************************************
 *  AFFICHAGE PANIER
 *************************************/
function renderCart() {

    const container = document.getElementById('cartItems');
    container.innerHTML = '';

    if (cart.length === 0) {
        container.innerHTML = `<p class="text-muted text-center">Panier vide</p>`;
        document.getElementById('totalPanier').innerText = 0;
        document.getElementById('monnaie').innerText = 0;
        return;
    }

    cart.forEach((p, index) => {
        container.innerHTML += `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>${p.nom}</strong><br>
                    <small>${p.prix_vente.toLocaleString()} FCFA</small>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-outline-secondary"
                        onclick="changeQte(${index}, -1)">−</button>
                    <span>${p.qte}</span>
                    <button class="btn btn-sm btn-outline-secondary"
                        onclick="changeQte(${index}, 1)">+</button>
                    <button class="btn btn-sm btn-outline-danger"
                        onclick="removeFromCart(${index})">×</button>
                </div>
            </div>
        `;
    });

    recalculerTotal();
}

/*************************************
 *  MODIFIER QUANTITÉ
 *************************************/
function changeQte(index, delta) {

    cart[index].qte += delta;

    if (cart[index].qte <= 0) {
        cart.splice(index, 1);
    }

    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

/*************************************
 *  CALCUL TOTAL / MONNAIE
 *************************************/
function recalculerTotal() {

    let totalHT = 0;

    cart.forEach(p => {
        totalHT += p.prix_vente * p.qte;
    });

    const remise = parseFloat(document.getElementById('remise').value) || 0;
    const montantVerse = parseFloat(document.getElementById('montantVerse').value) || 0;

    const totalTTC = Math.max(totalHT - remise, 0);
    const monnaie = Math.max(montantVerse - totalTTC, 0);

    document.getElementById('totalPanier').innerText =
        totalTTC.toLocaleString();

    document.getElementById('monnaie').innerText =
        monnaie.toLocaleString();

    return {
        totalHT,
        remise,
        totalTTC,
        montantVerse
    };
}

/*************************************
 *  MODAL PAIEMENT
 *************************************/
function openPaiementModal() {

    if (cart.length === 0) {
        alert("Panier vide");
        return;
    }

    paiementSelectionne = null;

    document.querySelectorAll('.paiement-card')
        .forEach(c => c.classList.remove('active'));

    new bootstrap.Modal(
        document.getElementById('paiementModal')
    ).show();
}

/*************************************
 *  SÉLECTION MODE PAIEMENT
 *************************************/
function selectPaiement(mode, el) {

    paiementSelectionne = mode;

    document.querySelectorAll('.paiement-card')
        .forEach(c => c.classList.remove('active'));

    el.classList.add('active');
}

/*************************************
 *  MAPPING PAIEMENT → CAISSE
 *************************************/
function getPaiementMapping(mode) {

    switch (mode) {
        case 'Wave':
        case 'Orange Money':
            return {
                mode_paiement: 'Mobile Money',
                caisse_type: 'mobile_money'
            };

        case 'Carte':
            return {
                mode_paiement: 'Carte',
                caisse_type: 'banque'
            };

        default:
            return {
                mode_paiement: 'Espèces',
                caisse_type: 'especes'
            };
    }
}

/*************************************
 *  VALIDATION PAIEMENT
 *************************************/
function validerPaiement() {

    if (!paiementSelectionne) {
        alert("Veuillez choisir un mode de paiement");
        return;
    }

    const calcul = recalculerTotal();
    const paiement = getPaiementMapping(paiementSelectionne);
    const clientId = document.getElementById('clientSelect')?.value || CLIENT_POS_ID;

    const payload = {
        client_id: clientId,
        produits: cart.map(p => ({
            id: p.id,
            qte: p.qte
        })),
        remise: calcul.remise,
        montant_verse: calcul.montantVerse,
        totalTTC: calcul.totalTTC,
        mode_paiement: paiement.mode_paiement,
        caisse_type: paiement.caisse_type,
        devise_id: 1,
        taux_change: 1
    };

    fetch('/eaglesuite/pages/pos/ajax/save_pos_vente.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("Réponse non JSON :", text);
            throw e;
        }
    })

    .then(res => {

        if (!res.success) {
            alert(res.message);
            return;
        }

       const url = `/eaglesuite/pages/rapports/utils/impression.php?cat=ventes&type=facture_client&ticket=ticket&id=${res.vente_id}`;

        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        document.body.appendChild(iframe);

        iframe.onload = function () {
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 300);
        };


        cart = [];
        renderCart();

        bootstrap.Modal.getInstance(
            document.getElementById('paiementModal')
        ).hide();
    })
    .catch(err => {
        console.error(err);
        alert("Erreur système");
    });
}

