<?php
// pages/crm/tasks.php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/check_auth.php";
requirePermission('clients.view');

$tasks = $conn->query("SELECT t.*, c.nom AS client_nom, u.nom AS user_name FROM crm_tasks t LEFT JOIN clients c ON t.client_id=c.idClient LEFT JOIN utilisateurs u ON t.utilisateur_id=u.id ORDER BY t.date_echeance IS NULL, t.date_echeance ASC")->fetchAll(PDO::FETCH_ASSOC);
include __DIR__ . "/../../includes/layout.php";
?>

<div class="container-fluid">
  <h1 class="h4 mb-3">Tâches CRM</h1>
  <a class="btn btn-sm btn-primary mb-2" id="btnNewTask">Nouvelle tâche</a>
  <table id="tasksTable" class="table table-striped">
    <thead class="table-dark"><tr><th>ID</th><th>Titre</th><th>Client</th><th>Échéance</th><th>Statut</th><th>Assigné</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach($tasks as $t): ?>
        <tr>
          <td><?= $t['id'] ?></td>
          <td><?= htmlspecialchars($t['titre'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($t['client_nom'], ENT_QUOTES, 'UTF-8' ?? '-') ?></td>
          <td><?= $t['date_echeance'] ?></td>
          <td><?= htmlspecialchars($t['statut'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($t['user_name'], ENT_QUOTES, 'UTF-8' ?? '-') ?></td>
          <td>
            <button class="btn btn-sm btn-success btn-complete" data-id="<?= $t['id'] ?>">✔</button>
            <button class="btn btn-sm btn-danger btn-delete-task" data-id="<?= $t['id'] ?>">✖</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . "/modal_templates.php"; include __DIR__ . "/../../includes/layout_end.php"; ?>

<script>
$(function(){
  $('#tasksTable').DataTable({ responsive:true, language:{ url: "/eaglesuite/public/js/fr-FR.json" } });
  $('#btnNewTask').on('click', function(){
    // simple prompt demo; remplacer par modal
    var titre = prompt('Titre tâche');
    if(!titre) return;
    $.post('/eaglesuite/pages/crm/actions.php', { action:'add_task', titre: titre }, function(r){
      if(r.success) location.reload(); else alert(r.error || 'Erreur');
    }, 'json');
  });

  $('.btn-delete-task').on('click', function(){
    if(!confirm('Supprimer ?')) return;
    var id = $(this).data('id');
    $.post('/eaglesuite/pages/crm/actions.php', { action:'delete_task', id: id }, function(r){
      if(r.success) location.reload(); else alert(r.error || 'Erreur');
    }, 'json');
  });

  $('.btn-complete').on('click', function(){
    var id = $(this).data('id');
    $.post('/eaglesuite/pages/crm/actions.php', { action:'complete_task', id: id }, function(r){
      if(r.success) location.reload(); else alert(r.error || 'Erreur');
    }, 'json');
  });
});
</script>
