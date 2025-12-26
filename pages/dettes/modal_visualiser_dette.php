<?php foreach ($dettes as $dette): ?>
<div class="modal fade" id="view<?= $dette['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title">Détails de la Dette N° <?= $dette['id'] ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p><strong>Fournisseur :</strong> <?= htmlspecialchars($dette['fournisseur_nom'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Date :</strong> <?= htmlspecialchars($dette['date_creation'], ENT_QUOTES, 'UTF-8') ?></p>

                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 🔹 Récupération des détails de la vente liée
                        $stmt = $conn->prepare("
                            SELECT 
                                ad.id,
                                p.nom AS produit,
                                ad.quantite,
                                ad.prix_unitaire AS prix_cfa,
                                (ad.prix_unitaire / a.taux_change) AS prix_devise,
                                (ad.quantite * ad.prix_unitaire) AS montant_cfa,
                                ((ad.quantite * ad.prix_unitaire) / a.taux_change) AS total_devise,
                                (a.totalHT / a.taux_change) AS totalHT_devise,
                                (a.totalTTC / a.taux_change) AS totalTTC_devise,
                                (a.taxe / a.taux_change) AS taxe_devise,
                                a.devise_id,
                                a.taux_change
                            FROM achats_details ad
                            JOIN produits p ON p.id = ad.produit_id
                        JOIN achats a ON a.id = ad.achat_id
                        WHERE ad.achat_id = ?
                        ");
                        $stmt->execute([$dette['achat_id']]);
                        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($details as $detail): ?>
                            <tr>
                                <td><?= htmlspecialchars($detail['produit'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int)$detail['quantite'] ?></td>
                                <td><?= number_format($detail['prix_devise'], 2, ',', ' ') ?> <?= $dette['symbole'] ?></td>
                                <td><?= number_format($detail['quantite'] * $detail['prix_devise'], 2, ',', ' ') ?> <?= $dette['symbole'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <hr>
                <p><strong>Total TTC :</strong> <?= number_format($dette['montant_total_devise'], 2, ',', ' ') ?> <?= $dette['symbole'] ?></p>
                <p><strong>Montant payé :</strong> <?= number_format($dette['montant_paye_devise'] ?? 0, 2, ',', ' ') ?> <?= $dette['symbole'] ?></p>
                <p><strong>Reste à payer :</strong> <?= number_format($dette['reste_a_payer_devise'] ?? 0, 2, ',', ' ') ?> <?= $dette['symbole'] ?></p>
                <p><strong>Statut :</strong> 
                    <?php if ($dette['statut'] === "Soldé"): ?>
                        <span class="badge bg-success">Soldé</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">En cours</span>
                    <?php endif; ?>
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <!-- <button type="button" class="btn btn-primary" onclick="printCreance(<?= $creance['id'] ?>)">🖨️ Imprimer</button> -->
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
