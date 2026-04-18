<?php

// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Check if current user is an admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require a user to be logged in, otherwise redirect to login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        setFlash('warning', 'Please log in to access this page.');
        redirect('/login.php');
    }
}

/**
 * Require an admin role
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlash('error', 'You do not have permission to access this area.');
        // If accessed via /admin/, maybe redirect to /index.php depending on folder structure
        // Since all are in root or /admin, we'll just redirect to root index.
        redirect('../index.php'); 
    }
}

/**
 * Login a user by setting session variables
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
}

/**
 * Logout the user
 */
function logoutUser() {
    // Unset all of the session variables
    $_SESSION = array();

    // If it's desired to kill the session, also delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finally, destroy the session.
    session_destroy();
}
