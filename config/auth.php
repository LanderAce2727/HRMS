<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tell the browser never to cache protected pages
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

function require_login(array $allowedRoles = []): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }

    if ($allowedRoles && !in_array($_SESSION['role_id'], $allowedRoles, true)) {
        http_response_code(403);
        exit('Access denied.');
    }
}