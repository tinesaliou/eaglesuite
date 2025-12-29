<?php
require_once __DIR__ . "/../../config/db.php";
session_start();

$user_id = $_SESSION['user']['id'];

// Vérifier si déjà ouverte
$stmt = $conn->prepare("
    SELECT * FROM sessions_caisse
    WHERE utilisateur_id = ? AND statut = 'ouverte'
");
$stmt->execute([$user_id]);
$ouverte = $stmt->fetch();
?>

<div class="card">
  <div class="card-header bg-success text-white">
    Ouverture de caisse
  </div>

  <div class="card-body">

    <?php if ($ouverte): ?>
      <div class="alert alert-warning">
        Caisse déjà ouverte (ID <?= $ouverte['caisse_id'] ?>)
      </div>
    <?php else: ?>

      <form method="post" action="../ventes/ajax/ouvrir_caisse.php">
        <select name="caisse_id" class="form-select mb-3" required>
          <?php
          $caisses = $conn->query("SELECT * FROM caisses WHERE active = 1")->fetchAll();
          foreach ($caisses as $c):
          ?>
            <option value="<?= $c['id'] ?>">
              <?= $c['nom'] ?> (<?= $c['solde_actuel'] ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <button class="btn btn-success w-100">
          Ouvrir la caisse
        </button>
      </form>

    <?php endif; ?>
  </div>
</div>
