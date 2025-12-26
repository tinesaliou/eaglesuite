<?php
// includes/sidebar.php
// Attention : check_auth.php doit avoir été exécuté AVANT d'inclure layout_start.php (par ex. dans index.php)
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<aside class="app-sidebar" id="appSidebar" role="navigation" aria-label="Navigation principale">
  <div class="px-2 pb-3">
    <!-- <h6 class="text-muted px-2">Menu principal</h6> -->
    <ul class="nav flex-column">

      <?php if (checkPermission('dashboard.view')): ?>
      <li><a class="nav-link<?= ($currentPage === 'dashboard') ? ' active' : '' ?>" href="/{{TENANT_DIR}}/index.php?page=dashboard"><i class="fa fa-home"></i> Tableau de bord</a></li>
      <?php endif; ?>

    <?php if (hasAnyPermission(['crm.dashboard.view','crm.clients.view','crm.opportunites.view','crm.interactions.view' ])): ?>
    <li class="sidebar-item">
      <a href="#" class="nav-link sidebar-toggle d-flex justify-content-between align-items-center" 
        data-target="#crmMenu" aria-expanded="false">
        <span><i class="fa fa-address-card"></i> CRM</span>
        <i class="fa fa-chevron-down small toggle-icon"></i>
      </a>

      <ul id="crmMenu" class="sidebar-dropdown">

        <?php if (checkPermission('crm.dashboard.view')): ?>
        <a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=crm_dashboard">
          <i class="fa fa-chart-line"></i> Tableau de bord
        </a>
        <?php endif; ?>

        <?php if (checkPermission('crm.clients.view')): ?>
        <a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=crm_clients">
          <i class="fa fa-users"></i> Clients CRM
        </a>
        <?php endif; ?>

        <?php if (checkPermission('crm.opportunites.view')): ?>
        <a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=crm_opportunites">
          <i class="fa fa-bullseye"></i> Opportunités
        </a>
        <?php endif; ?>

        <?php if (checkPermission('crm.interactions.view')): ?>
        <a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=crm_interactions">
          <i class="fa fa-comments"></i> Interactions
        </a>
        <?php endif; ?>

      </ul>
    </li>
    <?php endif; ?>


      <?php if (checkPermission('ventes.view')): ?>
      <li><a class="nav-link<?= (strpos($currentPage,'ventes')!==false) ? ' active' : '' ?>" href="/{{TENANT_DIR}}/index.php?page=ventes"><i class="fa fa-shopping-cart"></i> Ventes</a></li>
      <?php endif; ?>

      <?php if (checkPermission('achats.view')): ?>
      <li><a class="nav-link<?= (strpos($currentPage,'achats')!==false) ? ' active' : '' ?>" href="/{{TENANT_DIR}}/index.php?page=achats"><i class="fa fa-truck"></i> Achats</a></li>
      <?php endif; ?>

      <?php if (hasAnyPermission(['produits.view','categories.view','depots.view','mouvements.view','retours.view','inventaire.view'])): ?>
      <li class="sidebar-item">
        <a href="#" class="nav-link sidebar-toggle d-flex justify-content-between align-items-center" data-target="#stockMenu" aria-expanded="false">
          <span><i class="fa fa-boxes"></i> Stock</span>
          <i class="fa fa-chevron-down small toggle-icon"></i>
        </a>
        <ul id="stockMenu" class="sidebar-dropdown">
          <?php if (checkPermission('produits.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=produits"><i class="fa fa-box"></i> Produits</a><?php endif; ?>
          <?php if (checkPermission('categories.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=categories"><i class="fa fa-tags"></i> Catégories</a><?php endif; ?>
          <?php if (checkPermission('depots.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=depots"><i class="fa fa-warehouse"></i> Dépôts</a><?php endif; ?>
          <?php if (checkPermission('retours.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=retours"><i class="fa fa-rotate-left"></i> Retours</a><?php endif; ?>
          <?php if (checkPermission('mouvements.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=mouvements"><i class="fa fa-exchange-alt"></i> Mouvements</a><?php endif; ?>
          <?php if (checkPermission('inventaire.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=inventaire"><i class="fa fa-clipboard-list"></i> Inventaire</a><?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <?php if (hasAnyPermission(['tresorerie.view','caisse.especes.view','caisse.banque.view','caisse.mobile.view','operations.autres.view'])): ?>
      <li class="sidebar-item">
        <a href="#" class="nav-link sidebar-toggle d-flex justify-content-between align-items-center" data-target="#tresorerieMenu" aria-expanded="false">
          <span><i class="fa fa-wallet"></i> Trésorerie</span><i class="fa fa-chevron-down small toggle-icon"></i>
        </a>
        <ul id="tresorerieMenu" class="sidebar-dropdown">
          <?php if (checkPermission('tresorerie.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=tresorerie"><i class="fa fa-eye"></i> Vue globale</a><?php endif; ?>
          <?php if (checkPermission('caisse.especes.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=caisse_especes"><i class="fa fa-money-bill"></i> Caisse Espèces</a><?php endif; ?>
          <?php if (checkPermission('caisse.banque.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=caisse_banque"><i class="fa fa-university"></i> Caisse Banque</a><?php endif; ?>
          <?php if (checkPermission('caisse.mobile.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=caisse_mobile"><i class="fa fa-mobile-alt"></i> Caisse Mobile</a><?php endif; ?>
          <?php if (checkPermission('operations.autres.view')): ?><a class="nav-link" href="/v/index.php?page=autres_operations"><i class="fa fa-exchange-alt"></i> Autres opérations</a><?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <?php if (hasAnyPermission(['creances.view','dettes.view'])): ?>
      <li class="sidebar-item">
        <a href="#" class="nav-link sidebar-toggle d-flex justify-content-between align-items-center" data-target="#financesMenu" aria-expanded="false">
          <span><i class="fa fa-chart-line"></i> Finances</span><i class="fa fa-chevron-down small toggle-icon"></i>
        </a>
        <ul id="financesMenu" class="sidebar-dropdown">
          <?php if (checkPermission('creances.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=creances"><i class="fa fa-hand-holding-usd"></i> Créances</a><?php endif; ?>
          <?php if (checkPermission('dettes.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=dettes"><i class="fa fa-file-invoice-dollar"></i> Dettes</a><?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <?php if (hasAnyPermission(['clients.view','fournisseurs.view','employes.view'])): ?>
      <li class="sidebar-item">
        <a href="#" class="nav-link sidebar-toggle d-flex justify-content-between align-items-center" data-target="#relationsMenu" aria-expanded="false">
          <span><i class="fa fa-users"></i> Relations</span><i class="fa fa-chevron-down small toggle-icon"></i>
        </a>
        <ul id="relationsMenu" class="sidebar-dropdown">
          <?php if (checkPermission('clients.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=clients"><i class="fa fa-user"></i> Clients</a><?php endif; ?>
          <?php if (checkPermission('fournisseurs.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=fournisseurs"><i class="fa fa-truck"></i> Fournisseurs</a><?php endif; ?>
          <?php if (checkPermission('employes.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=employes"><i class="fa fa-id-badge"></i> Employés</a><?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

      <?php if (hasAnyPermission([
    'rapports.achats.view',
    'rapports.ventes.view',
    'rapports.stocks.view',
    'rapports.caisse.view'])): ?>
      <li class="sidebar-item">
         <a href="#" class="nav-link sidebar-toggle d-flex justify-content-between align-items-center" 
            data-target="#rapportsMenu" aria-expanded="false">
            <span><i class="fa fa-chart-pie"></i> Rapports</span>
            <i class="fa fa-chevron-down small toggle-icon"></i>
         </a>

         <ul id="rapportsMenu" class="sidebar-dropdown">
            <?php if (checkPermission('rapports.achats.view')): ?>
            <a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=rapports_achats">
                  <i class="fa fa-cart-arrow-down"></i> Achats</a>
            
            <?php endif; ?>

            <?php if (checkPermission('rapports.ventes.view')): ?>
            <a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=rapports_ventes">
                  <i class="fa fa-shopping-cart"></i> Ventes</a>
            
            <?php endif; ?>

            <?php if (checkPermission('rapports.stocks.view')): ?>
            <a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=rapports_stocks">
                  <i class="fa fa-boxes"></i> Stocks</a>
           
            <?php endif; ?>

            <?php if (checkPermission('rapports.caisse.view')): ?>
            <a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=rapports_caisse">
                  <i class="fa fa-wallet"></i> Caisse</a>
            
            <?php endif; ?>
         </ul>
      </li>
      <?php endif; ?>



      <?php if (hasAnyPermission(['users.manage','roles.manage','settings.view'])): ?>
      <li class="sidebar-item">
        <a href="#" class="nav-link sidebar-toggle d-flex justify-content-between align-items-center" data-target="#adminMenu" aria-expanded="false">
          <span><i class="fa fa-cogs"></i> Administration</span><i class="fa fa-chevron-down small toggle-icon"></i>
        </a>
        <ul id="adminMenu" class="sidebar-dropdown">
          <?php if (checkPermission('users.manage')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=utilisateurs"><i class="fa fa-user-shield"></i> Utilisateurs</a><?php endif; ?>
          <?php if (checkPermission('roles.manage')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=roles"><i class="fa fa-key"></i> Rôles & Permissions</a><?php endif; ?>
          <?php if (checkPermission('settings.view')): ?><a class="nav-link" href="/{{TENANT_DIR}}/index.php?page=parametres"><i class="fa fa-cog"></i> Paramètres</a><?php endif; ?>
        </ul>
      </li>
      <?php endif; ?>

    </ul>
  </div>

  <div class="text-center mt-auto pb-3">
    <img src="/{{TENANT_DIR}}/public/icone/logoeaglesuite.png" alt="Logo" style="height:70px; width:auto;">
  </div>
</aside>
