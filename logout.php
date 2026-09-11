<?php
session_start();

// Step 1: Remove session data
$_SESSION = [];

// Step 2: Remove session cookie
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

// Step 3: Destroy session
session_destroy();

// Redirect
header('Location: index.php');
exit();
