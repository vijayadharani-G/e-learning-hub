<?php
// Secure logout script for XAMPP deployment

// Only start the session if one isn't already active (avoids "session already started" warnings)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
$_SESSION = [];

// Destroy the session cookie itself so the browser doesn't keep sending a stale session id
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session on the server side
session_destroy();

// Redirect to login page with a friendly confirmation flag
header("Location: login.php?logged_out=1");
exit;
?>