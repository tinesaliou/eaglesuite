<?php
// /eaglesuite/admin/includes/sidebar.php
// $CURRENT_PAGE attendu
$active = function($p) use ($CURRENT_PAGE) {
    return ($CURRENT_PAGE === $p) ? 'active' : '';
};
?>

<nav class="app-sidebar" id="appSidebar" aria-label="Sidebar">
  <div class="px-2">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?= $active('dashboard') ?>" href="/eaglesuite/admin/index.php?page=dashboard">
          <i class="bi bi-house-door"></i> Tableau de bord
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $active('clients') ?>" href="/eaglesuite/admin/index.php?page=clients">
          <i class="bi bi-people"></i> Clients SaaS
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $active('abonnements') ?>" href="/eaglesuite/admin/index.php?page=abonnements">
          <i class="bi bi-calendar-check"></i> Abonnements
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $active('facturation') ?>" href="/eaglesuite/admin/index.php?page=facturation">
          <i class="bi bi-file-earmark-text"></i> Facturation
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $active('renouvellement') ?>" href="/eaglesuite/admin/index.php?page=renouvellement">
          <i class="bi bi-arrow-repeat"></i> Renouvellements
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $active('suspensions') ?>" href="/eaglesuite/admin/index.php?page=suspensions">
          <i class="bi bi-stop"></i> Suspensions
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <?= $active('paiement') ?>" href="/eaglesuite/admin/index.php?page=paiement">
          <i class="bi bi-credit-card"></i> Paiements
        </a>
      </li>

    </ul>
  </div>
</nav>
