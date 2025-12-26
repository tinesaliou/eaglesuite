<?php
$title = "Rapports Achats";
?>

<div class="container mt-4">
    <h4 class="mb-3"><i class="fa fa-truck me-2"></i>Rapports - Achats</h4>

    <div class="card p-3 shadow-sm">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom du rapport</th>
                    <th>Section</th>
                    <th>Description</th>
                    <th>Format</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Bon de commande fournisseur</strong></td>
                    <td>Achats</td>
                    <td>Commande envoyée à un fournisseur</td>
                    <td>A4</td>
                    <td><a href="/rapports/achats/bon_commande_fournisseur.jasper" class="btn btn-sm btn-primary"><i class="fa fa-print"></i> Imprimer</a></td>
                </tr>
                <tr>
                    <td><strong>Bon de réception</strong></td>
                    <td>Achats / Stock</td>
                    <td>Réception de marchandises</td>
                    <td>A4</td>
                    <td><a href="/rapports/achats/bon_reception.jasper" class="btn btn-sm btn-primary"><i class="fa fa-print"></i> Imprimer</a></td>
                </tr>
                <tr>
                    <td><strong>Facture fournisseur</strong></td>
                    <td>Achats</td>
                    <td>Facture d’achat validée</td>
                    <td>A4</td>
                    <td><a href="/rapports/achats/facture_fournisseur.jasper" class="btn btn-sm btn-primary"><i class="fa fa-print"></i> Imprimer</a></td>
                </tr>
                <tr>
                    <td><strong>Journal des achats</strong></td>
                    <td>Achats</td>
                    <td>Historique des achats par période</td>
                    <td>A4 / Excel</td>
                    <td><a href="/rapports/achats/journal_achats.jasper" class="btn btn-sm btn-primary"><i class="fa fa-file-export"></i> Exporter</a></td>
                </tr>
                <tr>
                    <td><strong>État fournisseurs</strong></td>
                    <td>Achats / Comptabilité</td>
                    <td>Liste des fournisseurs et soldes dus</td>
                    <td>A4 / Excel</td>
                    <td><a href="/rapports/achats/etat_fournisseurs.jasper" class="btn btn-sm btn-primary"><i class="fa fa-print"></i> Imprimer</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
