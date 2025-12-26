<?php
require_once __DIR__ . "/../../config/db.php";

$caisses = $conn->query("SELECT id, type, nom, solde_actuel FROM caisses ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
  <h1 class="mt-4">Autres opérations</h1>
  <ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/eaglesuite/index.php?page=dashboard">Tableau de bord</a></li>
    <li class="breadcrumb-item active">Autres opérations</li>
  </ol>

  <div class="card mb-4">
    <div class="card-header">
      <i class="fa fa-plus-circle"></i> Ajouter une opération
    </div>
    <div class="card-body">
      <form method="post" action="/eaglesuite/api/caisse/save_autre_operation.php">
        <div class="row mb-3">
          <div class="col-md-4">
            <label>Caisse</label>
            <select name="caisse_id" class="form-select" required>
              <?php foreach($caisses as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label>Type</label>
            <select name="type" class="form-select" required>
              <option value="entree">Recette</option>
              <option value="sortie">Dépense</option>
            </select>
          </div>
          <div class="col-md-4">
            <label>Catégorie</label>
            <select name="categorie" class="form-select" required>
              <option value="Charges fixes">Charges fixes</option>
              <option value="Charges variables">Charges variables</option>
              <option value="Autre entrée">Autre entrée</option>
            </select>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <label>Montant</label>
            <input type="number" step="0.01" name="montant" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label>Commentaire</label>
            <input type="text" name="commentaire" class="form-control">
          </div>
        </div>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>