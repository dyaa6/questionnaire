<?php
// /includes/functions.php

// Prevent direct access
if (!defined('ALLOW_ACCESS')) {
    die('Direct access not permitted.');
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Sanitize user input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Check if admin is logged in
function checkAdminLogin() {
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: login.php');
        exit();
    }
}


// Other reusable functions as needed
?>

