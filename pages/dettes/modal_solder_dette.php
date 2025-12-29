<?php foreach ($dettes as $dette): ?>
<div class="modal fade" id="solderDette<?= $dette['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="/eaglesuite/api/dettes/solder_dette.php">
            <input type="hidden" name="dette_id" value="<?= $dette['id'] ?>">
            <input type="hidden" name="caisse_id" value="1">
            <input type="hidden" name="user_id" value="<?= $_SESSION['user']['id'] ?? 0 ?>">

            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Solder la Dette N° <?= $dette['id'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Fournisseur : <strong><?= htmlspecialchars($dette['fournisseur_nom'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <p>Montant total : <strong><?= number_format($dette['montant_total_devise'], 2, ',', ' ') ?> <?= $dette['symbole'] ?></strong></p>
                    <p>Déjà payé : <strong><?= number_format($dette['montant_paye_devise'] ?? 0, 2, ',', ' ') ?> <?= $dette['symbole'] ?></strong></p>
                    <p>Reste à payer : <strong><?= number_format($dette['reste_a_payer_devise'], 2, ',', ' ') ?> <?= $dette['symbole'] ?></strong></p>

                    <div class="mb-3">
                        <label for="montant_paye">Nouveau paiement :</label>
                        <input type="number" step="0.01" 
                               name="montant_paye" 
                               required 
                               class="form-control" 
                               max="<?= $dette['reste_a_payer'] ?>" 
                               placeholder="Ex: 15000">
                    </div>

                    <div class="mb-3">
                        <label for="mode_paiement">Mode de paiement :</label>
                        <select name="mode_paiement" class="form-select">
                            <option value="Espèces">Espèces</option>
                            <option value="Virement">Virement</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Chèque">Chèque</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Valider le paiement</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>
