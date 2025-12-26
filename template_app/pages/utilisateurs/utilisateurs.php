<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . '/../../config/check_access.php';

require_once __DIR__ . "/../../includes/check_auth.php";
requirePermission("users.manage");

echo "<h4>Permissions de votre rôle :</h4>";

echo "<div class='row'>";
foreach ($_SESSION['permissions'] as $perm => $val) {
    echo "<div class='col-3'>
            <span class='badge bg-success'>✔ $perm</span>
          </div>";
}
echo "</div><hr>";

echo "<h5>Test permissions :</h5>";

$allPerms = [
    'dashboard.view','produits.view','categories.view','depots.view','ventes.view','achats.view',
    'mouvements.view','inventaire.view','tresorerie.view','caisse.especes','caisse.banque','caisse.mobile',
    'operations.view','clients.view','fournisseurs.view','creances.view','dettes.view',
    'users.manage','roles.manage','settings.view'
];

echo "<div class='row'>";
foreach ($allPerms as $p) {
    $ok = checkPermission($p);
    echo "<div class='col-3'>
            <span class='badge ".($ok?'bg-success':'bg-danger')."'>".($ok?'✔ ':'✖ ')."$p</span>
          </div>";
}
echo "</div>";

// Charger tous les utilisateurs
$stmt = $conn->query("SELECT utilisateurs.*, roles.id,(roles.nom) AS role  
FROM utilisateurs  INNER JOIN roles ON utilisateurs.role_id = roles.id ORDER BY utilisateurs.id DESC");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="fa fa-users"></i> Liste des utilisateurs</span>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouterUtilisateur">
      <i class="fa fa-plus"></i> Ajouter
    </button>
  </div>
  <div class="card-body">
    <table id="tableUtilisateurs" class="table table-bordered table-striped datatable">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nom</th>
          <th>Email</th>
          <th>Role</th>
          <th>Actif</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($utilisateurs as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?></td>
          <td>
            <?php if ($u['actif']): ?>
              <span class="badge bg-success">Actif</span>
            <?php else: ?>
              <span class="badge bg-danger">Inactif</span>
            <?php endif; ?>
          </td>
          <td>
            <!-- Bouton Modifier -->
            <button 
              class="btn btn-sm btn-warning btnEditUtilisateur"
              data-id="<?= $u['id'] ?>"
              data-nom="<?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?>"
              data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>"
              data-entreprise="<?= $u['entreprise_id'] ?>"
              data-actif="<?= $u['actif'] ?>"
              data-bs-toggle="modal"
              data-bs-target="#modalModifierUtilisateur"
            >
              <i class="fa fa-edit"></i>
            </button>

            <!-- Bouton Supprimer -->
            <button 
              class="btn btn-danger btn-sm btnSupprimerUtilisateur"
              data-id="<?= $u['id'] ?>"
              data-nom="<?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?>"
              data-bs-toggle="modal"
              data-bs-target="#modalDeleteUtilisateur">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . "/modal_ajouter_utilisateur.php"; ?>
<?php include __DIR__ . "/modal_modifier_utilisateur.php"; ?>
<?php include __DIR__ . "/modal_supprimer_utilisateur.php"; ?>

<?php include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(document).ready(function() {
  $('#tableUtilisateurs').DataTable({
    responsive: true,
    language: { url: "/{{TENANT_DIR}}/public/js/fr-FR.json" }
  });
});

// Remplir modal Supprimer
$(document).on("click", ".btnSupprimerUtilisateur", function () {
    $("#supprimer_id").val($(this).data("id"));
    $("#supprimer_nom").text($(this).data("nom"));
});

// Remplir modal Modifier
$(document).on("click", ".btnEditUtilisateur", function () {
    $("#edit_id").val($(this).data("id"));
    $("#edit_nom").val($(this).data("nom"));
    $("#edit_email").val($(this).data("email"));
    $("#edit_entreprise").val($(this).data("entreprise"));
    $("#edit_actif").val($(this).data("actif"));
});
</script>
