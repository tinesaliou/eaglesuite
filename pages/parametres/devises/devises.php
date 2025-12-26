<?php
require_once __DIR__ . "/../../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["maj_auto"])) {
    try {
        // 🌍 API publique sans clé
        $apiUrl = "https://open.er-api.com/v6/latest/XOF";
        $json = @file_get_contents($apiUrl);

        if ($json === false) {
            throw new Exception("Impossible d'accéder à l'API de taux de change.");
        }

        $data = json_decode($json, true);

        if (!isset($data["rates"]) || $data["result"] !== "success") {
            throw new Exception("Réponse invalide reçue depuis l'API.");
        }

        $updated = 0;
        $notFound = [];

        foreach ($data["rates"] as $code => $taux) {
            // ⚙️ Correction : on inverse pour exprimer le taux en XOF
            if ($taux != 0) {
                $taux_xof = 1 / $taux;
            } else {
                $taux_xof = 0;
            }

            // ⚖️ Cas spécial : taux fixe EUR <-> XOF (source BCEAO)
            if ($code === "EUR") {
                $taux_xof = 655.957;
            }
            if ($code === "XOF") {
                $taux_xof = 1;
            }

            $stmt = $conn->prepare("UPDATE devises SET taux_par_defaut = ?, date_mise_a_jour = NOW() WHERE code = ?");
            $stmt->execute([$taux_xof, $code]);

            if ($stmt->rowCount() > 0) {
                $updated++;
            } else {
                $notFound[] = $code;
            }
        }

        // ✅ Résumé
        echo "<div class='alert alert-success'>✅ $updated taux mis à jour avec succès (base XOF).</div>";

        // ⚠️ Devise(s) non trouvée(s)
        if (!empty($notFound)) {
            echo "<div class='alert alert-warning'>⚠️ Ces devises n'existent pas dans la base : " . implode(', ', $notFound) . "</div>";
        }

    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Charger toutes les devises
$stmt = $conn->query("SELECT * FROM devises ORDER BY id DESC");
$devises = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fa fa-tags"></i> Liste des devises</span>

    <div class="d-flex gap-2">
      <form method="POST" style="display:inline;">
        <button type="submit" name="maj_auto" class="btn btn-outline-primary btn-sm">
          🔄 Mettre à jour les taux
        </button>
      </form>

      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterDevise">
        <i class="fa fa-plus"></i> Ajouter
      </button>
    </div>
  </div>

  <div class="card-body">
    <table id="tableDevises" class="table table-bordered table-striped datatable">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Code</th>
          <th>Nom</th>
          <th>Symbole</th>
          <th>Taux (par rapport à XOF)</th>
          <th>Date mise à jour</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($devises as $dev): ?>
        <tr>
          <td><?= $dev['id'] ?></td>
          <td><?= htmlspecialchars($dev['code'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($dev['nom'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($dev['symbole'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= number_format($dev['taux_par_defaut'], 3, ',', ' ') ?></td>
          <td><?= htmlspecialchars($dev['date_mise_a_jour'], ENT_QUOTES, 'UTF-8') ?></td>
          <td>
            <button 
              class="btn btn-sm btn-warning btnEditDevise"
              data-id="<?= $dev['id'] ?>"
              data-code="<?= htmlspecialchars($dev['code'], ENT_QUOTES, 'UTF-8') ?>"
              data-nom="<?= htmlspecialchars($dev['nom'], ENT_QUOTES, 'UTF-8') ?>"
              data-symbole="<?= htmlspecialchars($dev['symbole'], ENT_QUOTES, 'UTF-8') ?>"
              data-taux="<?= htmlspecialchars($dev['taux_par_defaut'], ENT_QUOTES, 'UTF-8') ?>"
              data-bs-toggle="modal"
              data-bs-target="#modalModifierDevise"
            >
              <i class="fa fa-edit"></i>
            </button>

            <button 
              class="btn btn-danger btn-sm btnSupprimerDevise"
              data-id="<?= $dev['id'] ?>"
              data-nom="<?= htmlspecialchars($dev['nom'], ENT_QUOTES, 'UTF-8') ?>"
              data-bs-toggle="modal"
              data-bs-target="#modalDeleteDevise">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . "/modal_ajouter_devise.php"; ?>
<?php include __DIR__ . "/modal_modifier_devise.php"; ?>
<?php include __DIR__ . "/modal_supprimer_devise.php"; ?>

<script>
$(document).ready(function() {
  $('#tableDevises').DataTable({
    responsive: true,
    language: { url: "/eaglesuite/public/js/fr-FR.json" }
  });
});
</script>
