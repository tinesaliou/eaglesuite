<?php


declare(strict_types=1);

/* ------------------------------
   CONFIG
------------------------------ */
define('SESSION_TIMEOUT', 900); // 15 minutes
define('MAX_LOGIN_ATTEMPTS', 5);

/* ------------------------------
   SECURE SESSION INIT
------------------------------ */
if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_only_cookies', 1);

    session_start();
}

/* ------------------------------
   FORCE HTTPS (prod only)
------------------------------ */
if (!empty($_SERVER['HTTP_HOST']) && empty($_SERVER['HTTPS'])) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

/* ------------------------------
   AUTH CHECK
------------------------------ */
if (empty($_SESSION['user'])) {
    http_response_code(401);
    die('Accès refusé : utilisateur non authentifié');
}

/* ------------------------------
   SESSION TIMEOUT
------------------------------ */
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > SESSION_TIMEOUT) {
    session_unset();
    session_destroy();
    http_response_code(440);
    die('Session expirée');
}
$_SESSION['LAST_ACTIVITY'] = time();

/* ------------------------------
   USER STATUS CHECK
------------------------------ */
if (!isset($_SESSION['user']['id'], $_SESSION['user']['role_id'], $_SESSION['user']['actif'])) {
    http_response_code(403);
    die('Session invalide');
}

if ($_SESSION['user']['actif'] != 1) {
    http_response_code(403);
    die('Compte désactivé');
}

/* ------------------------------
   ROLE HELPERS
------------------------------ */
function require_role(array $allowedRoles): void
{
    if (!in_array($_SESSION['user']['role_id'], $allowedRoles, true)) {
        http_response_code(403);
        die('Accès refusé : droits insuffisants');
    }
}

/* ------------------------------
   CSRF PROTECTION
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['_csrf']) || empty($_SESSION['_csrf'])) {
        http_response_code(403);
        die('CSRF manquant');
    }

    if (!hash_equals($_SESSION['_csrf'], $_POST['_csrf'])) {
        http_response_code(403);
        die('CSRF invalide');
    }
}

/* ------------------------------
   CSRF TOKEN GENERATOR
------------------------------ */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/* ------------------------------
   INPUT SANITIZER
------------------------------ */
function input(string $key, int $filter = FILTER_SANITIZE_SPECIAL_CHARS)
{
    return filter_input(INPUT_POST, $key, $filter);
}

/* ------------------------------
   AUDIT LOG
------------------------------ */
function audit_log(PDO $pdo, string $action, ?int $target_id = null): void
{
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (utilisateur_id, action, objet_type,objet_id, detail,ip, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $_SESSION['user']['id'],
        $action,
        $objet_type,
        $objet_id,
        $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        
    ]);
}
