<?php

require __DIR__ . '/../../../config/db.php';

/**
 * Résolution du template Jasper
 */
function getJasperTemplatePath(
    string $category,
    string $report,
    string $format = 'a4'
): string {

    $baseDir = realpath(__DIR__ . "/../{$category}");
    if (!$baseDir) {
        throw new Exception("Dossier catégorie introuvable : {$category}");
    }

    $files = [
        "{$baseDir}/{$report}_{$format}.jasper",
        "{$baseDir}/{$report}.jasper",
    ];

    foreach ($files as $file) {
        if (file_exists($file)) {
            return $file;
        }
    }

    throw new Exception("Modèle Jasper introuvable : {$report}");
}

/**
 * 🚀 Exécution JasperStarter
 * 👉 Aucun logo ici
 * 👉 Aucune logique métier
 */
function runJasperReport(
    string $category,
    string $report,
    string $format = 'pdf',
    array $params = [],
    string $pageFormat = 'a4'
): string {

    require __DIR__ . '/../../../config/db.php';
    $config = require __DIR__ . '/config_print.php';

    global $conn, $host, $dbname, $user, $pass;

    if (!$conn instanceof PDO) {
        throw new Exception("Connexion PDO absente");
    }

    // -----------------------------
    // TEMPLATE
    // -----------------------------
    $jasperFile = getJasperTemplatePath($category, $report, $pageFormat);

    // -----------------------------
    // OUTPUT
    // -----------------------------
    $outputDir = rtrim($config['output_dir'], '/\\') . DIRECTORY_SEPARATOR;
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    $outputFile = $outputDir . "{$report}_" . date('Ymd_His');

    // -----------------------------
    // COMMANDE JASPER
    // -----------------------------
    $cmd = "\"{$config['jasperstarter']}\" process \"{$jasperFile}\""
         . " -o \"{$outputFile}\" -f {$format}"
         . " -t mysql -H {$host} -n {$dbname} -u {$user} -p {$pass}";

    foreach ($params as $key => $value) {
        $cmd .= " -P {$key}=\"" . addslashes($value) . "\"";
    }

    // DEBUG
    file_put_contents(__DIR__ . '/debug_cmd.txt', $cmd . PHP_EOL);

    exec($cmd . " 2>&1", $output, $code);

    file_put_contents(
        __DIR__ . '/debug_jasper_output.txt',
        implode("\n", $output)
    );

    if ($code !== 0) {
        throw new Exception("Erreur JasperStarter :\n" . implode("\n", $output));
    }

    $ext = ($format === 'xls') ? 'xlsx' : $format;
    return "{$outputFile}.{$ext}";
}
