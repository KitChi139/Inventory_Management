<?php
/**
 * Session Configuration
 * Include this file before session_start() to ensure sessions persist
 */

// Check if headers have already been sent
if (headers_sent($file, $line)) {
    // If headers are already sent, just start the session without configuration
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    // Don't return, continue to set session variables if needed
}

// Configure session settings for better persistence
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters (only if headers haven't been sent)
    @ini_set('session.cookie_lifetime', 86400); // 24 hours
    @ini_set('session.gc_maxlifetime', 86400); // 24 hours
    
    // Set cookie parameters (PHP 7.3+ syntax, with fallback)
    if (PHP_VERSION_ID >= 70300) {
        @session_set_cookie_params([
            'lifetime' => 86400, // 24 hours
            'path' => '/',
            'domain' => '', // Leave empty for current domain
            'secure' => false, // Set to true if using HTTPS
            'httponly' => true, // Prevents JavaScript access to cookie
            'samesite' => 'Lax' // CSRF protection
        ]);
    } else {
        // Fallback for older PHP versions
        @session_set_cookie_params(86400, '/', '', false, true);
    }
    
    // Start session
    session_start();
    
    // Regenerate session ID periodically for security (every 30 minutes)
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?>

