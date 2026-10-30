<?php
// Lets the static HTML pages read the signed-in user (replaces the mock Google profile).
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['authenticated' => false]);
    exit;
}
echo json_encode([
    'authenticated' => true,
    'name'    => $_SESSION['name']    ?? '',
    'email'   => $_SESSION['email']   ?? '',
    'picture' => $_SESSION['picture'] ?? '',
    'role_id' => $_SESSION['role_id'] ?? 0,
]);
