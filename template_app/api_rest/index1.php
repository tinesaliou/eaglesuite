<?php
// rest_api/index.php
declare(strict_types=1);

require_once __DIR__ . '/config/headers.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers.php';

// charger autoload composer si existant
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// --- déterminer le module depuis l'URI ---
// Supporte : /rest_api/fournisseurs?action=...  OR index.php?module=...
$module = null;
$action = $_GET['action'] ?? null;

// Méthode 1: param module GET (compatibilité)
if (!empty($_GET['module'])) {
    $module = preg_replace('/[^a-z0-9_]/i', '', $_GET['module']);
}

// Méthode 2: PATH_INFO / REQUEST_URI
if (!$module) {
    $scriptName = basename(__FILE__);
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // retirer le segment jusqu'à rest_api
    $basePos = strpos($requestUri, '/' . basename(__DIR__));
    if ($basePos !== false) {
        $sub = substr($requestUri, $basePos + strlen('/' . basename(__DIR__) . '/'));
        $parts = explode('/', trim($sub, '/'));
        if (!empty($parts[0])) {
            // module = premier segment
            $module = preg_replace('/[^a-z0-9_]/i', '', $parts[0]);
        }
    }
}

// action par défaut
$action = $action ?? ($_GET['action'] ?? 'list');

// validation simple
if (!$module) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Module manquant dans l'URL. Exemple: /rest_api/produits?action=list"]);
    exit;
}

$moduleFile = __DIR__ . '/modules/' . $module . '.php';
if (!file_exists($moduleFile)) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Module introuvable: $module"]);
    exit;
}

// Connexion DB (Database class dans config/database.php)
$db = new Database();
$conn = $db->getConnection();

// rendre $action disponible via GET (déjà possible)
// définir $conn global pour compatibilité avec modules
$GLOBALS['conn'] = $conn;

// inclure le module (les modules s'attendent à utiliser $conn et require_auth())
require_once $moduleFile;
