<?php
if (session_status() === PHP_SESSION_NONE) session_start();
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Accès refusé</title>
<link rel="stylesheet" href="/{{TENANT_DIR}}/public/vendor/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container text-center mt-5">
    <h1 class="text-danger mb-3">⛔ Accès suspendu</h1>
    <p class="lead">Votre abonnement est expiré ou une facture est impayée.</p>

    <div class="alert alert-warning w-50 mx-auto">
        Veuillez régulariser votre paiement pour restaurer l'accès.
    </div>

    <a href="https://wa.me/221778006335?text=Je veux renouveler mon abonnement"
       class="btn btn-success btn-lg">
       Payer maintenant via WhatsApp
    </a>
</div>

</body>
</html>
