<?php
// includes/topbar.php
// Charge uniquement les infos utilisateur à afficher (nom / email)
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';

$utilisateur = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id, nom, email FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
    // ne pas manipuler la session ici
}

// Charger l'entreprise (1ère par défaut)
$entreprise = null;

$stmt = $conn->query("
    SELECT nom, logo
    FROM entreprise
    ORDER BY id ASC
    LIMIT 1
");
$entreprise = $stmt->fetch(PDO::FETCH_ASSOC);

?>



<header class="app-topbar">
  <div class="d-flex align-items-center">
    <!-- bouton affiché sur mobile -->
    <button id="sidebarToggle" class="btn btn-outline-secondary btn-sidebar-toggle me-2" aria-label="Basculer le menu">
      <i class="fa fa-bars"></i>
    </button>

    <div class="brand d-flex align-items-center">
      <img src="/eaglesuite/public/icone/eaglesuite.png" alt="EagleSuite" />
      <div class="ms-2 d-none d-md-block">
        <div style="font-weight:700; font-size:1rem;">EagleSuite</div>
        <small class="text-muted">Gestion commerciale</small>
      </div>
      <div class="vr d-none d-md-block"></div>

      <!-- Bienvenue entreprise -->
      <div class="topbar-center d-none d-md-block text-center">
        <div style="font-size:0.85rem;" class="text-muted">
          Bienvenue
        </div>
        <div style="font-weight:600;">
          <?= htmlspecialchars($entreprise['nom'], ENT_QUOTES, 'UTF-8' ?? 'Entreprise') ?>
        </div>
      </div>
    </div>
  </div>

  <div class="ms-auto d-flex align-items-center gap-3">
    <form class="d-flex me-2" role="search" onsubmit="return false;">
      <input class="form-control form-control-sm" type="search" placeholder="Rechercher..." id="topSearch" aria-label="Recherche">
    </form>

    <div class="dropdown">
      <a class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-user-circle fa-2x me-2"></i>
        <div class="d-none d-sm-block">
          <div style="font-weight:600"><?= htmlspecialchars($utilisateur['nom'], ENT_QUOTES, 'UTF-8' ?? 'Utilisateur') ?></div>
          <small class="text-muted"><?= htmlspecialchars($utilisateur['email'], ENT_QUOTES, 'UTF-8' ?? '') ?></small>
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="/eaglesuite/index.php?page=profil"><i class="fa fa-user me-2"></i>Mon profil</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="/eaglesuite/deconnexion.php"><i class="fa fa-sign-out-alt me-2"></i>Déconnexion</a></li>
      </ul>
    </div>
  </div>
</header>
