<?php
session_start();

// Tell the browser not to keep (or restore) any page from this site after logout.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Clear-Site-Data: "cache"');

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

header('Location: ../login.php');
exit;