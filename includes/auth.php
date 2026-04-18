<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        setFlash('warning', 'Please log in to access this page.');
        redirect('/login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlash('error', 'You do not have permission to access this area.');
        
        
        redirect('../index.php'); 
    }
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
}

function logoutUser() {
    
    $_SESSION = array();

    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    
    session_destroy();
}
