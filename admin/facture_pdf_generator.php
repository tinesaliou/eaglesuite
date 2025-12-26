<?php
// facture_pdf_generator.php
// usage: generate_facture_pdf($masterPdo, $factureId);

function generate_facture_pdf(PDO $masterPdo, $id) {
    // fetch facture + client
    $stmt = $masterPdo->prepare("SELECT f.*, c.societe, c.subdomain FROM saas_factures f JOIN clients_saas c ON c.id=f.client_id WHERE f.id=?");
    $stmt->execute([$id]);
    $f = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$f) {
        http_response_code(404);
        echo "Facture introuvable.";
        exit;
    }

    $logo = __DIR__ . '/../public/icone/eaglesuite_logo.png';
    $reference = htmlspecialchars($f['reference']);
    $client = htmlspecialchars($f['societe']);
    $date = (new DateTime($f['date_emission']))->format('Y-m-d');

    // QR via Google Chart (simple)
    $qrText = urlencode("Facture #{$f['id']} - {$client} - Montant: {$f['montant']} CFA - Réf: {$reference}");
    $qrUrl = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={$qrText}&choe=UTF-8";

    // Si TCPDF présent -> faire PDF
    if (class_exists('TCPDF')) {
        $pdf = new TCPDF('P','mm','A4', true, 'UTF-8', false);
        $pdf->SetCreator('EagleSuite');
        $pdf->SetAuthor('EagleSuite');
        $pdf->SetTitle("Facture #{$f['id']}");
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();

        $html = '
        <table width="100%"><tr>
         <td width="60%"><h2>EagleSuite</h2><p>Facture #: '.$reference.'<br>Date: '.$date.'</p></td>
         <td width="40%" align="right"><img src="'.$logo.'" style="max-width:140px"></td>
        </tr></table>
        <hr>
        <h4>Client</h4>
        <p>'.$client.' ('.$f['subdomain'].')</p>
        <h4>Détails</h4>
        <table border="1" cellpadding="6">
          <tr><th>Description</th><th align="right">Montant</th></tr>
          <tr><td>Abonnement / Maintenance</td><td align="right">'.number_format($f['montant'],0,',',' ').' F CFA</td></tr>
        </table>
        <br>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div>Réf: '.$reference.'</div>
          <div><img src="'.$qrUrl.'" /></div>
        </div>
        ';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output("facture_{$f['id']}.pdf", 'I');
        exit;
    }

    // fallback HTML simple (affichable)
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!doctype html>
    <html><head><meta charset="utf-8"><title>Facture #<?= $f['id'] ?></title></head>
    <body style="font-family:Arial,Helvetica,sans-serif;">
    <h2>EagleSuite - Facture #<?= $reference ?></h2>
    <p><strong>Client:</strong> <?= $client ?> (<?= $f['subdomain'] ?>)</p>
    <p><strong>Date:</strong> <?= $date ?></p>
    <table border="1" cellpadding="6" style="border-collapse:collapse;width:60%">
      <tr><th>Description</th><th>Montant</th></tr>
      <tr><td>Abonnement / Maintenance</td><td style="text-align:right"><?= number_format($f['montant'],0,',',' ') ?> F CFA</td></tr>
    </table>
    <p><img src="<?= $qrUrl ?>" alt="QR"></p>
    </body></html>
    <?php
    exit;
}
