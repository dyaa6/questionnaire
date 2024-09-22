<?php
// /includes/header.php

if (!defined('ALLOW_ACCESS')) {
    die('Direct access not permitted.');
}
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="ar" dir='rtl'>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>لوحة التحكم - برنامج الاستبيان</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (Optional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<?php
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$host = parse_url($current_url, PHP_URL_HOST);
?>

<!-- Updated Navigation Menu -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/index.php">برنامج الاستبيان</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <!-- Dashboard Link -->
                <li class="nav-item">
                    <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/questionnaire/admin/index.php" class="button">
                    لوحة التحكم  
                    </a>
                </li>
                
                
                <!-- Admin Profile / Logout -->
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>

                    <li class="nav-item">
                        <a class="nav-link text-danger" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/questionnaire/admin/logout.php">
                            <i class="bi bi-box-arrow-right"></i> تسجيل الخروج
                        </a>
                    </li>
                <?php else: ?>
                    <!-- If not logged in, show Login link -->
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> تسجيل الدخول
                        </a>
                    </li>
                <?php endif; ?>
                <!-- Frontend Links -->
                <li class="nav-item">
                <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/questionnaire/index.php" class="button">
                    الصفحة الرئيسية
                    </a>
                </li>
                <!-- Additional Links (if any) -->
            </ul>
        </div>
    </div>
</nav>

<main class="container">