<?php
require_once __DIR__ . '/../config/check_access.php';
if (basename($_SERVER['SCRIPT_NAME']) === 'login.php') {
    return; // Stoppe immédiatement, empêche sidebar/topbar/HTML
}
// includes/layout.php
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>EagleSuite</title>

  
  <!-- Styles externes -->
  <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"> -->
   <link rel="stylesheet" href="/{{TENANT_DIR}}/public/vendor/fontawesome/css/all.min.css">

   <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> -->
   <link rel="stylesheet" href="/{{TENANT_DIR}}/public/vendor/bootstrap/css/bootstrap.min.css">
   <link rel="stylesheet" href="/{{TENANT_DIR}}/public/vendor/bootstrap/icons/bootstrap-icons.css">
   
  <!-- <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet"> 
  <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet"> -->

  


  <!-- Ton CSS local (unique, évite les doublons) -->
  <link rel="stylesheet" href="/{{TENANT_DIR}}/public/css/style.css">
  <link rel="stylesheet" href="/{{TENANT_DIR}}/public/css/datatables-custom.css">
  <link rel="stylesheet" href="/{{TENANT_DIR}}/public/css/datatables.min.css">
  <link rel="stylesheet" href="/{{TENANT_DIR}}/public/css/bootstrap.min.css">
  
  <link rel="shortcut icon" href="/{{TENANT_DIR}}/public/icone/favicon.png" type="image/png">


   


  <style>
    :root { --sidebar-width: 260px; --topbar-height: 64px; --accent:#ff8c00; }

    /* Topbar */
    .app-topbar {
      position: fixed;
      top: 0; left: 0; right: 0;
      height: var(--topbar-height);
      z-index: 1040;
      background: #fff;
      border-bottom: 1px solid #e9ecef;
      display: flex;
      align-items: center;
      padding: 0 1rem;
      gap: .75rem;
    }
    .app-topbar .brand img { height:44px; }

    /* Sidebar */
    .app-sidebar {
      width: var(--sidebar-width);
      background: #fff;
      height: 100vh;
      position: fixed;
      left: 0;
      top: var(--topbar-height);
      overflow-y: auto;
      transition: transform .28s ease, box-shadow .28s ease;
      z-index: 1035;
      border-right:1px solid #e9ecef;
    }

    /* Mobile: hide sidebar by translating left */
    @media (max-width: 991.98px) {
      .app-sidebar { transform: translateX(-100%); box-shadow: 3px 0 18px rgba(0,0,0,0.12); }
      .app-sidebar.sidebar-open { transform: translateX(0); }
      .app-main { margin-left: 0; }
      .btn-sidebar-toggle { display:inline-flex !important; }
    }

    /* Desktop main spacing */
    .app-main {
      margin-left: var(--sidebar-width);
      padding-top: calc(var(--topbar-height) + 10px);
      padding-left: 1.25rem;
      padding-right: 1.25rem;
      min-height: calc(150vh - var(--topbar-height));
      background: #f8f9fa;
      padding-bottom: 2rem;
    }

    /* Sidebar links */
    .app-sidebar .nav-link { color:#111; display:flex; align-items:center; gap:.6rem; padding:.45rem .75rem; border-radius:.35rem; }
    .app-sidebar .nav-link.active, .app-sidebar .nav-link:hover { background:#0d6efd; color:#fff !important; text-decoration:none; }

    /* Sidebar dropdowns */
    .sidebar-dropdown { max-height:0; overflow:hidden; transition: max-height .32s ease; padding-left: .8rem; }
    .sidebar-dropdown.open { max-height: 999px; }

    /* mobile toggle button (hidden on desktop via inline class) */
    .btn-sidebar-toggle { display:none; }
  </style>
</head>
<body>

<?php
// inclure la topbar et le sidebar (ils utilisent $_SESSION)
// inclusions avec include_once pour éviter doubles inclusions
include_once __DIR__ . '/topbar.php';
include_once __DIR__ . '/sidebar.php';
?>

<main class="app-main">
