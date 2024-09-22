<?php
// index.php
define('ALLOW_ACCESS', true); 
include 'includes/header.php';
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<h2>
    مرحباً بكم في برنامج الاستبيان
</h2>





<?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
    <p>
        لقد تم تسجيل الدخول مسبقاً
    </p>
    <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/questionnaire/admin/index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> الذهاب إلى لوحة التحكم
    </a>
    <a class="nav-link text-danger" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/questionnaire/admin/logout.php">
        <i class="bi bi-box-arrow-right"></i> تسجيل الخروج
    </a>
<?php else: ?>
    <p>
        يجب أن تقوم بتسجيل الدخول أولاً لتتمكن من إنشاء وإدارة الاستبيانات.
    </p>
<!-- If not logged in, show Login link -->
    <a class="nav-link" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/questionnaire/admin/login.php">
        <i class="bi bi-box-arrow-in-right"></i> تسجيل الدخول
    </a>
<?php endif; ?>
<br>
<br>
<br>
<?php
include 'includes/footer.php';
?>