<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';
//require_once __DIR__ . "/../../includes/check_auth.php";
//requirePermission('crm.opportunites.view');

// stages dynamiques
$stages = $conn->query("SELECT * FROM crm_stages WHERE active=1 ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);

// opportunités
$opps = $conn->query("
  SELECT o.*, c.nom AS client_nom
  FROM crm_opportunites o
  LEFT JOIN clients c ON c.idClient = o.client_id
  ORDER BY o.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container-fluid px-2">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4">Pipeline des opportunités</h1>
    <div>
      <?php if (checkPermission('crm.opportunites.manage')): ?>
        <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#modalManageStages">
          <i class="fa fa-list"></i> Gérer étapes
        </button>
      <?php endif; ?>
      <button class="btn btn-primary btn-sm mb-2" data-bs-toggle="modal" data-bs-target="#modalAddOpportunity">
             Nouvelle opportunité
      </button>
    </div>
  </div>

  <div class="row g-3" id="pipelineBoard">
    <?php foreach ($stages as $stage): 
      $items = array_filter($opps, fn($o) => $o['etat'] === $stage['slug']);
    ?>
      <div class="col-md-<?php echo max(2, intval(12 / max(1, count($stages)))); ?> col-12">
        <div class="card h-100 shadow-sm border-top border-3 border-<?php echo htmlspecialchars($stage['couleur'], ENT_QUOTES, 'UTF-8'); ?>">
          <div class="card-header d-flex justify-content-between align-items-center bg-light text-<?php echo htmlspecialchars($stage['couleur'], ENT_QUOTES, 'UTF-8'); ?>">
            <strong><?php echo htmlspecialchars($stage['nom'], ENT_QUOTES, 'UTF-8'); ?></strong>
            <span class="badge bg-<?php echo htmlspecialchars($stage['couleur'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo count($items); ?></span>
          </div>

          <div class="card-body p-2 opp-column" data-state="<?php echo htmlspecialchars($stage['slug'], ENT_QUOTES, 'UTF-8'); ?>" style="max-height:75vh; overflow-y:auto;">
            <?php if (empty($items)): ?>
              <div class="text-muted small text-center py-2">Aucune opportunité</div>
            <?php endif; ?>

            <?php foreach ($items as $o): ?>
            <div class="card shadow-sm mb-2 opp-card" draggable="true" data-id="<?= intval($o['id']); ?>">
              <div class="card-body p-2">

                <strong><?= htmlspecialchars($o['titre'], ENT_QUOTES, 'UTF-8'); ?></strong>

                <div class="small text-muted">
                  <?= htmlspecialchars($o['client_nom'], ENT_QUOTES, 'UTF-8'); ?> · <?= number_format($o['montant'], 0, ',', ' '); ?> FCFA
                </div>

                <div class="mt-2 text-end">

                  <button 
                    class="btn btn-sm btn-primary rounded-circle btnEditOpp"
                    style="width:32px;height:32px;padding:0;"
                    data-id="<?= intval($o['id']); ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditOpportunity"
                  >
                    <i class="fa fa-pencil-alt"></i>
                  </button>

                  <!-- Bouton SUPPRIMER (cercle, icône poubelle) -->
                <button 
                  class="btn btn-sm btn-danger rounded-circle btnDeleteOpp"
                  style="width:32px;height:32px;padding:0;"
                  data-id="<?= intval($o['id']); ?>"
                  data-nom="<?= htmlspecialchars($o['titre'], ENT_QUOTES, 'UTF-8'); ?>"
                  data-bs-toggle="modal"
                  data-bs-target="#modalDeleteOpportunity"
              >
                  <i class="fa fa-trash"></i>
              </button>

                </div>

              </div>
            </div>
          <?php endforeach; ?>


          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<link rel="stylesheet" href="/{{TENANT_DIR}}/public/css/crm_opps.css">

<?php include __DIR__ . "/modal_add_opportunity.php"; ?>
<?php include __DIR__ . "/modal_edit_opportunity.php"; ?>
<?php include __DIR__ . "/modal_delete_opportunity.php"; ?>
<?php include __DIR__ . "/modal_manage_stages.php"; ?>



<script src="/{{TENANT_DIR}}/public/js/crm_opps.js"></script>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalDeleteOpportunity");
    if (!modal) {
        console.warn("Modal #modalDeleteOpportunity introuvable au moment de l'exécution.");
        return;
    }

    modal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        if (!button) return;

        document.getElementById("deleteOppId").value = button.dataset.id;
        document.getElementById("delete-opp-name").textContent = button.dataset.nom;
    });
});
</script>
