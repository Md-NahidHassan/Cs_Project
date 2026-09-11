<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$INACTIVITY_LIMIT = 900; // 15 minutes

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $INACTIVITY_LIMIT) {
        // inactive → force logout
        session_unset();
        session_destroy();
        header('Location: logout.php?timeout=1');
        exit();
    }
}

// update activity timestamp
$_SESSION['last_activity'] = time();
