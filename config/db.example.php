<?php
/**
 * Database Configuration Template
 * Copy this file to db.php and fill in your credentials.
 * NEVER commit db.php to version control.
 */

$host   = 'localhost';       // e.g. sql123.infinityfree.com
$db     = 'your_db_name';
$user   = 'your_db_user';
$pass   = 'your_db_password';
$port   = '3306';            // 3307 for local XAMPP on non-default port
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}
