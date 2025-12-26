<?php
require_once __DIR__ . '/../../config/db.php';

// --- CONFIG --- //
$jasperstarter = __DIR__ . '/utils/jasperstarter8.bat';
$output_dir = __DIR__ . '/exports/achats';
if (!is_dir($output_dir)) mkdir($output_dir, 0777, true);

// --- INPUT (POST) --- //
$type_rapport   = $_POST['type_rapport'] ?? '';
$date_debut_raw = trim($_POST['date_debut'] ?? '');
$date_fin_raw   = trim($_POST['date_fin'] ?? '');
$fournisseur_id = trim($_POST['fournisseur_id'] ?? '');
$format         = ($_POST['format'] ?? 'pdf') === 'xls' ? 'xls' : 'pdf';


// --- VALIDATION --- //
if (empty($type_rapport)) {
    die("⚠️ Type de rapport non défini.");
}

// --- MAPPING DES FICHIERS --- //
$rapport_files = [
    'journal_achats'      => 'achats/journal_achats.jasper',
    'facture_fournisseur' => 'achats/facture_fournisseur.jasper',
    'bon_commande_fournisseur' => 'achats/bon_commande_fournisseur.jasper',
    'bon_reception'       => 'achats/bon_reception.jasper',
    'etat_fournisseurs'   => 'achats/etat_fournisseurs.jasper',
    'fiche_fournisseur'   => 'achats/fiche_fournisseur.jasper',
];

if (!isset($rapport_files[$type_rapport])) {
    die("❌ Type de rapport inconnu : $type_rapport");
}

$rapport_path = __DIR__ . '/' . $rapport_files[$type_rapport];
if (!file_exists($rapport_path)) {
    die("❌ Fichier rapport introuvable : $rapport_path");
}

// --- PARAMÈTRES BD --- //

$db_port = "3306";

// --- GESTION DES DATES --- //
//  Dates pour filtrage SQL (toujours valides)
$sql_date_debut = $date_debut_raw !== '' ? $date_debut_raw : '1900-01-01';
$sql_date_fin   = $date_fin_raw   !== '' ? $date_fin_raw   : date('Y-m-d');

//  Dates pour affichage (peuvent rester vides)
$param_date_debut = $date_debut_raw;
$param_date_fin   = $date_fin_raw;

// Fournisseur : peut être vide
$fournisseur_id = ($fournisseur_id === '' ? null : $fournisseur_id);

// --- RÉCUPÉRATION LOGO ENTREPRISE (depuis la BD) --- //
$query = $conn->query("SELECT logo FROM entreprise LIMIT 1");
$entreprise = $query->fetch(PDO::FETCH_ASSOC);
$entreprise_logo = $entreprise['logo'] ?? null;

$base_path = realpath(__DIR__ . '/../../public');

// Si problème, éviter que $base_path devienne null
if (!$base_path) {
    $base_path = $_SERVER['DOCUMENT_ROOT'];
}

$full_logo_path = $base_path . '/' . $entreprise_logo;

// Vérification réelle du fichier
if (!$entreprise_logo || !file_exists($full_logo_path)) {

    // Logo par défaut dans /public/uploads/logo/default.png
    $full_logo_path = $base_path . '/uploads/logo/default.png';
}


// --- NOM DU FICHIER DE SORTIE --- //
$date_now = date('Ymd_His');
$output_file = $output_dir . DIRECTORY_SEPARATOR . "rapport_{$type_rapport}_{$date_now}";

// --- CONSTRUCTION COMMANDE --- //
$ext = $format === 'xls' ? 'xls' : 'pdf';
$cmd = "\"$jasperstarter\" process \"$rapport_path\" -f $ext -o \"$output_file\" "
     . "-t mysql -H $host -P $db_port -n $dbname -u $user -p $pass ";

// --- PARAMÈTRES GÉNÉRAUX --- //
$titre = ucfirst(str_replace('_', ' ', $type_rapport));
$cmd .= "-P TITRE_RAPPORT=\"" . addslashes($titre) . "\" ";
$cmd .= "-P IMAGE_LOGO=\"" . addslashes($full_logo_path) . "\" ";


// --- PARAMÈTRES OPTIONNELS --- //
if ($param_date_debut !== '') {
    $cmd .= "-P date_debut=\"" . addslashes($param_date_debut) . "\" ";
}
if ($param_date_fin !== '') {
    $cmd .= "-P date_fin=\"" . addslashes($param_date_fin) . "\" ";
}
if (!is_null($fournisseur_id)) {
    $cmd .= "-P fournisseur_id=\"" . addslashes($fournisseur_id) . "\" ";
}

// --- DEBUG --- //
file_put_contents(__DIR__ . '/debug_cmd.txt', $cmd . PHP_EOL);

// --- EXÉCUTION --- //
exec($cmd . " 2>&1", $output, $return_var);

// --- ERREUR ? --- //
if ($return_var !== 0) {
    echo "<h3>⚠️ Erreur lors de la génération du rapport :</h3>";
    echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
    echo "<p><b>Commande exécutée :</b></p><pre>" . htmlspecialchars($cmd, ENT_QUOTES, 'UTF-8') . "</pre>";
    exit;
}

// --- LECTURE DU FICHIER GÉNÉRÉ --- //
$generated_file = "$output_file.$ext";
if (!file_exists($generated_file)) {
    echo "<div style='color:red'>❌ Fichier non trouvé après génération : $generated_file</div>";
    exit;
}

// --- ENVOI AU NAVIGATEUR --- //
if ($ext === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($generated_file) . '"');
    header('Content-Length: ' . filesize($generated_file));
    readfile($generated_file);
} else {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . basename($generated_file) . '"');
    header('Content-Length: ' . filesize($generated_file));
    readfile($generated_file);
}
exit;
?>
