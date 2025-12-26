<?php
// cron_renew.php
require_once __DIR__ . '/../init.php';        // définit $masterPdo, etc.
require_once __DIR__ . '/../lib/whatsapp.php'; // doit exposer sendWhatsApp($phone, $message)
date_default_timezone_set('Africa/Dakar');

function logCron($msg) {
    file_put_contents(__DIR__.'/cron.log', "[".date('Y-m-d H:i:s')."] $msg\n", FILE_APPEND);
}

logCron("=== CRON démarré ===");

try {
    $masterPdo->beginTransaction();

    /****************************************************
     * 1) RENOUVELLEMENT AUTO (abonnements expirés & auto_renew=1)
     ****************************************************/
    $stmt = $masterPdo->prepare("
        SELECT * FROM abonnements_saas
        WHERE statut='actif' AND auto_renew = 1 AND date_fin <= CURDATE()
    ");
    $stmt->execute();
    $abos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($abos as $a) {
        // calcul nouvelle fin
        if ($a['type'] === 'mensuel') {
            $new_fin = date('Y-m-d', strtotime($a['date_fin']." +1 month"));
        } elseif ($a['type'] === 'annuel') {
            $new_fin = date('Y-m-d', strtotime($a['date_fin']." +1 year"));
        } else {
            $new_fin = date('Y-m-d', strtotime($a['date_fin']." +3 month"));
        }

        // transaction locale
        $tStmt = $masterPdo->prepare("
            UPDATE abonnements_saas
            SET date_debut = date_fin, date_fin = ?, date_modif = NOW()
            WHERE id = ?
        ");
        $tStmt->execute([$new_fin, $a['id']]);

        // créer facture de renouvellement (maintenance)
        $insertF = $masterPdo->prepare("
            INSERT INTO saas_factures (client_id, abonnement_id, montant, description, statut, date_creation, date_echeance)
            VALUES (?, ?, ?, 'Renouvellement automatique', 'impayé', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 DAY))
        ");
        $insertF->execute([$a['client_id'], $a['id'], $a['prix_maintenance']]);

        

        $factureId = $masterPdo->lastInsertId();
        logCron("Renouvelé : abo_id={$a['id']} client_id={$a['client_id']} facture_id={$factureId}");

        // Option : envoyer WhatsApp/email
        // Récupérer contact client (depuis clients_saas)
        $cStmt = $masterPdo->prepare("SELECT * FROM clients_saas WHERE id = ?");
        $cStmt->execute([$a['client_id']]);
        $client = $cStmt->fetch(PDO::FETCH_ASSOC);

        if ($client) {
            $phone = $client['telephone'] ?? null;
            $message = "Bonjour {$client['societe']},\nUne nouvelle facture (#{$factureId}) de {$a['prix_maintenance']} FCFA a été générée pour le renouvellement de votre abonnement.";
            if ($phone) {
                sendWhatsApp($phone, $message);
                logCron("WhatsApp envoyé à {$phone} pour facture {$factureId}");
            }
            // Insert notification dans la BDD tenant (optionnel)
            if (!empty($client['database_name'])) {
                try {
                    $tenantPdo = new PDO(
                        "mysql:host=127.0.0.1;dbname={$client['database_name']};charset=utf8mb4",
                        $GLOBALS['MASTER_DB_USER'],
                        $GLOBALS['MASTER_DB_PASS'],
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    // create notification row (assure-toi que table notifications existe)
                    $tn = $tenantPdo->prepare("INSERT INTO saas_notifications (title, message, type) VALUES (?, ?, ?)");
                    $tn->execute(["Nouvelle facture", "Votre facture #{$factureId} de {$a['prix_maintenance']} FCFA est disponible.", "info"]);
                    logCron("Notification insérée client {$client['database_name']} pour facture {$factureId}");
                } catch (Exception $e) {
                    logCron("Impossible écrire notification tenant {$client['database_name']}: " . $e->getMessage());
                }
            }
        }
    }

    /****************************************************
     * 2) FACTURES MENSUELLES (maintenance) — exécution le 1er du mois
     ****************************************************/
    if (intval(date('d')) === 1) {
        $stmt = $masterPdo->prepare("SELECT * FROM abonnements_saas WHERE statut='actif'");
        $stmt->execute();
        $abosAll = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($abosAll as $a) {
            // vérifier si facture déjà créée ce mois
            $exists = $masterPdo->prepare("
                SELECT COUNT(*) FROM saas_factures
                WHERE abonnement_id = ? AND YEAR(date_creation)=YEAR(CURDATE()) AND MONTH(date_creation)=MONTH(CURDATE())
            ");
            $exists->execute([$a['id']]);
            if ($exists->fetchColumn() == 0) {
                $masterPdo->prepare("
                    INSERT INTO saas_factures (client_id, abonnement_id, montant, description, statut, date_creation, date_echeance)
                    VALUES (?, ?, ?, 'Maintenance mensuelle', 'impayé', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 DAY))
                ")->execute([$a['client_id'], $a['id'], $a['prix_maintenance']]);
                logCron("Facture mensuelle générée abo_id={$a['id']}");
            }
        }
    }

    /****************************************************
     * 3) ALERTE AVANT EXPIRATION (J-5)
     ****************************************************/
    $stmt = $masterPdo->prepare("
        SELECT a.*, c.email, c.societe, c.telephone, c.database_name
        FROM abonnements_saas a
        JOIN clients_saas c ON c.id = a.client_id
        WHERE a.date_fin = DATE_ADD(CURDATE(), INTERVAL 5 DAY)
    ");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
        $message = "Bonjour {$a['societe']}, votre abonnement expire le {$a['date_fin']} (dans 5 jours). Pensez à renouveler.";
        if (!empty($a['telephone'])) { sendWhatsApp($a['telephone'], $message); }
        // insert notification tenant (same pattern as above)
        if (!empty($a['database_name'])) {
            try {
                $tenantPdo = new PDO("mysql:host=127.0.0.1;dbname={$a['database_name']};charset=utf8mb4",
                    $GLOBALS['MASTER_DB_USER'], $GLOBALS['MASTER_DB_PASS'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $tn = $tenantPdo->prepare("INSERT INTO notifications (title, message, level) VALUES (?, ?, ?)");
                $tn->execute(["Expiration à venir", $message, "warning"]);
            } catch (Exception $e) {
                logCron("Notif tenant failed for {$a['database_name']}: ".$e->getMessage());
            }
        }
        logCron("Alerte expiration envoyée à {$a['email']} (Client {$a['societe']})");
    }

    /****************************************************
     * 4) ALERTE FACTURES IMPAYÉES (> date_echeance)
     ****************************************************/
    $stmt = $masterPdo->prepare("
        SELECT f.*, c.email, c.societe, c.telephone, c.database_name
        FROM saas_factures f
        JOIN clients_saas c ON c.id = f.client_id
        WHERE f.statut='impayé' AND f.date_echeance < CURDATE()
    ");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
        $message = "Bonjour {$f['societe']}, votre facture #{$f['id']} de {$f['montant']} FCFA est en retard (échéance: {$f['date_echeance']}).";
        if (!empty($f['telephone'])) sendWhatsApp($f['telephone'], $message);
        if (!empty($f['database_name'])) {
            try {
                $tenantPdo = new PDO("mysql:host=127.0.0.1;dbname={$f['database_name']};charset=utf8mb4",
                    $GLOBALS['MASTER_DB_USER'], $GLOBALS['MASTER_DB_PASS'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $tn = $tenantPdo->prepare("INSERT INTO notifications (title, message, level) VALUES (?, ?, ?)");
                $tn->execute(["Facture impayée", $message, "danger"]);
            } catch (Exception $e) {
                logCron("Notif tenant failed for {$f['database_name']}: ".$e->getMessage());
            }
        }
        logCron("Alerte facture impayée — Facture #{$f['id']} — Client {$f['societe']}");
    }

    /****************************************************
     * 5) SUSPENSION AUTOMATIQUE (> 10 jours de retard)
     ****************************************************/
    $stmt = $masterPdo->prepare("
        SELECT DISTINCT client_id
        FROM saas_factures
        WHERE statut='impayé' AND DATEDIFF(CURDATE(), date_echeance) > 10
    ");
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $clientId) {
        $masterPdo->prepare("UPDATE clients_saas SET statut='suspendu' WHERE id=?")->execute([$clientId]);
        logCron("Client suspendu (#$clientId)");
        // Optionnel : insert notification tenant and/or send WhatsApp to admin
    }

    $masterPdo->commit();
    logCron("=== CRON terminé OK ===");
} catch (Exception $e) {
    $masterPdo->rollBack();
    logCron("CRON ERROR: " . $e->getMessage());
    echo "CRON ERROR: " . $e->getMessage();
}

echo "CRON OK\n";
