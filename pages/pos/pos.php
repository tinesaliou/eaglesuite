<?php
require_once __DIR__ . "/../../config/db.php";
//session_start();

if (!isset($_SESSION['user']['id'])) {
    header("Location: /eaglesuite/index.php?page=login");
    exit;
}

$utilisateur_id = $_SESSION['user']['id'];

/* =======================
   SESSION CAISSE ACTIVE
======================= */
$stmt = $conn->prepare("
    SELECT *
    FROM sessions_caisse
    WHERE utilisateur_id = ?
      AND statut = 'ouverte'
    LIMIT 1
");
$stmt->execute([$utilisateur_id]);
$session_caisse = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session_caisse) {
    header("Location: /eaglesuite/index.php?page=pos-entry");
    exit;
}

$caisse_id       = (int)$session_caisse['caisse_id'];
$session_id      = (int)$session_caisse['id'];
$soldeOuverture  = (float)$session_caisse['solde_ouverture'];

/* =======================
   SOLDE JOURNALIER
======================= */
$stmt = $conn->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN type_operation='entree' THEN montant END),0) AS total_entrees,
        COALESCE(SUM(CASE WHEN type_operation='sortie' THEN montant END),0) AS total_sorties
    FROM operations_caisse
    WHERE session_caisse_id = ?
");
$stmt->execute([$session_id]);
$totaux = $stmt->fetch(PDO::FETCH_ASSOC);

$soldeJournalier =  $totaux['total_entrees'] - $totaux['total_sorties'];

