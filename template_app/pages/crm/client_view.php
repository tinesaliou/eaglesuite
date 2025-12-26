<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';
// require_once __DIR__ . "/../../includes/check_auth.php";
// requirePermission('clients.view');

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    $action = $_GET['action'] ?? '';
    if ($action === 'add') {
        include __DIR__ . '/client_form.php';
        exit;
    }
    echo "<div class='alert alert-danger'>Client introuvable</div>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM clients WHERE idClient = ?");
$stmt->execute([$id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo "<div class='alert alert-danger'>Client introuvable</div>";
    exit;
}

$ventesSt = $conn->prepare("
    SELECT v.id, v.date_vente, v.totalTTC,v.montant_devise, v.reste_a_payer, d.symbole
    FROM ventes v
    JOIN devises d ON v.devise_id = d.id
    WHERE v.client_id = ?
    ORDER BY v.date_vente DESC
");
$ventesSt->execute([$id]);
$ventes = $ventesSt->fetchAll(PDO::FETCH_ASSOC);

$interSt = $conn->prepare("
    SELECT i.*, u.nom AS user_name
    FROM crm_interactions i 
    LEFT JOIN utilisateurs u ON i.utilisateur_id = u.id 
    WHERE i.client_id = ?
    ORDER BY i.date_interaction DESC 
");
$interSt->execute([$id]);
$interactions = $interSt->fetchAll(PDO::FETCH_ASSOC);

$oppSt = $conn->prepare("SELECT * FROM crm_opportunites WHERE client_id=? ORDER BY created_at DESC");
$oppSt->execute([$id]);
$opps = $oppSt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">

    <!-- Zone d'alertes -->
    <div id="alertBox"></div>

    <!-- ENTÊTE CLIENT -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2><?= htmlspecialchars($client['nom'], ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="text-muted">
                <?= htmlspecialchars($client['telephone'], ENT_QUOTES, 'UTF-8') ?> • <?= htmlspecialchars($client['email'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>

        <div>
            <a class="btn btn-outline-secondary" href="/{{TENANT_DIR}}/index.php?page=crm_clients">← Retour</a>
            <a class="btn btn-primary" href="/{{TENANT_DIR}}/index.php?page=crm_client&action=edit&id=<?= $client['idClient'] ?>">Modifier</a>
        </div>
    </div>

    <div class="row g-3">

        <!-- COLONNE GAUCHE -->
        <div class="col-lg-4">

            <!-- Infos -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6>Informations</h6>
                    <p><strong>Adresse :</strong><br><?= nl2br(htmlspecialchars($client['adresse'], ENT_QUOTES, 'UTF-8' ?? '-')) ?></p>
                    <p><strong>Type :</strong> <?= htmlspecialchars($client['type'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Statut :</strong> <?= htmlspecialchars($client['statut'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Origine :</strong> <?= htmlspecialchars($client['origine'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Secteur :</strong> <?= htmlspecialchars($client['secteur'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Score :</strong> <span class="badge bg-primary"><?= $client['score'] ?></span></p>
                </div>
            </div>

            <!-- Opportunités -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6>Opportunités</h6>

                    <button class="btn btn-success btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalAddOpportunity">
                        Nouvelle opportunité
                    </button>

                    <?php foreach ($opps as $o): ?>
                        <div class="p-2 border rounded mb-1 small">
                            <strong><?= htmlspecialchars($o['titre'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                            <small class="text-muted">
                                <?= htmlspecialchars($o['etat'], ENT_QUOTES, 'UTF-8') ?> •
                                <?= number_format($o['montant'], 0, ',', ' ') ?> FCFA
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- COLONNE DROITE -->
        <div class="col-lg-8">

            <!-- Timeline -->
            <div class="card mb-3">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6>Timeline / Interactions</h6>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddInteraction">
                            Ajouter interaction
                        </button>
                    </div>

                    <div id="timeline">
                        <?php foreach ($interactions as $it): ?>
                            <div class="d-flex mb-3">
                                <div class="me-3 text-muted" style="width: 120px;">
                                    <?= date('d/m/Y H:i', strtotime($it['date_interaction'])) ?><br>
                                    <small><?= htmlspecialchars($it['user_name'], ENT_QUOTES, 'UTF-8' ?? '-') ?></small>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="card p-2">
                                        <strong><?= htmlspecialchars($it['sujet'], ENT_QUOTES, 'UTF-8' ?: ucfirst($it['type'])) ?></strong>
                                        <div class="small text-muted"><?= htmlspecialchars($it['type'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($it['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

            <!-- Ventes -->
            <div class="card">
                <div class="card-body">
                    <h6>Ventes récentes</h6>

                    <table class="table table-sm">
                        <thead>
                        <tr><th>Date</th><th>Nº</th><th>Total</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ventes as $v): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($v['date_vente'])) ?></td>
                                <td><?= htmlspecialchars($v['id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= number_format($v['montant_devise'], 2, ',', ' ') ?> <?= $v['symbole'] ?></td>
                                <td>
                                    <?= ($v['reste_a_payer'] > 0)
                                        ? '<span class="badge bg-warning">Crédit</span>'
                                        : '<span class="badge bg-success">Payée</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>

    </div>
</div>

<?php include __DIR__ . "/modal_add_opportunity.php"; ?>



<div class="modal fade" id="modalAddInteraction">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="formAddInteraction">

                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle interaction</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="action" value="add_interaction">
                    <input type="hidden" name="client_id" value="<?= $client['idClient'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="note">Note</option>
                            <option value="appel">Appel</option>
                            <option value="email">Email</option>
                            <option value="rdv">RDV</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sujet</label>
                        <input type="text" name="sujet" class="form-control" placeholder="Sujet">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary">Ajouter</button>
                </div>

            </form>

        </div>
    </div>
</div>


<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<!-- JS AJAX -->
<script>
function showAlert(type, message) {
    $('#alertBox').html(`
        <div class="alert alert-${type} alert-dismissible fade show mt-2" role="alert">
            ${message}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
}

/* Ajouter Interaction */
$('#formAddInteraction').on('submit', function(e){
    e.preventDefault();
    $.post('/{{TENANT_DIR}}/pages/crm/actions.php', $(this).serialize(), function(r){
        if (r.success) {
            showAlert('success', 'Interaction ajoutée avec succès ✔');
            setTimeout(() => location.reload(), 800);
        } else {
            showAlert('danger', r.error || 'Erreur');
        }
    }, 'json');
});

/* Ajouter Opportunité */
$('#formAddOpportunity').on('submit', function(e){
    e.preventDefault();

    $.post('/{{TENANT_DIR}}/pages/crm/actions.php', $(this).serialize(), function(r){

        if (r.success) {
            $('#modalAddOpportunity').modal('hide');

            showAlert('success', 'Opportunité ajoutée avec succès !');

            setTimeout(() => location.reload(), 900);

        } else {
            showAlert('danger', r.error || 'Erreur lors de l’enregistrement');
        }

    }, 'json');
});

</script>
