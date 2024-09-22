<?php
// /admin/manage_admins.php

define('ALLOW_ACCESS', true);
include '../includes/header.php';
include '../includes/db_connect.php';
include '../includes/functions.php';

// Check if admin is logged in
checkAdminLogin();

// Fetch all admins
$stmt = $pdo->query('SELECT * FROM admins');
$admins = $stmt->fetchAll();

// Initialize message variables
$success_message = '';
$error_message = '';

// Handle adding a new admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Check if username or email exists
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, email) VALUES (?, ?, ?)');
        $stmt->execute([$username, $password_hash, $email]);
        $success_message = "تم إنشاء مسؤول جديد باسم '$username'.";
        
        // Refresh the admins list
        $stmt = $pdo->query('SELECT * FROM admins');
        $admins = $stmt->fetchAll();
    } else {
        $error_message = "اسم المستخدم أو البريد الإلكتروني موجود بالفعل.";
    }
}

// Handle deleting an admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_admin'])) {
    $admin_id = (int)$_POST['admin_id'];

    // Prevent self-deletion
    if ($admin_id != $_SESSION['admin_id']) {
        $stmt = $pdo->prepare('DELETE FROM admins WHERE admin_id = ?');
        $stmt->execute([$admin_id]);
        $success_message = "تم حذف المسؤول بنجاح.";
        
        // Refresh the admins list
        $stmt = $pdo->query('SELECT * FROM admins');
        $admins = $stmt->fetchAll();
    } else {
        $error_message = "لا يمكنك حذف حسابك الخاص أثناء تسجيل الدخول.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المسؤولين</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .container {
            max-width: 900px;
        }
        .table thead th {
            vertical-align: middle;
        }
        .form-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .action-buttons .btn {
            margin-left: 5px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2 class="text-center mb-4">إدارة المسؤولين</h2>

        <!-- Display Success and Error Messages -->
        <div class="mb-4">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Current Admins Table -->
        <div class="form-section mb-5">
            <h3 class="mb-3">المسؤولون الحاليون</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">اسم المستخدم</th>
                            <th scope="col">البريد الإلكتروني</th>
                            <th scope="col">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($admins) > 0): ?>
                            <?php foreach ($admins as $index => $admin): ?>
                                <tr>
                                    <th scope="row"><?php echo $index + 1; ?></th>
                                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td class="action-buttons">
                                        <?php if ($admin['admin_id'] != $_SESSION['admin_id']): ?>
                                            <form action="manage_admins.php" method="post" onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا المسؤول؟');">
                                                <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                                                <button type="submit" name="delete_admin" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash-alt"></i> حذف
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">أنت</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">لا توجد مسؤولين حاليين.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add New Admin Form -->
        <div class="form-section">
            <h3 class="mb-3">إضافة مسؤول جديد</h3>
            <form action="manage_admins.php" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">اسم المستخدم</label>
                    <input type="text" name="username" id="username" class="form-control" required placeholder="أدخل اسم المستخدم">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control" required placeholder="أدخل البريد الإلكتروني">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <input type="password" name="password" id="password" class="form-control" required placeholder="أدخل كلمة المرور">
                </div>
                <button type="submit" name="add_admin" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> إضافة مسؤول
                </button>
            </form>
        </div>

        <!-- Back to Dashboard Button -->
        <div class="mt-4 text-center">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> العودة إلى لوحة التحكم
            </a>
        </div>
    </div>

    <?php
    include '../includes/footer.php';
    ?>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Include Font Awesome JS (Optional if not included in header.php) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>