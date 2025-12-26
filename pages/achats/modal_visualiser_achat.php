
<div class="modal fade" id="visualiserAchatModal<?= $achat['id'] ?>" tabindex="-1" aria-labelledby="visualiserAchatModalLabel<?= $achat['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="visualiserAchatModalLabel<?= $achat['id'] ?>">Détails de l' achat #<?= $achat['id'] ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>

      <div class="modal-body">
     <div class="row mb-3">
        <!-- Partie gauche -->
        <div class="col-md-6">
            <p><strong>Fournisseur :</strong> <?= $achat['fournisseur_nom'] ?? '---' ?></p>
            <p><strong>Date :</strong> <?= $achat['date_achat'] ?></p>
        </div>

        <!-- Partie droite -->
        <div class="col-md-6 text-end">
            <p><strong>Montant versé :</strong> <?= number_format($achat['montant_verse_devise'], 2, ',', ' ') ?> <?= $achat['symbole'] ?></p>
            <p><strong>Reste à payer :</strong> <?= number_format($achat['reste_a_payer_devise'], 2, ',', ' ') ?> <?= $achat['symbole'] ?></p>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Produit</th>
                <th>Dépôt</th>
                <th>Quantité</th>
                <th>PU</th>
                <th>Sous-total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['produit'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($d['depot'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $d['quantite'] ?></td>
                    <td><?= number_format($d['prix_devise'], 2, ',', ' ') ?> <?= $achat['symbole'] ?></td>
                    <td><?= number_format($d['quantite'] * $d['prix_devise'], 2, ',', ' ') ?> <?= $achat['symbole'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light">
            <tr>
                <th colspan="4" class="text-end">Total HT</th>
                <th><?= number_format($d['totalHT_devise'], 2, ',', ' ') ?> <?= $achat['symbole'] ?></th>
            </tr>
            <tr>
                <th colspan="4" class="text-end">Taxe</th>
                <th><?= number_format($achat['taxe_devise'], 2, ',', ' ') ?> <?= $achat['symbole'] ?></th>
            </tr>
            <tr>
                <th colspan="4" class="text-end">Remise</th>
                <th><?= number_format($achat['remise_devise'], 2, ',', ' ') ?> <?= $achat['symbole'] ?></th>
            </tr>
            <tr class="table-success">
                <th colspan="4" class="text-end">Total TTC</th>
                <th><?= number_format($achat['montant_devise'], 2, ',', ' ') ?> <?= $achat['symbole'] ?></th>
            </tr>
        </tfoot>
    </table>
</div>

      <!-- Footer -->
      <div class="modal-footer">
        <a href="/eaglesuite/api/achats/imprimer.php?id=<?= $achat['id'] ?>" target="_blank" class="btn btn-success btn-sm">
          <i class="fa fa-print"></i>
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>

    </div> 
  </div> 
</div> 
