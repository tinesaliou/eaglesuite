<?php

// Empêcher tout warning d’être affiché (sinon TCPDF plante)
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set("display_errors", "0");


ini_set('max_execution_time', 0);
set_time_limit(0);
ignore_user_abort(true);

require_once __DIR__ . '/init.php';
require_admin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';



/* ----------------------------------------------------
   UTILS
---------------------------------------------------- */
function random_password($len = 12)
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    $s = '';
    for ($i = 0; $i < $len; $i++) {
        $s .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $s;
}

function importSqlFile(PDO $pdo, string $sqlFile)
{
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL template introuvable: $sqlFile");
    }

    $content = file_get_contents($sqlFile);

    // Supprimer /* ... */
    $content = preg_replace('/\/\*.*?\*\//s', '', $content);
    // Supprimer -- ...
    $content = preg_replace('/^\s*--.*$/m', '', $content);

    $queries = preg_split('/;\s*$/m', $content);

    foreach ($queries as $query) {
        $q = trim($query);
        if ($q === '') continue;

        try {
            $pdo->exec($q);
        } catch (Exception $e) {
            throw new Exception("Erreur SQL import : " . $e->getMessage() . "\nRequête :\n" . $q);
        }
    }
}

function recursiveCopy($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst, 0775, true);

    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') continue;

        $srcFile = "$src/$file";
        $dstFile = "$dst/$file";

        if (is_dir($srcFile)) {
            recursiveCopy($srcFile, $dstFile);
        } else {
            copy($srcFile, $dstFile);
        }
    }

    closedir($dir);
}

function replaceTenantPlaceholders($dir, $tenantName) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($files as $file) {
        if ($file->isFile()) {

            $path = $file->getRealPath();

            // lire contenu
            $contents = file_get_contents($path);

            // remplacer le placeholder
            $contents = str_replace('{{TENANT_DIR}}', $tenantName, $contents);

            // réécrire fichier
            file_put_contents($path, $contents);
        }
    }
}


