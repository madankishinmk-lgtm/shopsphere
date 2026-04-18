<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

logoutUser();
setFlash('success', 'You have been successfully logged out.');
redirect('login.php');
