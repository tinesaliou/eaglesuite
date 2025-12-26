<?php
$email = $_GET['email'] ?? '';
$pass  = $_GET['pass'] ?? '';
$sub   = $_GET['sub'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Client créé</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-5">

<h2>🎉 Client créé avec succès !</h2>

<p>Identifiants admin du client :</p>

<ul class="list-group mb-3">
    <li class="list-group-item"><strong>Email : </strong><?= $email ?></li>
    <li class="list-group-item"><strong>Mot de passe : </strong><?= $pass ?></li>
</ul>

<a href="clients.php" class="btn btn-primary">Retour aux clients</a>
<a class="btn btn-success" target="_blank"
   href="http://localhost/eagle_client_<?= $sub ?>/login.php">
   Accéder au client
</a>

</div>

</body>
</html>
