<?php
require_once 'config.php';
/* ----------------------------------------------------------
   1.  Session‑cookie settings (before session_start)
---------------------------------------------------------- */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 86400);

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443
    );

    session_set_cookie_params([
        'lifetime' => 86400,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    }
}

/* ----------------------------------------------------------
   2.  Session‑ID regeneration logic
---------------------------------------------------------- */
function regenerate_session_id_loggedin($pdo)
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $newId = session_create_id();
    session_commit();
    session_id($newId);
    $_SESSION['last_regeneration'] = time();
    return true;
}

function regenerate_session_id_generic()
{
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
    return true;
}

$interval = 3000;
if (
    !isset($_SESSION['last_regeneration'])
    || time() - $_SESSION['last_regeneration'] >= $interval
) {

    isset($_SESSION['user_id'])
        ? regenerate_session_id_loggedin($pdo)
        : regenerate_session_id_generic();
}

/* ----------------------------------------------------------
   3.  CSRF token (one per session)
---------------------------------------------------------- */
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'] ?? '';
