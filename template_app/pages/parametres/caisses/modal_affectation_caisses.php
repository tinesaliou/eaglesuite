<?php
$caisses = $conn->query("
    SELECT id, nom FROM caisses WHERE actif = 1
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal fade" id="modalAffectation">
  <div class="modal-dialog">
    <form method="POST" action="/save_affectation.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5>Affecter des caisses</h5>
      </div>

      <div class="modal-body">
        <input type="hidden" name="utilisateur_id" id="user_id">

        <p><strong id="user_nom"></strong></p>

        <?php foreach ($caisses as $c): ?>
          <div class="form-check">
            <input class="form-check-input caisse-check"
                   type="checkbox"
                   name="caisses[]"
                   value="<?= $c['id'] ?>">
            <label class="form-check-label">
              <?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?>
            </label>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="modal-footer">
        <button class="btn btn-success">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

<script>
$('.btnAffecter').on('click', function () {
  const id = $(this).data('id');
  $('#user_id').val(id);
  $('#user_nom').text($(this).data('nom'));

  $('.caisse-check').prop('checked', false);

  $.getJSON('ajax/get_user_caisses.php', {id}, function (data) {
    data.forEach(caisseId => {
      $('.caisse-check[value="'+caisseId+'"]').prop('checked', true);
    });
  });
});
</script>
