<!-- <:?php
echo "<h1 style='color:red;text-align:center;margin-top:50px'>⛔ Accès refusé</h1>";
echo "<p style='text-align:center'>Vous n'avez pas la permission d’accéder à cette page.</p>";
echo "<p style='text-align:center'><a href='/eaglesuite/index.php?page=dashboard'>Retour au dashboard</a></p>";
 -->

 <?php
// includes/access_denied.php
if (session_status() === PHP_SESSION_NONE) session_start();
http_response_code(403);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Accès refusé</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width:600px">
      <div class="card-body text-center">
        <h3 class="text-danger">Accès refusé</h3>
        <p class="mb-4">Vous n'avez pas la permission nécessaire pour voir cette page.</p>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="/eaglesuite/index.php?page=dashboard" class="btn btn-primary">Retour au tableau de bord</a>
        <?php else: ?>
          <a href="/eaglesuite/login.php" class="btn btn-primary">Se connecter</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
