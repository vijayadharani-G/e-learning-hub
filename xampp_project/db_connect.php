<?php
// Secure database connector for XAMPP deployment
// Auto-creates the database + tables + seed data on first run,
// so this project works correctly on ANY computer/person without
// manual phpMyAdmin steps.

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elearning_db');

// Path to the SQL file that contains schema + seed data
define('SQL_FILE', __DIR__ . '/database.sql');

// 1. Connect to MySQL server (no database selected yet)
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("<div style='font-family:sans-serif; text-align:center; padding: 50px; background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; margin: 50px auto; max-width: 600px; border-radius: 12px;'>
            <h2 style='margin-bottom: 10px;'>MySQL Server Connection Failed</h2>
            <p style='font-size: 14px; color: #4a5568;'>Ensure that your <strong>XAMPP Control Panel</strong> is running and that the <strong>MySQL</strong> service is fully started.</p>
         </div>");
}

// 2. Check if the database already exists
$dbCheck = $conn->query("SHOW DATABASES LIKE '" . $conn->real_escape_string(DB_NAME) . "'");
$dbExists = ($dbCheck && $dbCheck->num_rows > 0);

if (!$dbExists) {
    // 3. Database not found -> create it automatically
    if (!$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
        die("<div style='font-family:sans-serif; text-align:center; padding:50px; background:#fff5f5; color:#c53030; max-width:600px; margin:50px auto; border-radius:12px;'>
                <h2>Could not create database automatically</h2>
                <p style='font-size:14px;'>Error: " . htmlspecialchars($conn->error) . "</p>
             </div>");
    }
}

// 4. Select the database
if (!$conn->select_db(DB_NAME)) {
    die("<div style='font-family:sans-serif; text-align:center; padding:50px; background:#fff5f5; color:#c53030; max-width:600px; margin:50px auto; border-radius:12px;'>
            <h2>Could not select database '" . DB_NAME . "'</h2>
         </div>");
}

// 5. If the database was just created (or is empty), auto-import database.sql
$tableCheck = $conn->query("SHOW TABLES");
$hasTables = ($tableCheck && $tableCheck->num_rows > 0);

if (!$hasTables) {
    if (!file_exists(SQL_FILE)) {
        die("<div style='font-family:sans-serif; text-align:center; padding:50px; background:#fff5f5; color:#c53030; max-width:600px; margin:50px auto; border-radius:12px;'>
                <h2>database.sql file not found</h2>
                <p style='font-size:14px;'>Make sure database.sql is in the same folder as db_connect.php.</p>
             </div>");
    }

    $sql = file_get_contents(SQL_FILE);

    // multi_query lets us run the whole .sql file (multiple statements) at once
    if ($conn->multi_query($sql)) {
        // Flush through all result sets/statements so the connection is left clean
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    }

    if ($conn->errno) {
        die("<div style='font-family:sans-serif; text-align:center; padding:50px; background:#fff5f5; color:#c53030; max-width:600px; margin:50px auto; border-radius:12px;'>
                <h2>Auto import of database.sql failed</h2>
                <p style='font-size:14px;'>Error: " . htmlspecialchars($conn->error) . "</p>
             </div>");
    }
}

// 6. Start user session securely on all connected pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
