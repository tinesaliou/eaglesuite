<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/jasper_helper.php';
require __DIR__ . '/../../../config/db.php';
$config = require __DIR__ . '/config_print.php';

// -----------------------------
// PARAMÈTRES GET
// -----------------------------
$category = $_GET['cat'] ?? 'ventes';
$type     = $_GET['type'] ?? 'facture_client';
$format   = $_GET['format'] ?? 'pdf';
$pageFmt  = $_GET['ticket'] ?? 'a4';
$id       = $_GET['id'] ?? null;

try {

    // -----------------------------
    // LOGO ENTREPRISE (ICI SEULEMENT)
    // -----------------------------
    $query = $conn->query("SELECT logo FROM entreprise LIMIT 1");
    $entreprise = $query->fetch(PDO::FETCH_ASSOC);
    $entreprise_logo = $entreprise['logo'] ?? null;

    $base_path = realpath(__DIR__ . '/../../../public');
    if (!$base_path) {
        $base_path = $_SERVER['DOCUMENT_ROOT'];
    }

    $logoPath = $base_path . '/' . $entreprise_logo;

    if (!$entreprise_logo || !file_exists($logoPath)) {
        $logoPath = $base_path . '/uploads/logo/default.png';
    }

    // Normalisation pour Jasper
    $logoPath = str_replace('\\', '/', $logoPath);

    if (!file_exists($logoPath)) {
        throw new Exception("Logo introuvable : {$logoPath}");
    }

    // -----------------------------
    // PARAMÈTRES JASPER
    // ⚠️ IMAGE_LOGO DOIT EXISTER DANS LE JRXML
    // -----------------------------
    $params = [
        'DOCUMENT_ID' => $id,
        //'IMAGE_LOGO'  => $logoPath
    ];

    // -----------------------------
    // GÉNÉRATION DU RAPPORT
    // -----------------------------
    $outputFile = runJasperReport(
        $category,
        $type,
        $format,
        $params,
        $pageFmt
    );

    if (!file_exists($outputFile)) {
        throw new Exception("Fichier généré introuvable : {$outputFile}");
    }

    // -----------------------------
    // ENVOI AU NAVIGATEUR
    // -----------------------------
    $ext = pathinfo($outputFile, PATHINFO_EXTENSION);
    $mime = ($ext === 'pdf')
        ? 'application/pdf'
        : (($ext === 'xlsx')
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            : 'text/plain');

    header("Content-Type: {$mime}");
    header("Content-Disposition: inline; filename=\"" . basename($outputFile) . "\"");
    header("Content-Length: " . filesize($outputFile));

    readfile($outputFile);
    exit;

} catch (Exception $e) {

    echo "<h3 style='color:red;'>❌ Erreur d'impression</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
