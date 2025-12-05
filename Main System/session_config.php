<?php

if (headers_sent($file, $line)) {

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

}


if (session_status() === PHP_SESSION_NONE) {

    @ini_set('session.cookie_lifetime', 86400); 
    @ini_set('session.gc_maxlifetime', 86400); 

    if (PHP_VERSION_ID >= 70300) {
        @session_set_cookie_params([
            'lifetime' => 86400, 
            'path' => '/',
            'domain' => '',
            'secure' => false, 
            'httponly' => true, 
            'samesite' => 'Lax' 
        ]);
    } else {
 
        @session_set_cookie_params(86400, '/', '', false, true);
    }

    session_start();

    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}
?>

