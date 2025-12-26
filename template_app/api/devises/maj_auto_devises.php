<?php

require_once __DIR__ . "/../../config/db.php"; 


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["maj_auto"])) {
    try {
        // Devise de base : XOF
        $apiUrl = "https://api.exchangerate.host/latest?base=XOF";
        $json = file_get_contents($apiUrl);
        $data = json_decode($json, true);

        if (isset($data["rates"])) {
            $updated = 0;
            foreach ($data["rates"] as $code => $taux) {
                // Mettre à jour uniquement si la devise existe dans ta base
                $stmt = $conn->prepare("UPDATE devises SET taux_par_defaut = ?, date_creation = NOW() WHERE code = ?");
                $stmt->execute([$taux, $code]);
                if ($stmt->rowCount() > 0) $updated++;
            }
            echo "<div class='alert alert-success'> $updated taux mis à jour avec succès.</div>";
        } else {
            echo "<div class='alert alert-warning'> Impossible de lire les données de l’API.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'> Erreur lors de la mise à jour : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
