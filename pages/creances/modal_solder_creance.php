<?php foreach ($creances as $creance): ?>
<div class="modal fade" id="solder<?= $creance['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="/eaglesuite/api/creances/solder_creance.php">
            <input type="hidden" name="creance_id" value="<?= $creance['id'] ?>">
            <input type="hidden" name="caisse_id" value="1">
            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?? 0 ?>">

            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Solder la Créance N° <?= $creance['id'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Client : <strong><?= htmlspecialchars($creance['client_nom'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <p>Montant total : <strong><?= number_format($creance['montant_total_devise'], 2, ',', ' ') ?> <?= $creance['symbole'] ?></strong></p>
                    <p>Déjà payé : <strong><?= number_format($creance['montant_paye_devise'] ?? 0, 2, ',', ' ') ?> <?= $creance['symbole'] ?></strong></p>
                    <p>Reste à payer : <strong><?= number_format($creance['reste_a_payer_devise'], 2, ',', ' ') ?> <?= $creance['symbole'] ?></strong></p>

                    <div class="mb-3">
                        <label for="montant_verse">Nouveau montant versé :</label>
                        <input type="number" step="0.01" 
                               name="montant_verse" 
                               required 
                               class="form-control" 
                               max="<?= $creance['reste_a_payer_devise'] ?>" 
                               placeholder="Ex: 5000">
                    </div>

                    <div class="mb-3">
                        <label for="mode_paiement">Mode de paiement :</label>
                        <select name="mode_paiement" class="form-select">
                            <option value="Espèces">Espèces</option>
                            <option value="Virement">Virement</option>
                            <option value=" Mobile Money">Mobile Money</option>
                            <option value="Chèque">Chèque</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Valider le paiement</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>
