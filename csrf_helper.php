<?php
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    session_start();
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token']) || time() > $_SESSION['csrf_token_expiry']) {
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
        $_SESSION['csrf_token_expiry'] = time() + (15 * 60); // Token valid for 15 minutes
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token'], $_SESSION['csrf_token_expiry']) &&
           time() <= $_SESSION['csrf_token_expiry'] &&
           hash_equals($_SESSION['csrf_token'], $token);
}
?>