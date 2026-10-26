<?php
// Database connection for the existing HRMS project.
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '127.0.0.1';
$dbname = 'smart_hrms';
$dbUser = 'root';
$dbPassword = ''; // Default XAMPP MySQL password. Change if yours is different.

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $dbUser,
        $dbPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('HRMS database connection failed: ' . $e->getMessage());
    http_response_code(500);
    if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'login.php') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Database connection failed. Check api/db.php and make sure MySQL is running.']);
        exit;
    }
    exit('Database connection failed. Check api/db.php and make sure MySQL is running.');
}
