<?php
require_once __DIR__ . '/security.php'; 
// 
$currentPage = $currentPage ?? basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<aside class="app-sidebar" id="appSidebar" role="navigation">
    <div class="px-2 pb-3">
        <h6 class="text-muted px-2">Menu principal</h6>
        <ul class="nav flex-column">

            <!-- TABLEAU DE BORD -->
            <?php if (checkPermission('acces_dashboard')): ?>
                <li>
                    <a class="nav-link<?= ($currentPage === 'dashboard') ? ' active' : '' ?>"
                       href="/quincaillerie/index.php?page=dashboard">
                        <i class="fa fa-home"></i> Tableau de bord
                    </a>
                </li>
            <?php endif; ?>


            <!-- STOCK & PRODUITS -->
            <?php if (hasAnyPermission(['acces_produits','acces_categories','acces_depots','acces_retours'])): ?>
                <?php if (checkPermission('acces_produits')): ?>
                    <li><a class="nav-link<?= (strpos($currentPage,'produit')!==false) ? ' active' : '' ?>"
                           href="/quincaillerie/index.php?page=produits"><i class="fa fa-boxes"></i> Produits</a></li>
                <?php endif; ?>
                <?php if (checkPermission('acces_categories')): ?>
                    <li><a class="nav-link<?= (strpos($currentPage,'categorie')!==false) ? ' active' : '' ?>"
                           href="/quincaillerie/index.php?page=categories"><i class="fa fa-tags"></i> Catégories</a></li>
                <?php endif; ?>
                <?php if (checkPermission('acces_depots')): ?>
                    <li><a class="nav-link<?= (strpos($currentPage,'depots')!==false) ? ' active' : '' ?>"
                           href="/quincaillerie/index.php?page=depots"><i class="fa fa-warehouse"></i> Dépôts</a></li>
                <?php endif; ?>
                <?php if (checkPermission('acces_retours')): ?>
                    <li><a class="nav-link<?= (strpos($currentPage,'retours')!==false) ? ' active' : '' ?>"
                           href="/quincaillerie/index.php?page=retours"><i class="fa fa-rotate-left"></i> Retours</a></li>
                <?php endif; ?>
            <?php endif; ?>


            <!-- VENTES & ACHATS -->
            <?php if (hasAnyPermission(['acces_ventes','acces_achats'])): ?>
                <?php if (checkPermission('acces_ventes')): ?>
                    <li><a class="nav-link<?= (strpos($currentPage,'ventes')!==false) ? ' active' : '' ?>"
                           href="/quincaillerie/index.php?page=ventes"><i class="fa fa-shopping-cart"></i> Ventes</a></li>
                <?php endif; ?>
                <?php if (checkPermission('acces_achats')): ?>
                    <li><a class="nav-link<?= (strpos($currentPage,'achats')!==false) ? ' active' : '' ?>"
                           href="/quincaillerie/index.php?page=achats"><i class="fa fa-truck"></i> Achats</a></li>
                <?php endif; ?>
            <?php endif; ?>


            <!-- TRÉSORERIE -->
            <?php if (hasAnyPermission(['acces_tresorerie','acces_caisse_especes','acces_caisse_banque','acces_caisse_mobile','acces_autres_operations'])): ?>
                <li>
                    <a class="nav-link d-flex justify-content-between align-items-center"
                       data-bs-toggle="collapse"
                       data-bs-target="#submenuTresorerie"
                       role="button"
                       aria-expanded="false"
                       aria-controls="submenuTresorerie">
                        <span><i class="fa fa-wallet"></i> Trésorerie</span>
                        <i class="fa fa-chevron-down small"></i>
                    </a>
                    <ul class="collapse ps-3" id="submenuTresorerie">
                        <?php if (checkPermission('voir_vue_globale')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'tresorerie')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=tresorerie"><i class="fa fa-eye"></i> Vue globale</a>
                        <?php endif; ?>
                        <?php if (checkPermission('acces_caisse_especes')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'caisse_especes')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=caisse_especes"><i class="fa fa-money-bill"></i> Caisse Espèces</a>
                        <?php endif; ?>
                        <?php if (checkPermission('acces_caisse_banque')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'caisse_banque')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=caisse_banque"><i class="fa fa-university"></i> Caisse Banque</a>
                        <?php endif; ?>
                        <?php if (checkPermission('acces_caisse_mobile')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'caisse_mobile')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=caisse_mobile"><i class="fa fa-mobile-alt"></i> Caisse Mobile Money</a>
                        <?php endif; ?>
                        <?php if (checkPermission('acces_autres_operations')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'autres_operations')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=autres_operations"><i class="fa fa-exchange-alt"></i> Autres opérations</a>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>


            <!-- FINANCES -->
            <?php if (hasAnyPermission(['acces_finances','voir_creances_clients','voir_dettes_fournisseurs'])): ?>
                <li>
                    <a class="nav-link d-flex justify-content-between align-items-center"
                       data-bs-toggle="collapse"
                       data-bs-target="#submenuFinances"
                       role="button"
                       aria-expanded="false"
                       aria-controls="submenuFinances">
                        <span><i class="fa fa-chart-line"></i> Finances</span>
                        <i class="fa fa-chevron-down small"></i>
                    </a>
                    <ul class="collapse ps-3" id="submenuFinances">
                        <?php if (checkPermission('voir_creances_clients')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'creances')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=creances"><i class="fa fa-hand-holding-usd"></i> Créances Clients</a>
                        <?php endif; ?>
                        <?php if (checkPermission('voir_dettes_fournisseurs')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'dettes')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=dettes"><i class="fa fa-file-invoice-dollar"></i> Dettes Fournisseurs</a>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>


            <!-- RELATIONS -->
            <?php if (hasAnyPermission(['acces_clients','acces_fournisseurs','acces_employes'])): ?>
                <li>
                    <a class="nav-link d-flex justify-content-between align-items-center"
                       data-bs-toggle="collapse"
                       data-bs-target="#submenuRelations"
                       role="button"
                       aria-expanded="false"
                       aria-controls="submenuRelations">
                        <span><i class="fa fa-users"></i> Relations</span>
                        <i class="fa fa-chevron-down small"></i>
                    </a>
                    <ul class="collapse ps-3" id="submenuRelations">
                        <?php if (checkPermission('acces_clients')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'clients')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=clients"><i class="fa fa-user"></i> Clients</a>
                        <?php endif; ?>
                        <?php if (checkPermission('acces_fournisseurs')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'fournisseurs')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=fournisseurs"><i class="fa fa-truck"></i> Fournisseurs</a>
                        <?php endif; ?>
                        <?php if (checkPermission('acces_employes')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'employes')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=employes"><i class="fa fa-id-badge"></i> Employés</a>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>


            <!-- ADMINISTRATION -->
            <?php if (hasAnyPermission(['acces_utilisateurs','acces_roles','acces_parametres'])): ?>
                <li>
                    <a class="nav-link d-flex justify-content-between align-items-center"
                       data-bs-toggle="collapse"
                       data-bs-target="#submenuAdministration"
                       role="button"
                       aria-expanded="false"
                       aria-controls="submenuAdministration">
                        <span><i class="fa fa-cogs"></i> Administration</span>
                        <i class="fa fa-chevron-down small"></i>
                    </a>
                    <ul class="collapse ps-3" id="submenuAdministration">
                        <?php if (checkPermission('acces_utilisateurs')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'utilisateurs')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=utilisateurs"><i class="fa fa-user-shield"></i> Utilisateurs</a>
                        <?php endif; ?>
                        <?php if (checkPermission('acces_roles')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'roles')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=roles"><i class="fa fa-key"></i> Rôles & Permissions</a>
                        <?php endif; ?>
                        <?php if (checkPermission('acces_parametres')): ?>
                            <a class="nav-link <?= (strpos($currentPage,'parametres')!==false) ? 'active' : '' ?>"
                               href="/quincaillerie/index.php?page=parametres"><i class="fa fa-cog"></i> Paramètres</a>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>

        </ul>
    </div>

    <div class="px-3 mt-3 text-muted small">
        <div><i class="fa fa-building me-2"></i> Quincaillerie</div>
    </div>
</aside>
