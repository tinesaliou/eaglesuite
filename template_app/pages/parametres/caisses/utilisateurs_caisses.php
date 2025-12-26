<?php
require_once __DIR__ . "/../../../config/db.php";

$users = $conn->query("
    SELECT id, nom, email, actif
    FROM utilisateurs
    ORDER BY nom
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card mb-4">
  <div class="card-header">
    <i class="fa fa-users"></i> Affectation utilisateurs ↔ caisses
  </div>

  <div class="card-body">
    <table class="table table-bordered datatable">
      <thead class="table-dark">
        <tr>
          <th>Utilisateur</th>
          <th>Email</th>
          <th>Statut</th>
          <th>Caisses</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= $u['actif'] ? 'Actif' : 'Inactif' ?></td>
          <td>
            <button class="btn btn-sm btn-primary btnAffecter"
                    data-id="<?= $u['id'] ?>"
                    data-nom="<?= htmlspecialchars($u['nom'], ENT_QUOTES, 'UTF-8') ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#modalAffectation">
              Gérer caisses
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include "modal_affectation_caisses.php"; ?>

<script>
$('.datatable').DataTable({
  responsive:true,
  language:{ url:'/{TENANT_DIR}}/public/js/fr-FR.json' }
});
</script>
