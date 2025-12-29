<?php
session_start();
require_once __DIR__ . "/../config/db.php";

// Si pas connecté → redirection
if (empty($_SESSION['user']['id'])) {
    header("Location: /eaglesuite/login.php");
    exit;
}

// Charger infos utilisateur
$stmt = $conn->prepare("SELECT nom, email, created_at, actif FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mon profil - EagleSuite</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body {
    background: #0f1720;
    font-family: "Poppins", sans-serif;
    color: #fff;
}

.card-profile {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    backdrop-filter: blur(10px);
}

.avatar {
    width: 100px;
    height: 100px;
    border-radius: 80px;
    background: rgba(255,140,0,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
    box-shadow: 0 0 25px rgba(255,140,0,0.2);
}

.avatar i {
    font-size: 50px;
    color: #ff8c00;
}
</style>

</head>
<body>

<div class="container mt-5">
    
    <div class="col-md-6 mx-auto card-profile">

        <div class="text-center mb-4">
            <div class="avatar"><i class="fa fa-user"></i></div>
            <h3 class="mt-3"><?= htmlspecialchars($user['nom'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-muted"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <hr class="border-secondary">

        <p><strong>Date de création :</strong> 
            <?= date("d/m/Y à H:i", strtotime($user['created_at'])) ?></p>

        <p><strong>Statut :</strong>
            <?= $user['actif'] ? '<span class="text-success">Actif</span>' : '<span class="text-danger">Inactif</span>' ?>
        </p>

        <div class="text-center mt-4">
            <a href="/eaglesuite/index.php?page=dashboard" class="btn btn-outline-light">Retour</a>
        </div>

    </div>

</div>

</body>
</html>
