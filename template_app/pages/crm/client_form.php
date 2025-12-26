<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';

//require_once __DIR__ . "/../../includes/check_auth.php";
//requirePermission('crm.clients.view');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<div class='alert alert-danger'>Client introuvable.</div>";
    exit;
}

// Infos client
$stmt = $conn->prepare("SELECT * FROM clients WHERE idClient=?");
$stmt->execute([$id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    echo "<div class='alert alert-danger'>Client introuvable.</div>";
    exit;
}

// Opportunités du client
$opps = $conn->prepare("
    SELECT * FROM crm_opportunites 
    WHERE client_id=? ORDER BY created_at DESC
");
$opps->execute([$id]);
$opps = $opps->fetchAll(PDO::FETCH_ASSOC);

// Interactions
$inter = $conn->prepare("
    SELECT * FROM crm_interactions 
    WHERE client_id=? ORDER BY date_interaction DESC
");
$inter->execute([$id]);
$inter = $inter->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-2">
    
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fa fa-user"></i> <?= htmlspecialchars($client['nom'], ENT_QUOTES, 'UTF-8') ?></h2>

        <div>
            <a href="/{{TENANT_DIR}}/index.php?page=crm_clients" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Retour
            </a>

            <a href="/{{TENANT_DIR}}/index.php?page=crm_client&action=edit&id=<?= $id ?>" 
               class="btn btn-primary btn-sm">
                <i class="fa fa-edit"></i> Modifier
            </a>
        </div>
    </div>

    <!-- INFO CLIENT -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <strong>Informations du client</strong>
        </div>
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="text-muted">Nom</label>
                    <div class="fw-bold"><?= htmlspecialchars($client['nom'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div class="col-md-4">
                    <label class="text-muted">Téléphone</label>
                    <div><?= htmlspecialchars($client['telephone'], ENT_QUOTES, 'UTF-8' ?: '—') ?></div>
                </div>

                <div class="col-md-4">
                    <label class="text-muted">Email</label>
                    <div><?= htmlspecialchars($client['email'], ENT_QUOTES, 'UTF-8' ?: '—') ?></div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="text-muted">Type</label>
                    <div class="badge bg-primary"><?= $client['type'] ?></div>
                </div>

                <div class="col-md-3">
                    <label class="text-muted">Statut</label>
                    <div class="badge bg-success"><?= $client['statut'] ?></div>
                </div>

                <div class="col-md-3">
                    <label class="text-muted">Origine</label>
                    <div><?= $client['origine'] ?: '—' ?></div>
                </div>

                <div class="col-md-3">
                    <label class="text-muted">Score</label>
                    <div class="fw-bold"><?= $client['score'] ?></div>
                </div>
            </div>

            <div>
                <label class="text-muted">Adresse</label>
                <div><?= nl2br(htmlspecialchars($client['adresse'], ENT_QUOTES, 'UTF-8' ?: '—')) ?></div>
            </div>

        </div>
    </div>


    <!-- TABS -->
    <ul class="nav nav-tabs mb-3" id="crmTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#oppsTab">Opportunités</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#interTab">Interactions</button>
        </li>
    </ul>


    <div class="tab-content">

        <!-- TAB OPPORTUNITES -->
        <div class="tab-pane fade show active" id="oppsTab">

            <div class="d-flex justify-content-between mb-2">
                <h5>Opportunités</h5>
                <a class="btn btn-success btn-sm" href="/{{TENANT_DIR}}/index.php?page=crm_opportunites&action=add&client_id=<?= $id ?>">
                    <i class="fa fa-plus"></i> Nouvelle opportunité
                </a>
            </div>

            <?php if (!$opps): ?>
                <div class="alert alert-info">Aucune opportunité.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                        <tr>
                            <th>Titre</th>
                            <th>Montant</th>
                            <th>Probabilité</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($opps as $o): ?>
                            <tr>
                                <td><?= htmlspecialchars($o['titre']) ?></td>
                                <td><?= number_format($o['montant'], 0, ',', ' ') ?> FCFA</td>
                                <td><?= $o['probabilite'] ?> %</td>
                                <td><span class="badge bg-info"><?= $o['etat'] ?></span></td>
                                <td><?= substr($o['created_at'], 0, 10) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>


        <!-- TAB INTERACTIONS -->
        <div class="tab-pane fade" id="interTab">

            <div class="d-flex justify-content-between mb-2">
                <h5>Interactions</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalInteraction">
                    <i class="fa fa-plus"></i> Nouvelle interaction
                </button>
            </div>

            <?php if (!$inter): ?>
                <div class="alert alert-info">Aucune interaction enregistrée.</div>
            <?php else: ?>

                <ul class="list-group">
                    <?php foreach ($inter as $i): ?>
                        <li class="list-group-item">
                            <strong><?= htmlspecialchars($i['sujet'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                            <span class="text-muted">
                                <?= substr($i['date_interaction'], 0, 16) ?>
                            </span>
                            <div><?= nl2br(htmlspecialchars($i['description'], ENT_QUOTES, 'UTF-8')) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>

            <?php endif; ?>

        </div>

    </div>

</div>


<!-- MODAL AJOUT INTERACTION -->
<div class="modal fade" id="modalInteraction">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="post" action="/{{TENANT_DIR}}/pages/crm/interaction_add.php">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle interaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="client_id" value="<?= $id ?>">

                    <div class="mb-3">
                        <label>Sujet</label>
                        <input type="text" name="sujet" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Date interaction</label>
                        <input type="datetime-local" name="date_interaction" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>

        </div>
    </div>
</div>
