<?php
// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

// Email address that should receive a notification for every new submission
define('ADMIN_EMAIL', 'your_admin_email@example.com');

// ✅ FIXED: Changed 'mysql' to 'mysqli'
$mysql = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysql->connect_errno) {
    die('Database connection failed: ' . $mysql->connect_errno);
}

$mysql->set_charset('utf8mb4');
?>