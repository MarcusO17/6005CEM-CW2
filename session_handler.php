<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('INACTIVITY_TIMEOUT', 900); // 15 minutes
define('SESSION_EXPIRATION_TIME', 18000); // 5 hours

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > INACTIVITY_TIMEOUT) {

    if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-86400, '/');
	}

    session_unset();
    session_destroy();
    header("Location: ../login.php?expired=true");
    exit();
}

if (isset($_SESSION['created']) && (time() - $_SESSION['created']) > SESSION_EXPIRATION_TIME) {

    if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-86400, '/');
	}

    session_unset();
    session_destroy();
    header("Location: ../login.php?expired=true");
    exit();
}

$_SESSION['last_activity'] = time();

if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
}
?>
