<!-- modal_manage_stages.php -->
<div class="modal fade" id="modalManageStages" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Gérer les étapes du pipeline</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <form id="formAddStage" class="row g-2">
            <input type="hidden" name="action" value="add_stage">
            <div class="col-md-4"><input name="nom" placeholder="Nom étape" class="form-control" required></div>
            <div class="col-md-4"><input name="slug" placeholder="slug (ex: qualified)" class="form-control" required></div>
            <div class="col-md-2">
              <select name="couleur" class="form-select">
                <option value="secondary">Grey</option>
                <option value="info">Info</option>
                <option value="warning">Warning</option>
                <option value="success">Success</option>
                <option value="danger">Danger</option>
                <option value="primary">Primary</option>
              </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary">Ajouter</button></div>
          </form>
        </div>

        <div>
          <p class="small text-muted">Glisser-déposer pour réordonner les étapes :</p>
          <ul id="stagesList" class="list-group">
            <?php
              $all = $conn->query("SELECT * FROM crm_stages ORDER BY position ASC")->fetchAll(PDO::FETCH_ASSOC);
              foreach($all as $s) {
                echo "<li class=\"list-group-item d-flex justify-content-between align-items-center\" data-id=\"{$s['id']}\">
                        <span><i class=\"fa fa-grip-vertical me-2\"></i> ".htmlspecialchars($s['nom'], ENT_QUOTES, 'UTF-8')."</span>
                        <span>
                          <button class=\"btn btn-sm btn-outline-secondary btnEditStage\" data-id=\"{$s['id']}\">✎</button>
                          <button class=\"btn btn-sm btn-outline-danger btnDeleteStage\" data-id=\"{$s['id']}\">🗑</button>
                        </span>
                      </li>";
              }
            ?>
          </ul>
        </div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        <button class="btn btn-primary" id="saveStageOrder">Enregistrer l'ordre</button>
      </div>
    </div>
  </div>
</div>