/* =======================
   DONNÉES POS
======================= */
$produits = $conn->query("
    SELECT p.id, p.nom, p.prix_vente, p.categorie_id, p.image,
           COALESCE(SUM(sd.quantite),0) AS quantite
    FROM produits p
    LEFT JOIN stock_depot sd ON sd.produit_id = p.id
    GROUP BY p.id
    HAVING quantite > 0
    ORDER BY p.nom
")->fetchAll(PDO::FETCH_ASSOC);

$categories = $conn->query("
    SELECT id, nom FROM categories ORDER BY nom
")->fetchAll(PDO::FETCH_ASSOC);

$clients = $conn->query("
    SELECT idClient, nom FROM clients
    WHERE statut='Actif'
    ORDER BY nom
")->fetchAll(PDO::FETCH_ASSOC);

$clientPosId = null;
foreach ($clients as $c) {
    if ($c['nom'] === 'Client POS') {
        $clientPosId = $c['idClient'];
        break;
    }
}
?>

<style>

:root {
    --pos-orange: #f57c00;      /* orange principal */
    --pos-orange-dark: #ef6c00;
    --pos-orange-light: #fff3e0;
}

/* ==========================
   OVERRIDE BOOTSTRAP - POS
========================== */

.pos-page .bg-danger {
    background-color: var(--pos-orange) !important;
}

.pos-page .btn-danger {
    background-color: var(--pos-orange) !important;
    border-color: var(--pos-orange) !important;
}

.pos-page .btn-danger:hover {
    background-color: var(--pos-orange-dark) !important;
    border-color: var(--pos-orange-dark) !important;
}

.pos-page .btn-outline-danger {
    color: var(--pos-orange) !important;
    border-color: var(--pos-orange) !important;
}

.pos-page .btn-outline-danger:hover,
.pos-page .btn-outline-danger.active {
    background-color: var(--pos-orange) !important;
    color: #fff !important;
}

.pos-page .text-danger {
    color: var(--pos-orange) !important;
}


.paiement-card {
    cursor: pointer;
    padding: 15px;
    border: 2px solid #eee;
    transition: 0.2s;
}
.paiement-card:hover {
    border-color: var(--pos-orange);
}

.paiement-card.active {
    border-color: var(--pos-orange);
    background: var(--pos-orange-light);
}

</style>


<div class="container-fluid pos-page">
    <h1 class="mt-4">Ventes</h1>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item">
            <a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a>
        </li>
        <li class="breadcrumb-item active">Ventes</li>
    </ol>
    <div class="alert alert-info d-flex justify-content-between">
        <div>
            <strong>Session #<?= $session_id ?></strong><br>
            Solde d’ouverture :
            <strong><?= number_format($soldeOuverture, 2, ',', ' ') ?> FCFA</strong><br>
            Solde journalier :
            <strong><?= number_format($soldeJournalier, 2, ',', ' ') ?> FCFA</strong>
        </div>
        <div>
            <a href="/eaglesuite/index.php?page=fermer_caisse"
               class="btn btn-outline-danger btn-sm"
               onclick="return confirm('Clôturer la caisse ?')">
                Fermer caisse
            </a>
        </div>
    </div>

    <!-- ROW PRINCIPALE -->
    <div class="row">

        <!-- PRODUITS -->
        <div class="col-md-8">
            
            <input id="searchProduit" class="form-control mb-3" placeholder="Rechercher un produit">

            <div class="mb-3 d-flex flex-wrap gap-2">
                <button class="btn btn-outline-dark btn-sm active"
                        onclick="filterCategorie('all', this)">
                    Tous
                </button>

                <?php foreach ($categories as $c): ?>
                    <button class="btn btn-outline-danger btn-sm"
                            onclick="filterCategorie(<?= $c['id'] ?>, this)">
                        <?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="row" id="produitsGrid">
                <?php foreach ($produits as $p): ?>
                   <div class="col-md-3 mb-3 produit-card"data-categorie="<?= (int)$p['categorie_id'] ?>">

                        <div class="card h-100 shadow-sm">

                            <?php if (!empty($p['image'])): ?>
                                <img src="/eaglesuite/public/uploads/produits/<?= htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($p['nom'], ENT_QUOTES, 'UTF-8') ?>"
                                     class="card-img-top"
                                     style="height:120px; object-fit:cover;">
                            <?php else: ?>
                                <div class="text-center py-4 bg-light">
                                    <small class="text-muted">Aucune image</small>
                                </div>
                            <?php endif; ?>

                            <div class="card-body text-center">
                                <h6><?= htmlspecialchars($p['nom']) ?></h6>

                                <small class="text-muted d-block">
                                    Stock : <?= (int)$p['quantite'] ?>
                                </small>

                                <strong>
                                    <?= number_format($p['prix_vente'], 0, '', ' ') ?> FCFA
                                </strong>

                                <button class="btn btn-danger btn-sm w-100 mt-2"
                                        onclick='addToCart(<?= json_encode($p) ?>)'>
                                    Ajouter
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PANIER -->
        <div class="col-md-4">
            <div class="card shadow position-sticky" style="top: 80px;">
                <div class="card-header bg-danger text-white">
                  Panier
                </div>

                <div class="card-body">
                    <label class="fw-bold mb-1">Client</label>
                        <select id="clientSelect" class="form-select mb-2">
                            <?php foreach ($clients as $c): ?>
                                <option value="<?= (int)$c['idClient'] ?>"
                                    <?= $c['idClient'] == $clientPosId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>


                    <div id="cartItems"></div>
                    <hr>

                    <input id="remise"
                        type="number"
                        class="form-control mb-2"
                        placeholder="Remise (FCFA)"
                        value="0">

                    <input id="montantVerse"
                        type="number"
                        class="form-control mb-2"
                        placeholder="Montant versé">

                    <h5>
                        Total : <strong><span id="totalPanier">0</span></strong> FCFA
                    </h5>

                    <h6 class="text-success">
                        Monnaie : <span id="monnaie">0</span> FCFA
                    </h6>

                    <button class="btn btn-primary w-100 mt-3"
                            onclick="openPaiementModal()">
                        PAYER
                    </button>

                </div>
            </div>
        </div>


    </div>
</div>

<div class="modal fade" id="paiementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Choisir le mode de paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row text-center g-3">

                    <div class="col-6">
                        <div class="card paiement-card"
                             onclick="selectPaiement('Wave', this)">
                            <img src="/eaglesuite/public/money/wave.png" height="40">
                            <p class="mt-2">Wave</p>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="card paiement-card"
                             onclick="selectPaiement('Orange Money', this)">
                            <img src="/eaglesuite/public/money/om.png" height="40">
                            <p class="mt-2">Orange Money</p>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="card paiement-card"
                             onclick="selectPaiement('Espèces', this)">
                            💵
                            <p class="mt-2">Espèces</p>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="card paiement-card"
                             onclick="selectPaiement('Carte', this)">
                            💳
                            <p class="mt-2">Carte Visa</p>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary w-100"
                        onclick="validerPaiement()">
                    Valider & Imprimer
                </button>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
const CLIENT_POS_ID = <?= (int)$clientPosId ?>;
</script>


<script src="/eaglesuite/public/js/pos.js"></script>

<script>
document.getElementById('searchProduit').addEventListener('keyup', function () {
    const value = this.value.toLowerCase().trim();

    document.querySelectorAll('.produit-card').forEach(card => {
        const text = card.innerText.toLowerCase();
        card.style.display = text.includes(value) ? '' : 'none';
    });
});
</script>

<script>
document.getElementById('filtreStock').addEventListener('change', function () {
    const value = this.value;

    document.querySelectorAll('.produit-card').forEach(card => {
        const stockText = card.querySelector('small')?.innerText || '';
        const stock = parseInt(stockText.replace(/\D/g, ''), 10);

        if (value === 'all') {
            card.style.display = '';
        } else if (value === 'dispo') {
            card.style.display = stock > 0 ? '' : 'none';
        } else {
            card.style.display = stock <= 0 ? '' : 'none';
        }
    });
});

function filterCategorie(catId, btn) {
    document.querySelectorAll('.produit-card').forEach(card => {
        if (catId === 'all' || card.dataset.categorie == catId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });

    document.querySelectorAll('.btn-outline-danger, .btn-outline-dark')
        .forEach(b => b.classList.remove('active'));

    btn.classList.add('active');
}

</script>



