<?php

require_once __DIR__ . "/../../../config/db.php"; 

// === Mise à jour automatique des taux via API ExchangeRate.host ===
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["maj_auto"])) {
    try {
        $apiUrl = "https://api.exchangerate.host/latest?base=XOF"; // Devise de base
        $json = @file_get_contents($apiUrl);

        if ($json === false) {
            echo "<div class='alert alert-warning m-3'>⚠️ Erreur de connexion à l’API ExchangeRate.host</div>";
        } else {
            $data = json_decode($json, true);
            if (isset($data["rates"])) {
                $updated = 0;
                foreach ($data["rates"] as $code => $taux) {
                    $stmt = $conn->prepare("UPDATE devises SET taux_par_defaut = ?, date_creation = NOW() WHERE code = ?");
                    $stmt->execute([$taux, $code]);
                    if ($stmt->rowCount() > 0) $updated++;
                }
                echo "<div class='alert alert-success m-3'>✅ $updated taux mis à jour avec succès !</div>";
            } else {
                echo "<div class='alert alert-danger m-3'>❌ Format de réponse invalide reçu depuis l’API.</div>";
            }
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger m-3'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