/* ----------------------------------------------------
   CREATE CLIENT SAAS
---------------------------------------------------- */
if ($action === 'create_client_saas') {

    global $masterPdo, $MASTER_DB_HOST, $MASTER_DB_USER, $MASTER_DB_PASS;

    $societe    = trim($_POST['societe'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $telephone      = trim($_POST['telephone'] ?? '');
    $subdomain  = preg_replace('/[^a-z0-9-]/', '', strtolower(trim($_POST['subdomain'] ?? '')));
    $pack       = $_POST['pack'] ?? 'full';
    $abonnement = $_POST['abonnement'] ?? 'mensuel';
    $duration   = intval($_POST['duration_days'] ?? 30);
    $expiration = date('Y-m-d', strtotime("+$duration days"));

    if ($societe === '' || $subdomain === '') {
        die("Veuillez remplir tous les champs requis.");
    }

    /* 1) Nom base */
    $dbName = "eagle_client_" . $subdomain;


    $adminUsername  = strtolower(str_replace(' ', '', $societe)); // ex: "Eagle Suite" -> "eaglesuite"
    $adminEmail = filter_var($email, FILTER_VALIDATE_EMAIL)
    ? $email
    : $adminUsername . "@client.local"; 
    
    $adminPassPlain =$adminUsername . "@".$adminUsername;
    $adminPassHash  = password_hash($adminPassPlain, PASSWORD_DEFAULT);


    try {

        /* 3) Création base */
        $masterPdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

        /* 4) Import template */
        $pdoNew = new PDO(
            "mysql:host=$MASTER_DB_HOST;dbname=$dbName;charset=utf8mb4",
            $MASTER_DB_USER,
            $MASTER_DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $sqlFile = __DIR__ . "/../templates/eagle_template.sql";
        importSqlFile($pdoNew, $sqlFile);

        /* 5) Insérer admin tenant */
        $stmt = $pdoNew->prepare("
            INSERT INTO utilisateurs (nom, email,username, mot_de_passe, role_id, actif, created_at)
            VALUES (?, ?, ?, ?, 1, 1, NOW())
        ");
        $stmt->execute([$societe,$adminEmail,$adminUsername,$adminPassHash]);

        /* 6) Copier app modèle */
        $tenantDir   = __DIR__ . "/../../$dbName";
        $templateDir = __DIR__ . "/../template_app";

        recursiveCopy($templateDir, $tenantDir);

        /* Remplacer les placeholders dans toutes les pages */
        replaceTenantPlaceholders($tenantDir, $dbName);

        /* 7) Générer config tenant */
        $dbConfig = "<?php
            \$host = '$MASTER_DB_HOST';
            \$dbname = '$dbName';
            \$user = '$MASTER_DB_USER';
            \$pass = '$MASTER_DB_PASS';

            try {
                \$conn = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8mb4\", \$user, \$pass);
                \$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException \$e) {
                die('Erreur tenant : ' . \$e->getMessage());
            }
        ?>";

        file_put_contents("$tenantDir/config/db.php", $dbConfig);

        /* 8) Enregistrer master */
        $stmt = $masterPdo->prepare("
            INSERT INTO clients_saas (societe,email,telephone,subdomain,database_name,database_user,database_password,pack,abonnement,expiration,statut,date_creation)
            VALUES (?,?,?,?,?,?,?,?,?, ?,'suspendu',NOW())
        ");

        $stmt->execute([
            $societe, $email, $telephone,$subdomain,
            $dbName, $MASTER_DB_USER, $MASTER_DB_PASS,
            $pack, $abonnement,$expiration
        ]);

        /* 9) Redirect success */
        header("Location: /eaglesuite/admin/client_created.php?email=$adminEmail&pass=$adminPassPlain&sub=$subdomain");
        exit;

    } catch (Exception $e) {
        die("Erreur création client : " . $e->getMessage());
    }
}

if ($action === 'update_client_saas') {
    $id = intval($_POST['id']);
    $societe = trim($_POST['societe'] ?? '');
    $subdomain = trim($_POST['subdomain'] ?? '');
    $pack = $_POST['pack'] ?? 'full';
    $abonnement = $_POST['abonnement'] ?? 'mensuel';

    $stmt = $masterPdo->prepare("
        UPDATE clients_saas
        SET societe=?, subdomain=?, pack=?, abonnement=?, date_modif=NOW()
        WHERE id=?
    ");

    $stmt->execute([$societe, $subdomain, $pack, $abonnement, $id]);

    header("Location: /eaglesuite/admin/index.php?page=clients");
    exit;
}


if ($action === 'delete_client_saas') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        header("Location: /eaglesuite/admin/?page=clients");
        exit;

    }

    // récupérer dossier + db
    $stmt = $masterPdo->prepare("SELECT database_name FROM clients_saas WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $dbName = $stmt->fetchColumn();

    // supprimer client dans master
    $masterPdo->prepare("DELETE FROM clients_saas WHERE id=?")->execute([$id]);

    // supprimer base SQL
    try { $masterPdo->exec("DROP DATABASE IF EXISTS `$dbName`"); } catch (Exception $e) {}

    // supprimer dossier tenant
    $dir = __DIR__ . "/../../" . $dbName;
    if (is_dir($dir)) {
        shell_exec("rm -rf " . escapeshellarg($dir));  // Linux / macOS
        // ou version Windows :
        // exec("rmdir /s /q " . escapeshellarg($dir));
    }

    header("Location: /eaglesuite/admin/?page=clients");
    exit;
}

// Marquer facture payé
if ($_POST['action'] === 'mark_facture_paid') {
    $id = intval($_POST['id']);
    // update facture
    $stmt = $masterPdo->prepare("UPDATE saas_factures SET statut='payé', date_paiement=NOW(), payment_method='manual' WHERE id=?");
    $stmt->execute([$id]);

    // create reçu simple (can reuse facture_pdf route to render PDF but save file)
    // récupérer facture
    $stmt = $masterPdo->prepare("SELECT f.*, c.societe, c.email, c.telephone FROM saas_factures f JOIN clients_saas c ON c.id = f.client_id WHERE f.id=?");
    $stmt->execute([$id]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);

    // générer reçu PDF et le sauvegarder
    require_once __DIR__ . '/vendor/autoload.php';
    $pdf = new \TCPDF();
    $pdf->AddPage();
    $html = "<h2>Reçu de paiement — Facture #{$f['id']}</h2>";
    $html .= "<p>Client: {$f['societe']}</p>";
    $html .= "<p>Montant: ".number_format($f['montant'],0,',',' ')." F CFA</p>";
    $html .= "<p>Date: ".date('Y-m-d H:i:s')."</p>";
    $pdf->writeHTML($html, true, false, true, false, '');
    $outPath = __DIR__ . "/factures/receipt_{$f['id']}.pdf";
    @mkdir(dirname($outPath), 0755, true);
    $pdf->Output($outPath, 'F');

    // send WhatsApp & email
    require_once __DIR__ . '/lib/whatsapp.php';
    $msg = "Bonjour {$f['societe']},\nVotre paiement pour la facture #{$f['id']} de ".number_format($f['montant'],0,',',' ')." F CFA a été reçu. Merci.";
    sendWhatsApp($f['telephone'], $msg);

    if (!empty($f['email'])) {
        // envoi email : attache $outPath (à implémenter via PHPMailer si tu veux)
        // mail($f['email'], "Reçu paiement #{$f['id']}", "Bonjour, votre paiement a été reçu. Voir pièce jointe.");
    }

    header("Location: /eaglesuite/admin/index.php?page=facturation&paid=1");
    exit;
}

if ($action === 'send_invoice') {

    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) { die("Facture invalide"); }

    // Charger facture + client
    $stmt = $masterPdo->prepare("
        SELECT f.*, c.email, c.societe 
        FROM saas_factures f 
        JOIN clients_saas c ON c.id = f.client_id 
        WHERE f.id=?
    ");
    $stmt->execute([$id]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$f) die("Facture introuvable");

    // TODO: envoi réel via PHPMailer
    // mail($f['email'], "Votre facture", "Merci...");

    header("Location: /eaglesuite/admin/index.php?page=facturation&sent=1");
    exit;
}

// Suspendre client
if ($action === 'suspend_client') {
    $id = intval($_POST['id']);
    $masterPdo->prepare("UPDATE clients_saas SET statut='suspendu' WHERE id=?")->execute([$id]);
    header("Location: /eaglesuite/admin/index.php?page=suspensions");
    exit;
}

// Réactiver client
if ($action === 'reactivate_client') {
    $id = intval($_POST['id']);
    $masterPdo->prepare("UPDATE clients_saas SET statut='actif' WHERE id=?")->execute([$id]);
    header("Location: /eaglesuite/admin/index.php?page=suspensions");
    exit;
}

// Générer facture PDF (appel direct)
if ($action === 'facture_pdf' && !empty($_GET['id'])) {
    require_once __DIR__ . '/vendor/autoload.php'; // TCPDF
    $id = intval($_GET['id']);
    $stmt = $masterPdo->prepare("SELECT f.*, c.societe, c.telephone, c.email, c.subdomain FROM saas_factures f JOIN clients_saas c ON c.id = f.client_id WHERE f.id=?");
    $stmt->execute([$id]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$f) die("Facture non trouvée");

    // simple HTML
    $html = '<h1>Facture #' . $f['id'] . '</h1>';
    $html .= '<p><strong>Client :</strong> ' . htmlspecialchars($f['societe']) . ' (' . htmlspecialchars($f['subdomain']) . ')</p>';
    $html .= '<p><strong>Montant :</strong> ' . number_format($f['montant'],0,',',' ') . ' F CFA</p>';
    $html .= '<p><strong>Émise :</strong> ' . $f['date_emission'] . '</p>';
    $html .= '<p><strong>Échéance :</strong> ' . ($f['date_echeance'] ?? '-') . '</p>';

    // QR code (lien de paiement)
    $payUrl = "https://wa.me/".(preg_replace('/\D/','',$f['telephone']?:'221778006335'))."?text=".urlencode("Paiement facture #".$f['id']." montant ".number_format($f['montant'],0,',',' ')." F CFA");
    $html .= '<p><strong>Paiement rapide :</strong> <a href="'.$payUrl.'">Payer via WhatsApp</a></p>';

    // Generate PDF
    $pdf = new \TCPDF();
    $pdf->SetCreator('EagleSuite SaaS');
    $pdf->SetAuthor('EagleSuite');
    $pdf->SetTitle('Facture_'.$f['id']);
    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');
    // Add QR image
    $style = ['border'=>0,'vpadding'=>'auto','hpadding'=>'auto','fgcolor'=>[0,0,0],'bgcolor'=>false];
    $qr = $pdf->write2DBarcode($payUrl, 'QRCODE,H', 150, 200, 40, 40, $style, 'N');
    $pdf->Output("facture_{$f['id']}.pdf", 'I'); // inline
    exit;
}

if ($action === 'send_invoice') {

    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) { die("Facture invalide"); }

    // Charger facture + client
    $stmt = $masterPdo->prepare("
        SELECT f.*, c.email, c.societe 
        FROM saas_factures f 
        JOIN clients_saas c ON c.id = f.client_id 
        WHERE f.id=?
    ");
    $stmt->execute([$id]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$f) die("Facture introuvable");

    // TODO: envoi réel via PHPMailer
    // mail($f['email'], "Votre facture", "Merci...");

    header("Location: /eaglesuite/admin/index.php?page=facturation&sent=1");
    exit;
}

if ($action === 'impersonate' && !empty($_GET['client'])) {

    $sub = preg_replace('/[^a-z0-9-]/', '', strtolower($_GET['client']));

    // récupérer info client
    $stmt = $masterPdo->prepare("SELECT database_name FROM clients_saas WHERE subdomain = ? LIMIT 1");
    $stmt->execute([$sub]);
    $dbName = $stmt->fetchColumn();

    if (!$dbName) {
        die("Client introuvable : $sub");
    }

    $tenantFolder = $dbName;

    // Rediriger vers login du tenant
    header("Location: http://localhost/{$tenantFolder}/login.php");
    exit;
}


/******************************************************
 *  WEBHOOK PAIEMENT (Orange Money / Wave / PayTech)
 *  URL : /eaglesuite/admin/actions.php?action=webhook_payment
 ******************************************************/
// dans admin/actions.php (assure-toi que $masterPdo est disponible)
if (($action ?? '') === 'webhook_payment' || ($_GET['action'] ?? '') === 'webhook_payment') {

    // Lire payload JSON ou GET/POST (simulation)
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];

    $payload['invoice_id'] = $payload['invoice_id'] ?? ($_GET['invoice_id'] ?? null);
    $payload['amount']     = $payload['amount'] ?? ($_GET['amount'] ?? null);
    $payload['status']     = $payload['status'] ?? ($_GET['status'] ?? null);
    $payload['transaction_ref'] = $payload['transaction_ref'] ?? ($_GET['transaction_ref'] ?? null);
    $payload['method'] = $payload['method'] ?? ($_GET['method'] ?? null);

    if (empty($payload['invoice_id']) || empty($payload['status'])) {
        http_response_code(400);
        echo "INVALID_PAYLOAD";
        exit;
    }

    $invoiceId = intval($payload['invoice_id']);
    $status    = strtoupper($payload['status']);

    // uniquement si payé
    if ($status === 'SUCCESS' || $status === 'PAID') {

        // récupérer facture
        $stmt = $masterPdo->prepare("SELECT * FROM saas_factures WHERE id=? LIMIT 1");
        $stmt->execute([$invoiceId]);
        $f = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$f) { echo "INV_NOT_FOUND"; exit; }

        $masterPdo->beginTransaction();

        try {

            // 1) mise à jour facture
            $masterPdo->prepare("
                UPDATE saas_factures 
                SET statut='payé', date_paiement=NOW(), ref_paiement=?, mode_paiement=? 
                WHERE id=?
            ")->execute([
                $payload['transaction_ref'],
                $payload['method'],
                $invoiceId
            ]);

            // 2) insertion paiement
            $masterPdo->prepare("
                INSERT INTO saas_payments 
                (facture_id, client_id, amount, method, transaction_ref, meta, created_at)
                VALUES (?,?,?,?,?,?,NOW())
            ")->execute([
                $invoiceId,
                $f['client_id'],
                $payload['amount'] ?? $f['montant'],
                $payload['method'] ?? 'manual',
                $payload['transaction_ref'] ?? null,
                json_encode($payload)
            ]);

            // 3) rendre le client ACTIF si suspens
            $masterPdo->prepare("
                UPDATE clients_saas SET statut='actif' WHERE id=?
            ")->execute([$f['client_id']]);

            // 4) récupérer infos du client
            $stmt2 = $masterPdo->prepare("
                SELECT * FROM clients_saas WHERE id=? LIMIT 1
            ");
            $stmt2->execute([$f['client_id']]);
            $c = $stmt2->fetch(PDO::FETCH_ASSOC);

            // 5) ajouter notification dans la DB client
            if (!empty($c['database_name'])) {

                $stmt3 = $masterPdo->prepare("
                    SELECT database_user, database_password 
                    FROM clients_saas 
                    WHERE database_name=? LIMIT 1
                ");
                $stmt3->execute([$c['database_name']]);
                $creds = $stmt3->fetch(PDO::FETCH_ASSOC);

                if ($creds) {
                    $tenantPdo = new PDO(
                        "mysql:host={$MASTER_DB_HOST};dbname={$c['database_name']};charset=utf8mb4",
                        $creds['database_user'],
                        $creds['database_password'],
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );

                    $tenantPdo->prepare("
                        INSERT INTO notifications (title,message,type,created_at)
                        VALUES ('Paiement reçu', ?, 'success', NOW())
                    ")->execute([
                        "Votre facture #{$invoiceId} a été réglée."
                    ]);
                }
            }

            // 6) Envoi WhatsApp
            if (!empty($c['telephone'])) {
                sendWhatsApp(
                    $c['telephone'],
                    "Bonjour {$c['societe']}, votre paiement de {$payload['amount']} FCFA (Facture #{$invoiceId}) a été confirmé."
                );
            }

            $masterPdo->commit();
            echo "OK_PAID";
            exit;

        } catch (Exception $e) {
            $masterPdo->rollBack();
            echo "ERR: ".$e->getMessage();
            exit;
        }
    }

    echo "IGNORED";
    exit;
}

// create_abonnement
if ($_POST['action'] === 'create_abonnement') {

    $client           = $_POST['client_id'];
    $type             = $_POST['type'];
    $prixAcquisition  = $_POST['prix_acquisition'];
    $prixMaintenance  = $_POST['prix_maintenance'];
    $autoRenew        = $_POST['auto_renew'];
    $dateDebut        = $_POST['date_debut'];
    $dateFin          = $_POST['date_fin'];
    $notes            = $_POST['notes'];

    // Créer l'abonnement
    $stmt = $masterPdo->prepare("
        INSERT INTO abonnements_saas 
        (client_id, type, prix_acquisition, prix_maintenance, auto_renew, date_debut, date_fin, statut, notes, date_creation)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'actif', ?, NOW())
    ");
    $stmt->execute([
        $client, $type, $prixAcquisition, $prixMaintenance,
        $autoRenew, $dateDebut, $dateFin, $notes
    ]);

    $aboId = $masterPdo->lastInsertId();

    // ==== créer facture acquisition ====
    if ($prixAcquisition > 0) {

        $ref        = "ACQ-".$client."-".time();
        $echeance   = date('Y-m-d', strtotime("$dateDebut +5 days"));

        $masterPdo->prepare("
            INSERT INTO saas_factures (client_id, abonnement_id, reference, montant, montant_acquisition, statut, date_creation, date_echeance)
            VALUES (?, ?, ?, ?, ?, 'impayé', NOW(), ?)
        ")->execute([
            $client,
            $aboId,
            $ref,
            $prixAcquisition,
            $prixAcquisition,
            $echeance
        ]);
    }

    // ==== créer facture maintenance (si abonnement mensuel) ====
    if ($prixMaintenance > 0) {

        $refM = "MNT-".$client."-".time();
        $echeanceM = date('Y-m-d', strtotime("$dateDebut +5 days"));

        $masterPdo->prepare("
            INSERT INTO saas_factures (client_id, abonnement_id, reference, montant, montant_maintenance, statut, date_creation, date_echeance)
            VALUES (?, ?, ?, ?, ?, 'impayé', NOW(), ?)
        ")->execute([
            $client,
            $aboId,
            $refM,
            $prixMaintenance,
            $prixMaintenance,
            $echeanceM
        ]);
    }

    header("Location: /eaglesuite/admin/pages/abonnements.php?success=1");
    exit;
}



// update_abonnement
if ($action === 'update_abonnement') {
    $id = intval($_POST['id']);
    $type = $_POST['type'] ?? 'mensuel';
    $prix = floatval($_POST['prix'] ?? 0);
    $date_debut = $_POST['date_debut'] ?? null;
    $date_fin = $_POST['date_fin'] ?? null;
    $auto_renew = isset($_POST['auto_renew']) ? intval($_POST['auto_renew']) : 0;
    $notes = $_POST['notes'] ?? null;

    $stmt = $masterPdo->prepare("UPDATE abonnements_saas SET type=?, prix=?, date_debut=?, date_fin=?, auto_renew=?, notes=?, date_modif=NOW() WHERE id=?");
    $stmt->execute([$type, $prix, $date_debut, $date_fin, $auto_renew, $notes, $id]);
    header('Location: /eaglesuite/admin/index.php?page=abonnements');
    exit;
}

// delete_abonnement
if ($action === 'delete_abonnement') {
    $id = intval($_POST['id']);
    $masterPdo->prepare("DELETE FROM abonnements_saas WHERE id=?")->execute([$id]);
    header('Location: /eaglesuite/admin/index.php?page=abonnements');
    exit;
}

// create_facture
if ($action === 'create_facture') {
    $client_id = intval($_POST['client_id']);
    $montant = floatval($_POST['montant']);
    $description = $_POST['description'] ?? null;

    $stmt = $masterPdo->prepare("INSERT INTO saas_factures (client_id, montant, description, statut, date_creation) VALUES (?, ?, ?, 'impayé', NOW())");
    $stmt->execute([$client_id, $montant, $description]);

    header('Location: /eaglesuite/admin/index.php?page=facturation');
    exit;
}

// pay_facture
if ($action === 'pay_facture') {
    $facture_id = intval($_POST['facture_id']);
    $mode = $_POST['mode'] ?? 'Unknown';
    $reference = $_POST['reference'] ?? null;

    // Mark invoice paid & insert paiement log
    $masterPdo->beginTransaction();
    try {
        $masterPdo->prepare("UPDATE clients_saas SET statut='actif'WHERE id = ?")->execute([$clientId]);
        $masterPdo->prepare("UPDATE saas_factures SET statut='payé', date_paie=NOW() WHERE id=?")->execute([$facture_id]);
        $masterPdo->prepare("INSERT INTO saas_payments (facture_id, mode, reference, date_creation) VALUES (?,?,?,NOW())")
            ->execute([$facture_id, $mode, $reference]);
        $masterPdo->commit();
    } catch (Exception $e) {
        $masterPdo->rollBack();
        die("Erreur paiement : " . $e->getMessage());
    }

    header('Location: /eaglesuite/admin/index.php?page=facturation');
    exit;
}

// toggle_auto_renew
if ($action === 'toggle_auto_renew') {
    $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
    $stmt = $masterPdo->prepare("UPDATE abonnements_saas SET auto_renew = NOT auto_renew WHERE id=?");
    $stmt->execute([$id]);
    header('Location: /eaglesuite/admin/index.php?page=renouvellement');
    exit;
}

// renew_now
if ($action === 'renew_now') {
    $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

    // Exemple simple : créer une facture pour l'abonnement et l'envoyer (ou marquer payé si tu veux)
    $stmt = $masterPdo->prepare("SELECT * FROM abonnements_saas WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $abo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($abo) {
        // créer facture
        $masterPdo->prepare("INSERT INTO saas_factures (client_id, montant, description, statut, date_creation) VALUES (?,?,?,?,NOW())")
            ->execute([$abo['client_id'], $abo['prix'], 'Renouvellement automatique abonnement #' . $abo['id'], 'impayé']);
    }

    header('Location: /eaglesuite/admin/index.php?page=renouvellement');
    exit;
}

