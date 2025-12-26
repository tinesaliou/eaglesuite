<?php
// /eaglesuite/admin/includes/header.php
if (!isset($CURRENT_ADMIN)) {
    require_once __DIR__ . "/auth.php"; // doit définir $CURRENT_ADMIN ou faire redirect
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin EagleSuite</title>

  <!-- CSS locaux (tu as ces fichiers dans /public) -->
  <link rel="stylesheet" href="/eaglesuite/public/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="/eaglesuite/public/vendor/bootstrap/icons/bootstrap-icons.css">

  <link rel="stylesheet" href="/eaglesuite/public/css/style.css">
  <link rel="stylesheet" href="/eaglesuite/public/css/datatables.min.css">
  <link rel="shortcut icon" href="/eaglesuite/public/icone/favicon.png" type="image/png">

  <style>
    :root { --sidebar-width: 260px; --topbar-height: 64px; --accent:#ff8c00; }
    body { min-height:100vh; }
    /* Topbar */
    .app-topbar {
      position: fixed; top: 0; left: 0; right: 0; height: var(--topbar-height); z-index: 1040;
      background: #fff; border-bottom: 1px solid #e9ecef; display:flex; align-items:center; padding:0 .75rem;
    }
    .app-topbar .brand img { height:40px; }
    .app-topbar .top-actions { margin-left:auto; display:flex; gap:.6rem; align-items:center; }

    /* Sidebar */
    .app-sidebar {
      width: var(--sidebar-width); background: #fff; height: calc(100vh - var(--topbar-height));
      position: fixed; left: 0; top: var(--topbar-height); overflow-y:auto; z-index:1035;
      border-right:1px solid #e9ecef; padding-top: .75rem;
    }
    .app-sidebar .nav-link { color:#333; display:flex; align-items:center; gap:.6rem; padding:.45rem .9rem; border-radius:.35rem; }
    .app-sidebar .nav-link.active, .app-sidebar .nav-link:hover { background:#0d6efd; color:#fff !important; }

    /* Main */
    .app-main { margin-left: var(--sidebar-width); padding-top: calc(var(--topbar-height) + 16px); padding: 1.25rem; min-height:100vh; background:#f8f9fa; }

    /* Mobile */
    @media (max-width:991.98px){
      .app-sidebar { transform: translateX(-110%); transition: transform .28s ease; box-shadow: 4px 0 24px rgba(0,0,0,.08); position: fixed; z-index: 1100; }
      .app-sidebar.open { transform: translateX(0); }
      .app-main { margin-left: 0; padding-top: calc(var(--topbar-height) + 10px); }
      .btn-sidebar-toggle { display:inline-flex; }
    }

    .btn-sidebar-toggle { display:none; border:0; background:transparent; }
    .breadcrumb-wrap { margin-bottom: .75rem; }

    .action-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50% !important;
    font-size: 14px;
}
 
  </style>
</head>
<body>

  <!-- Topbar -->
  <header class="app-topbar">
    <button class="btn btn-sm btn-light me-2 btn-sidebar-toggle" id="btnToggleSidebar" title="Menu">
      <i class="bi bi-list"></i>
    </button>

    <a class="d-flex align-items-center text-decoration-none" href="/eaglesuite/admin/index.php?page=dashboard">
      <img src="/eaglesuite/public/icone/eaglesuite.png" alt="EagleSuite" style="height:36px;margin-right:.6rem">
      <strong style="color:var(--accent)">EagleSuite</strong>
    </a>

    <div class="top-actions">
      <div class="me-3 text-muted small">
        <?= htmlspecialchars($CURRENT_ADMIN['email'] ?? 'admin') ?>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/eaglesuite/admin/logout.php">Se déconnecter</a>
    </div>
  </header>

  <?php include __DIR__ . '/sidebar.php'; ?>

  <main class="app-main">
    <?php
      // breadcrumbs helper
      include_once __DIR__ . '/breadcrumbs.php';
      echo render_breadcrumbs($CURRENT_PAGE ?? 'dashboard');
    ?>
