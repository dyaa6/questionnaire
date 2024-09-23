<?php
// /admin/questionnaires/create.php
define('ALLOW_ACCESS', true); 
include '../../includes/header.php';
include '../../includes/db_connect.php';
include '../../includes/functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
checkAdminLogin();

// Get current admin ID
$admin_id = $_SESSION['admin_id'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);

    // Insert into questionnaires table with admin_id
    $stmt = $pdo->prepare('INSERT INTO questionnaires (title, description, admin_id, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$title, $description, $admin_id]);

    $questionnaire_id = $pdo->lastInsertId();

    // Redirect to edit page to add questions
    header('Location: edit.php?id=' . $questionnaire_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء استبيان جديد</title>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .form-group{
            text-align:right;
        }
    </style>
    
</head>
<body>

    <div class="container mt-5">
        <h2 class="mb-4 text-center">إنشاء استبيان جديد</h2>

        <form action="create.php" method="post">
            <div class="form-group">
                <label for="title">عنوان الاستبيان</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="أدخل عنوان الاستبيان" required>
            </div>
            <div class="form-group">
                <label for="description">وصف الاستبيان</label>
                <textarea name="description" id="description" class="form-control" placeholder="أدخل وصفاً للاستبيان" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">إنشاء الاستبيان</button>
        </form>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
</body>
</html>