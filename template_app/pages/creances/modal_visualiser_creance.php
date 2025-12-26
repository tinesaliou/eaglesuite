<?php foreach ($creances as $creance): ?>
<div class="modal fade" id="view<?= $creance['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title">Détails de la Créance N° <?= $creance['id'] ?></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p><strong>Client :</strong> <?= htmlspecialchars($creance['client_nom'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Date :</strong> <?= htmlspecialchars($creance['date_creation'], ENT_QUOTES, 'UTF-8') ?></p>

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
                            vd.id,
                            p.nom AS produit,
                            vd.quantite,
                            vd.prix_unitaire AS prix_cfa,
                            (vd.prix_unitaire / v.taux_change) AS prix_devise,
                            (vd.quantite * vd.prix_unitaire) AS montant_cfa,
                            ((vd.quantite * vd.prix_unitaire) / v.taux_change) AS total_devise,
                            (v.totalHT / v.taux_change) AS totalHT_devise,
                            (v.totalTTC / v.taux_change) AS totalTTC_devise,
                            (v.taxe / v.taux_change) AS taxe_devise,
                            v.devise_id,
                            v.taux_change
                        FROM ventes_details vd
                        JOIN produits p ON p.id = vd.produit_id
                        JOIN ventes v ON v.id = vd.vente_id
                        WHERE vd.vente_id = ?
                        ");
                        $stmt->execute([$creance['vente_id']]);
                        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($details as $detail): ?>
                            <tr>
                                <td><?= htmlspecialchars($detail['produit'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int)$detail['quantite'] ?></td>
                                <td><?= number_format($detail['prix_devise'], 2, ',', ' ') ?> <?= $creance['symbole'] ?></td>
                                <td><?= number_format($detail['quantite'] * $detail['prix_devise'], 2, ',', ' ') ?> <?= $creance['symbole'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <hr>
                <p><strong>Total TTC :</strong> <?= number_format($creance['montant_total_devise'], 2, ',', ' ') ?> <?= $creance['symbole'] ?></p>
                <p><strong>Montant payé :</strong> <?= number_format($creance['montant_paye_devise'] ?? 0, 2, ',', ' ') ?> <?= $creance['symbole'] ?></p>
                <p><strong>Reste à payer :</strong> <?= number_format($creance['reste_a_payer_devise'] ?? 0, 2, ',', ' ') ?> <?= $creance['symbole'] ?></p>
                <p><strong>Statut :</strong> 
                    <?php if ($creance['statut'] === "Soldé"): ?>
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
