<?php
// includes/sidebar.php
// ATTENTION : require_once 'config/db.php' doit déjà être appelé avant si load_user_permissions a besoin de DB
if (session_status() === PHP_SESSION_NONE) session_start();

// Si pas connecté, on n'affiche pas le menu
if (empty($_SESSION['user_id'])) return;

?>
<aside class="app-sidebar" id="appSidebar" role="navigation">
  <div class="px-2 pb-3">
    <h6 class="text-muted px-2">Menu principal</h6>
    <ul class="nav flex-column">
      <?php if (has_permission('dashboard.view')): ?>
        <li><a class="nav-link<?= ($currentPage === 'dashboard') ? ' active' : '' ?>" href="/eaglesuite/index.php?page=dashboard"><i class="fa fa-home"></i> Tableau de bord</a></li>
      <?php endif; ?>

      <?php if (has_permission('ventes.view')): ?>
        <li><a class="nav-link<?= (strpos($currentPage,'ventes')!==false) ? ' active' : '' ?>" href="/eaglesuite/index.php?page=ventes"><i class="fa fa-shopping-cart"></i> Ventes</a></li>
      <?php endif; ?>

      <?php if (has_permission('achats.view')): ?>
        <li><a class="nav-link<?= (strpos($currentPage,'achats')!==false) ? ' active' : '' ?>" href="/eaglesuite/index.php?page=achats"><i class="fa fa-truck"></i> Achats</a></li>
      <?php endif; ?>

      <?php if (has_any_permission(['produits.view','categories.view','depots.view','retours.view'])): ?>
        <li class="sidebar-item">
          <a href="#" class="nav-link sidebar-toggle" data-target="#stockMenu"><span><i class="fa fa-boxes"></i> Stock</span></a>
          <ul id="stockMenu" class="sidebar-dropdown">
            <?php if (has_permission('produits.view')): ?>
              <a class="nav-link <?= (strpos($currentPage,'produit')!==false) ? 'active' : '' ?>" href="/eaglesuite/index.php?page=produits"><i class="fa fa-box"></i> Produits</a>
            <?php endif; ?>
            <?php if (has_permission('categories.view')): ?>
              <a class="nav-link <?= (strpos($currentPage,'categorie')!==false) ? 'active' : '' ?>" href="/eaglesuite/index.php?page=categories"><i class="fa fa-tags"></i> Catégories</a>
            <?php endif; ?>
            <?php if (has_permission('depots.view')): ?>
              <a class="nav-link <?= (strpos($currentPage,'depots')!==false) ? 'active' : '' ?>" href="/eaglesuite/index.php?page=depots"><i class="fa fa-warehouse"></i> Dépôts</a>
            <?php endif; ?>
          </ul>
        </li>
      <?php endif; ?>

      <?php if (has_permission('users.view')): ?>
        <li class="sidebar-item">
          <a class="nav-link" href="/eaglesuite/index.php?page=utilisateurs"><i class="fa fa-user-shield"></i> Utilisateurs</a>
        </li>
      <?php endif; ?>

      <?php if (has_permission('roles.view')): ?>
        <li class="sidebar-item">
          <a class="nav-link" href="/eaglesuite/index.php?page=roles"><i class="fa fa-key"></i> Rôles & Permissions</a>
        </li>
      <?php endif; ?>

    </ul>
  </div>
</aside>
