<?php
//   require_once __DIR__ . '/../config/db.php';
//   $pdo = db();

date_default_timezone_set('Asia/Manila');

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'smart_hrms';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            // Match PHP timezone (Philippines)
            $pdo->exec("SET time_zone = '+08:00'");
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            exit('Database connection failed.');
        }
    }

    return $pdo;
}