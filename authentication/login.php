<?php
session_start();

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: Authentication_Login.php');
    exit;
}

$identity = trim($_POST['identity'] ?? '');
$password = $_POST['password'] ?? '';

if ($identity === '' || $password === '') {
    header('Location: Authentication_Login.php?error=empty');
    exit;
}

try {
    $pdo = db();

    $stmt = $pdo->prepare(
        "SELECT id, username, email, password_hash, role_id, branch_id
         FROM users
         WHERE (username = :username OR email = :email)
           AND role_id IN (1, 2)
           AND is_active = 1
         LIMIT 1"
    );

    $stmt->execute([
        'username' => $identity,
        'email' => $identity
    ]);

    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header('Location: Authentication_Login.php?error=invalid');
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role_id'] = (int) $user['role_id'];
    $_SESSION['branch_id'] = $user['branch_id'];

    if ((int) $user['role_id'] === 1) {
        header('Location: ../hr-admin/HR_Admin_Dashboard.php');
        exit;
    }

    if ((int) $user['role_id'] === 2) {
        header('Location: ../employee/Employee_Employee_Profile.html'); // wala ko kabalo
        exit;
    }

    session_unset();
    session_destroy();

    http_response_code(403);
    exit('Access denied.');

} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('A server error occurred during login. Check the PHP error log.');
}
?>