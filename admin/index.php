
<?php
define('ALLOW_ACCESS', true);
if (!defined('ALLOW_ACCESS')) {
    die('Direct access not permitted.');
}
// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
// /admin/index.php

include '../includes/db_connect.php';
include '../includes/functions.php';

// Check if admin is logged in
checkAdminLogin();

// Get the current admin's ID and username from the session
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['username'];

// Fetch total number of questionnaires owned by the admin
$stmt = $pdo->prepare('SELECT COUNT(*) as total_questionnaires FROM questionnaires WHERE admin_id = ?');
$stmt->execute([$admin_id]);
$total_questionnaires = $stmt->fetchColumn();

// Fetch total number of responses for questionnaires owned by the admin
$stmt = $pdo->prepare('SELECT COUNT(*) as total_responses
                      FROM responses r
                      JOIN questionnaires q ON r.questionnaire_id = q.questionnaire_id
                      WHERE q.admin_id = ?');
$stmt->execute([$admin_id]);
$total_responses = $stmt->fetchColumn();

// Fetch recent questionnaires (latest 5) owned by the admin
$stmt = $pdo->prepare('SELECT * FROM questionnaires WHERE admin_id = ? ORDER BY created_at DESC LIMIT 5');
$stmt->execute([$admin_id]);
$recent_questionnaires = $stmt->fetchAll();

// Fetch recent responses (latest 5) for questionnaires owned by the admin
$stmt = $pdo->prepare('SELECT r.*, q.title FROM responses r
                      JOIN questionnaires q ON r.questionnaire_id = q.questionnaire_id
                      WHERE q.admin_id = ?
                      ORDER BY r.submitted_at DESC LIMIT 5');
$stmt->execute([$admin_id]);
$recent_responses = $stmt->fetchAll();
?>

<?php
if (!defined('ALLOW_ACCESS')) {
    die('Direct access not permitted.');
}
?>

<?php
// Check for success message
$msg = '';
if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $msg = 'تم حذف الاستبيان بنجاح.';
    echo '<script>alert("تم حذف الاستبيان بنجاح.")</script>';
    header('Location: index.php');
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


<!-- Updated Navigation Menu -->

<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 d-md-none">
    <div class="container-fluid">
        <a class="navbar-brand" href="/index.php">برنامج الاستبيان</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">لوحة التحكم</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="questionnaires/create.php">إنشاء استبيان جديد</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" href="manage_admins.php">
                                    إدارة الحسابات
                    </a>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link" href="responses/index.php">الردود</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/questionnaire/admin/logout.php">
                        تسجيل الخروج
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container">



<h2 style="text-align:center;">
    لوحة التحكم
</h2>


<main class="container-fluid">
    <div class="row">
  <!-- Sidebar for Medium and Larger Screens -->
  <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <!-- Display the admin's name -->
                <div class="px-3 mb-3">
                    <h5>مرحباً، <?php echo htmlspecialchars($admin_username); ?></h5>
                </div>
                    <ul class="nav flex-column">
                        
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="../index.php">
                                <i class="bi bi-house-fill"></i>
                                الرئيسية
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="index.php">
                            <i class="bi bi-ui-checks"></i>
                                لوحة التحكم
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="questionnaires/create.php">
                                <i class="bi bi-plus-circle-fill"></i>
                                إنشاء استبيان جديد
                            </a>
                        </li>
                        
                        <!-- <li class="nav-item">
                            <a class="nav-link" href="manage_admins.php">
                                <i class="bi bi-person-fill"></i>
                                    إدارة الحسابات
                            </a>
                        </li> -->
                        
                        <li class="nav-item">
                            <a class="nav-link" href="responses/index.php">
                                <i class="bi bi-bar-chart-fill"></i>
                                الردود
                            </a>
                        </li>
                        
                        <li class="nav-item">
                        <i class="bi bi-door-closed-fill"></i>
                            <a href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/questionnaire/admin/logout.php" class="button">
                            تسجيل الخروج    
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <!-- End of Sidebar -->

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Dashboard summary cards -->
            <div class="row mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <div class="card-title">إجمالي الاستبيانات</div>
                            <h2 class="card-text"><?php echo $total_questionnaires; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <div class="card-title">إجمالي الردود</div>
                            <h2 class="card-text"><?php echo $total_responses; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Questionnaires -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    آخر الاستبيانات
                </div>
                <div class="card-body">
                    <?php if (count($recent_questionnaires) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recent_questionnaires as $questionnaire): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($questionnaire['title']); ?>
                                    <span>
                                        <a href="questionnaires/view.php?id=<?php echo $questionnaire['questionnaire_id']; ?>" class="btn btn-sm btn-info">عرض</a>
                                        <a href="questionnaires/edit.php?id=<?php echo $questionnaire['questionnaire_id']; ?>" class="btn btn-sm btn-warning">تعديل</a>
                                        <a href="questionnaires/delete.php?id=<?php echo $questionnaire['questionnaire_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا الاستبيان؟');">حذف</a>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>لا توجد استبيانات حديثة.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Responses -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    آخر الردود
                </div>
                <div class="card-body">
                    <?php if (count($recent_responses) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>الاستبيان</th>
                                        <th>اسم المستخدم</th>
                                        <th>تاريخ الإرسال</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_responses as $response): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($response['title']); ?></td>
                                            <td><?php echo htmlspecialchars($response['user_name'] ?? 'غير متوفر'); ?></td>
                                            <td><?php echo date('d-m-Y H:i', strtotime($response['submitted_at'])); ?></td>
                                            <td>
                                                <a href="responses/view.php?id=<?php echo $response['response_id']; ?>" class="btn btn-sm btn-info">عرض</a>
                                                <a href="responses/delete.php?id=<?php echo $response['response_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا الرد؟');">حذف</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>لا توجد ردود حديثة.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- All Questionnaires List -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    قائمة الاستبيانات
                    <a href="questionnaires/create.php" class="btn btn-sm btn-success">إضافة استبيان جديد</a>
                </div>
                <div class="card-body">
                    <?php
                    // Fetch all questionnaires owned by the admin
                    $stmt = $pdo->prepare('SELECT * FROM questionnaires WHERE admin_id = ? ORDER BY created_at DESC');
                    $stmt->execute([$admin_id]);
                    $questionnaires = $stmt->fetchAll();
                    if ($questionnaires):
                    ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>العنوان</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($questionnaires as $index => $questionnaire): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($questionnaire['title']); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($questionnaire['created_at'])); ?></td>
                                            <td>
                                                <a href="questionnaires/view.php?id=<?php echo $questionnaire['questionnaire_id']; ?>" class="btn btn-sm btn-info">عرض</a>
                                                <a href="questionnaires/edit.php?id=<?php echo $questionnaire['questionnaire_id']; ?>" class="btn btn-sm btn-warning">تعديل</a>
                                                <a href="questionnaires/delete.php?id=<?php echo $questionnaire['questionnaire_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا الاستبيان؟');">حذف</a>
                                                <a href="../../questionnaire/questionnaire/take.php?id=<?php echo $questionnaire['questionnaire_id']; ?>" class="btn btn-sm btn-success" target="_blank">الرابط</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>لا توجد استبيانات متاحة.</p>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</main>

<!-- Include the footer file -->
<?php include '../includes/footer.php'; ?>

</body>
</html>