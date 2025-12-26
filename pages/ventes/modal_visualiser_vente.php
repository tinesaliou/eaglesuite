


<div class="modal fade" id="visualiserVenteModal<?= $vente['id'] ?>" tabindex="-1" aria-labelledby="visualiserVenteModalLabel<?= $vente['id'] ?>" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

       <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="visualiserVenteModalLabel<?= $vente['id'] ?>">Détails de la vente #<?= $vente['id'] ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>

      <div class="modal-body">

         <div class="row mb-3">
        <!-- Partie gauche -->
        <div class="col-md-6">
            <p><strong>Client :</strong> <?= $vente['client_nom'] ?? '---' ?></p>
            <p><strong>Date :</strong> <?= $vente['date_vente'] ?></p>
        </div>

        <!-- Partie droite -->
        <div class="col-md-6 text-end">
            <p><strong>Montant versé :</strong> <?= number_format($vente['montant_verse_devise'], 2, ',', ' ') ?> <?= $vente['symbole'] ?></p>
            <p><strong>Reste à payer :</strong> <?= number_format($vente['reste_a_payer_devise'], 2, ',', ' ') ?> <?= $vente['symbole'] ?></p>
        </div>
    </div>
       

        <table class="table table-bordered">
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
                <td><?= $d['produit'] ?></td>
                <td><?= $d['depot'] ?></td>
                <td><?= $d['quantite'] ?></td>
                <td><?= number_format($d['prix_devise'], 2, ',', ' ') ?> <?= $vente['symbole'] ?></td>
                <td><?= number_format($d['quantite'] * $d['prix_devise'], 2, ',', ' ') ?> <?= $vente['symbole'] ?></td>
              </tr>
            <?php endforeach; ?>
            <tfoot class="table-light">
            <tr>
                <th colspan="4" class="text-end">Total HT</th>
                <th><?= number_format($d['totalHT_devise'], 2, ',', ' ') ?> <?= $vente['symbole'] ?></th>
            </tr>
            <tr>
                <th colspan="4" class="text-end">Taxe</th>
                <th><?= number_format($vente['taxe_devise'], 2, ',', ' ') ?> <?= $vente['symbole'] ?></th>
            </tr>
            <tr>
                <th colspan="4" class="text-end">Remise</th>
                <th><?= number_format($vente['remise_devise'], 2, ',', ' ') ?> <?= $vente['symbole'] ?></th>
            </tr>
            <tr class="table-success">
                <th colspan="4" class="text-end">Total TTC</th>
                <th><?= number_format($vente['montant_devise'], 2, ',', ' ') ?> <?= $vente['symbole'] ?></th>
            </tr>
        </tfoot>
          </tbody>   
        </table>
        
      </div>

      <!-- Footer -->
      <div class="modal-footer">
        <a href="/eaglesuite/pages/rapports/utils/impression.php?cat=ventes&type=facture_client&ticket=a4&id=<?= $vente['id'] ?>" target="_blank" class="btn btn-success btn-sm">
          <i class="fa fa-print"></i>
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>

    </div> 
  </div> 
</div> 
