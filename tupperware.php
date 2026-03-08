<?php

// session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('BASE_PATH', __DIR__);
define('BASE_FR', '/');
require_once __DIR__ . '/authentication/functions.php';
require_once __DIR__ . '/vendor/autoload.php';