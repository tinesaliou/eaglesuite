<?php
require_once __DIR__ . "/../../config/db.php";

if (empty($_SESSION['user']['id'])) {
    header("Location: /eaglesuite/login.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT u.*, e.nom AS entreprise_nom, e.adresse AS entreprise_adresse,
           e.telephone AS entreprise_tel, e.email AS entreprise_email,
           e.site_web AS entreprise_web, e.logo AS entreprise_logo
    FROM utilisateurs u
    LEFT JOIN entreprise e ON e.id = u.entreprise_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user']['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$profilePhoto = $user["photo"]
    ? "/eaglesuite/public/uploads/profiles/" . $user["photo"]
    : "/eaglesuite/public/icone/default_user.png";
?>

<!-- TOPBAR SPÉCIALE DE LA PAGE (placée dans le main, pas en haut de layout) -->
<div class="d-flex align-items-center justify-content-between mb-4"
     style="background:#fff; padding:12px 20px; border-radius:10px; border:1px solid #ddd;">
    
    <div class="d-flex align-items-center">
        <img src="/eaglesuite/public/icone/logoeaglesuite.png" alt="logo" style="height:50px;">
        <h4 class="ms-3 mb-0">Mon Profil</h4>
    </div>

    <a href="/eaglesuite/index.php?page=dashboard" class="btn btn-outline-warning">
        <i class="fa fa-arrow-left me-2"></i> Tableau de bord
    </a>
</div>

<!-- CONTENU PROFIL -->
<div class="profile-container" style="max-width:900px;margin:auto;">

    <div class="card p-4 shadow-sm mb-4">
        <h3 class="text-center mb-3">
            <i class="fa fa-user"></i> Informations du compte
        </h3>

        <div class="text-center mb-4">
            <img src="<?= htmlspecialchars($profilePhoto) ?>" 
                 class="rounded-circle" 
                 style="width:130px;height:130px;object-fit:cover;border:3px solid #ff8c00;" 
                 id="preview">

            <form action="/eaglesuite/update_profile.php" method="POST" enctype="multipart/form-data" class="mt-3">
                <input type="file" name="photo" class="form-control" onchange="previewImage(event)">
                <button type="submit" class="btn btn-warning mt-3">Mettre à jour la photo</button>
            </form>
        </div>

        <hr>

        <h5><i class="fa fa-id-badge"></i> Informations personnelles</h5>

        <form action="/eaglesuite/update_profile.php" method="POST">
            <div class="row mt-3">
                <div class="col-md-6 mb-3">
                    <label>Nom complet</label>
                    <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
            <button class="btn btn-warning mt-2">Mettre à jour</button>
        </form>

        <hr>

        <h5><i class="fa fa-building"></i> Entreprise</h5>
        <p><strong>Nom :</strong> <?= htmlspecialchars($user['entreprise_nom'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Adresse :</strong> <?= htmlspecialchars($user['entreprise_adresse'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Téléphone :</strong> <?= htmlspecialchars($user['entreprise_tel'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($user['entreprise_email'], ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Site Web :</strong> <?= htmlspecialchars($user['entreprise_web'], ENT_QUOTES, 'UTF-8') ?></p>

        <hr>

        <h5><i class="fa fa-key"></i> Changer mon mot de passe</h5>

        <form action="/eaglesuite/change_password.php" method="POST" class="mt-3">
            <input type="password" name="old_password" class="form-control mb-2" placeholder="Ancien mot de passe" required>
            <input type="password" name="new_password" class="form-control mb-2" placeholder="Nouveau mot de passe" required>
            <input type="password" name="confirm_password" class="form-control mb-3" placeholder="Confirmer" required>

            <button class="btn btn-warning">Changer le mot de passe</button>
        </form>

    </div>
</div>

<script>
function previewImage(event) {
    const img = document.getElementById("preview");
    img.src = URL.createObjectURL(event.target.files[0]);
}
</script>
