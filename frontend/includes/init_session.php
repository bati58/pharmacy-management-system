<?php
/**
 * Shared session bootstrap so API (backend) and pages share the same session cookie.
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
