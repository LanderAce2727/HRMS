<?php

$host = "localhost";
$dbname = "smart_hrms";
$username = "root";
$password = "";

try {
    // Create database connection using PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Configure error handling
    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Use associative arrays for query results
    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    // Disable emulated prepared statements
    $pdo->setAttribute(
        PDO::ATTR_EMULATE_PREPARES,
        false
    );

} catch (PDOException $e) {
    // Log the actual error privately
    error_log("Database connection failed: " . $e->getMessage());

    // Display a generic error message
    http_response_code(500);
    exit("Database connection failed. Please try again later.");
}