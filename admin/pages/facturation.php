<?php
require_once __DIR__ . '/../init.php';
require_admin();

$filter_status = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

// requête de base
$sql = "SELECT f.*, c.societe, c.subdomain,c.telephone
        FROM saas_factures f
        JOIN clients_saas c ON c.id = f.client_id
       ";
$where = [];
$params = [];

if ($filter_status) {
    $where[] = "f.statut = ?";
    $params[] = $filter_status;
}
if ($search !== '') {
    $where[] = "(c.societe LIKE ? OR c.subdomain LIKE ? OR f.reference LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($where) $sql .= " WHERE " . implode(" AND ", $where);

$sql .= " ORDER BY f.date_emission DESC";

$stmt = $masterPdo->prepare($sql);
$stmt->execute($params);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer : dictionnaire JS des numéros de téléphone par facture
$phones = [];
foreach ($factures as $f) {
    $tel = preg_replace('/\D+/', '', $f['telephone'] ?? '');
    
    // Format Sénégal → si 9 chiffres, alors ajouter "221"
    if ($tel && strlen($tel) === 9) {
        $tel = "221" . $tel;
    }

    $phones[$f['id']] = $tel;
}
?>
<script>
const FACTURE_PHONES = <?= json_encode($phones) ?>;
</script>
<?php
 
//include __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-2 mt-4">
    <div class="container-fluid px-2">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/eaglesuite/admin/index.php?page=dashboard">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Facturation</li>
    </ol>
  <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h1 class="h4">Facturation SaaS</h1>
    <div class="d-flex gap-2">
      <form class="d-flex" method="GET">
        <input type="hidden" name="page" value="facturation">
        <input name="q" value="<?= htmlspecialchars($search) ?>" class="form-control form-control-sm" placeholder="Rechercher client, référence..." />
        <select name="status" class="form-select form-select-sm ms-2">
          <option value="">-- Statut --</option>
          <option value="impayé" <?= $filter_status==='impayé' ? 'selected' : '' ?>>Impayé</option>
          <option value="en_attente" <?= $filter_status==='en_attente' ? 'selected' : '' ?>>En attente</option>
          <option value="payé" <?= $filter_status==='payé' ? 'selected' : '' ?>>Payé</option>
        </select>
        <button class="btn btn-primary btn-sm ms-2">Filtrer</button>
      </form>
      <a href="/eaglesuite/admin/actions.php?action=generate_invoices_now" class="btn btn-outline-secondary btn-sm">Générer maintenant</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <table class="table table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Client</th>
            <th>Réf</th>
            <th>Montant</th>
            <th>Émise</th>
            <th>Échéance</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($factures as $f): ?>
            <tr>
              <td><?= $f['id'] ?></td>
              <td>
                <strong><?= htmlspecialchars($f['societe']) ?></strong><br>
                <small class="text-muted"><?= htmlspecialchars($f['subdomain']) ?></small>
              </td>
              <td><?= htmlspecialchars($f['reference']) ?></td>
              <td><?= number_format($f['montant'],0,',',' ') ?> F CFA</td>
              <td><?= (new DateTime($f['date_emission']))->format('Y-m-d') ?></td>
              <td><?= $f['date_echeance'] ?? '-' ?></td>
              <td>
                <?php if ($f['statut']=='payé'): ?>
                  <span class="badge bg-success">Payé</span>
                <?php elseif ($f['statut']=='en_attente'): ?>
                  <span class="badge bg-warning text-dark">En attente</span>
                <?php else: ?>
                  <span class="badge bg-danger">Impayé</span>
                <?php endif; ?>
              </td>
              <td class="text-nowrap">
                <a class="btn btn-sm btn-outline-primary action-btn" title="Télécharger PDF"
                    href="/eaglesuite/admin/actions.php?action=facture_pdf&id=<?= $f['id'] ?>" target="_blank">
                    <i class="bi bi-file-earmark-pdf"></i>
                </a>
                <button class="btn btn-success btn-sm rounded-circle ms-1 btnPay" data-id="<?= $f['id'] ?>" data-montant="<?= $f['montant'] ?>" data-bs-toggle="modal" data-bs-target="#modalPay">
                 <i class="bi bi-cash-stack"></i>
                </button>

                 <button class="btn btn-sm btn-success action-btn btnPayNow"
                        data-id="<?= $f['id'] ?>"
                        data-client="<?= htmlspecialchars($f['societe'], ENT_QUOTES) ?>"
                        data-amount="<?= $f['montant'] ?>"
                        data-sub="<?= htmlspecialchars($f['subdomain'], ENT_QUOTES) ?>"
                        data-phone="<?= htmlspecialchars($f['telephone'], ENT_QUOTES) ?>"
                        title="Payer via WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                 </button>


                <form method="POST" action="/eaglesuite/admin/actions.php" class="d-inline">
                    <input type="hidden" name="action" value="mark_facture_paid">
                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
                    <button class="btn btn-sm btn-outline-success action-btn" title="Marquer payé">
                        <i class="bi bi-check"></i>
                    </button>
                </form>

                <a href="/eaglesuite/admin/actions.php?action=send_invoice&id=<?= $f['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-circle ms-1" title="Envoyer email">
                <i class="bi bi-envelope"></i>
                </a>

                </td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../modals/modal_pay.php'; ?>
<!-- PayNow modal (reusable) -->
<div class="modal fade" id="modalPayNow" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Payer via WhatsApp</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="payNowInfo"></p>
        <div class="d-flex justify-content-end gap-2">
          <a id="whatsLink" class="btn btn-success" target="_blank"><i class="fa fa-whatsapp me-1"></i> Ouvrir WhatsApp</a>
          <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.btnPayNow').forEach(btn => {
  btn.addEventListener('click', () => {

    const id      = btn.dataset.id;
    const client  = btn.dataset.client;
    const amount  = btn.dataset.amount;

    // Récupération du vrai numéro du client
    const phone = FACTURE_PHONES[id];

    if (!phone) {
        alert("Ce client n’a pas de numéro WhatsApp enregistré.");
        return;
    }

    const msg = encodeURIComponent(
      `Bonjour ${client},\n\nJe souhaite régler ma facture #${id} d'un montant de ${parseFloat(amount).toFixed(0)} FCFA.\nMerci de m’envoyer les instructions ou le lien de paiement.\n`
    );

    const waUrl = `https://wa.me/${phone}?text=${msg}`;

    document.getElementById('payNowInfo').textContent =
      `Vous allez ouvrir WhatsApp pour contacter ${client} et effectuer le paiement.`;

    document.getElementById('whatsLink').setAttribute('href', waUrl);

    new bootstrap.Modal(document.getElementById('modalPayNow')).show();
  });
});
</script>


<?php include __DIR__ . '/../includes/footer.php'; ?>
