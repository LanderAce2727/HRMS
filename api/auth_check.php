<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(): void {
    $idleLimit = 1800; // 30 minutes
    if (empty($_SESSION['user_id'])) {
        header('Location: /HRMS/authentication/Authentication_Login.html');
        exit;
    }
    if (isset($_SESSION['last_activity']) && time() - (int)$_SESSION['last_activity'] > $idleLimit) {
        $_SESSION = [];
        session_destroy();
        header('Location: /HRMS/authentication/Authentication_Login.html?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

function require_role(string ...$allowedRoles): void {
    require_login();
    if (!in_array($_SESSION['role'] ?? '', $allowedRoles, true)) {
        http_response_code(403);
        exit('403 Forbidden: You do not have permission to view this page.');
    }
}
