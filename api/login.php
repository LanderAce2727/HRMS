<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $data): never {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['success' => false, 'message' => 'Use POST to log in.']);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(400, ['success' => false, 'message' => 'Invalid request data.']);
    }

    $email = trim((string)($input['email'] ?? ''));
    $password = (string)($input['password'] ?? '');
    $selectedRole = strtolower(trim((string)($input['role'] ?? '')));

    // Preserve the existing UI role labels while matching the roles in smart_hrms.
    $roleMap = [
        'admin' => 'hr_admin',
        'hr_admin' => 'hr_admin',
        'instructor' => 'employee',
        'employee' => 'employee',
        'applicant' => 'applicant',
    ];
    if ($email === '' || $password === '' || !isset($roleMap[$selectedRole])) {
        respond(400, ['success' => false, 'message' => 'Enter your email and password, and select the correct role.']);
    }

    $sql = 'SELECT u.id, u.username, u.email, u.password_hash, u.is_active, r.role_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE u.email = :email
            LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || (int)$user['is_active'] !== 1 ||
        !password_verify($password, (string)$user['password_hash']) ||
        $user['role_name'] !== $roleMap[$selectedRole]) {
        respond(401, ['success' => false, 'message' => 'Incorrect email, password, or selected role.']);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role_name'];
    $_SESSION['last_activity'] = time();

    $redirects = [
        'hr_admin' => '../hr-admin/HR_Admin_Dashboard.html',
        'employee' => '../Attendance.html',
        'applicant' => '../applicant/dashboard.php',
    ];

    respond(200, [
        'success' => true,
        'message' => 'Login successful.',
        'redirect' => $redirects[$user['role_name']] ?? '../index.html'
    ]);
} catch (Throwable $e) {
    error_log('HRMS login error: ' . $e->getMessage());
    respond(500, ['success' => false, 'message' => 'A server error occurred while logging in.']);
}
