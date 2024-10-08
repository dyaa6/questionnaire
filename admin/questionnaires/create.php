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

// Initialize error messages array
$error_messages = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);

    // Initialize variables for image paths
    $logo_path = null;
    $background_path = null;

    // Define allowed extensions and max file size
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    $max_file_size = 2 * 1024 * 1024; // 2MB

    // Handle Logo Upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['logo']['size'] <= $max_file_size) {
            $logo_tmp = $_FILES['logo']['tmp_name'];
            $logo_name = basename($_FILES['logo']['name']);
            $logo_ext = strtolower(pathinfo($logo_name, PATHINFO_EXTENSION));

            if (in_array($logo_ext, $allowed_ext)) {
                // Sanitize and generate unique file name
                $unique_id = uniqid();
                $new_logo_name = 'logo_' . $admin_id . '_' . $unique_id . '.' . $logo_ext;
                $logo_destination = '../../uploads/logos/' . $new_logo_name;

                if (move_uploaded_file($logo_tmp, $logo_destination)) {
                    $logo_path = 'uploads/logos/' . $new_logo_name;
                } else {
                    $error_messages[] = "فشل رفع الشعار.";
                }
            } else {
                $error_messages[] = "امتداد الشعار غير مسموح به. فقط JPG، JPEG، PNG، GIF مسموح.";
            }
        } else {
            $error_messages[] = "حجم الشعار يجب أن يكون أقل من 2MB.";
        }
    }

    // Handle Background Image Upload
    if (isset($_FILES['background']) && $_FILES['background']['error'] == UPLOAD_ERR_OK) {
        if ($_FILES['background']['size'] <= $max_file_size) {
            $background_tmp = $_FILES['background']['tmp_name'];
            $background_name = basename($_FILES['background']['name']);
            $background_ext = strtolower(pathinfo($background_name, PATHINFO_EXTENSION));

            if (in_array($background_ext, $allowed_ext)) {
                // Sanitize and generate unique file name
                $unique_id = uniqid();
                $new_background_name = 'background_' . $admin_id . '_' . $unique_id . '.' . $background_ext;
                $background_destination = '../../uploads/backgrounds/' . $new_background_name;

                if (move_uploaded_file($background_tmp, $background_destination)) {
                    $background_path = 'uploads/backgrounds/' . $new_background_name;
                } else {
                    $error_messages[] = "فشل رفع صورة الخلفية.";
                }
            } else {
                $error_messages[] = "امتداد صورة الخلفية غير مسموح به. فقط JPG، JPEG، PNG، GIF مسموح.";
            }
        } else {
            $error_messages[] = "حجم صورة الخلفية يجب أن يكون أقل من 2MB.";
        }
    }

    // Insert into questionnaires table with admin_id and image paths
    $stmt = $pdo->prepare('INSERT INTO questionnaires (title, description, logo_path, background_path, admin_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$title, $description, $logo_path, $background_path, $admin_id]);

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
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Font Awesome for Icons (Optional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">

    <style>
        /* Custom Styles */
        body, .form-group, label {
            text-align: right;
        }
    </style>
</head>
<body dir="rtl">

    <div class="container mt-5">
        <h2 class="mb-4 text-center">إنشاء استبيان جديد</h2>

        <!-- Display Error Messages -->
        <?php if (!empty($error_messages)): ?>
            <div class="alert alert-danger">
                <?php foreach ($error_messages as $message): ?>
                    <p><?php echo htmlspecialchars($message); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="create.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">عنوان الاستبيان</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="أدخل عنوان الاستبيان" required>
            </div>
            <div class="form-group">
                <label for="description">وصف الاستبيان</label>
                <textarea name="description" id="description" class="form-control" placeholder="أدخل وصفاً للاستبيان" rows="5" required></textarea>
            </div>

            <!-- Logo Upload Input -->
            <div class="form-group">
                <label for="logo">الشعار</label>
                <div class="custom-file">
                    <input type="file" name="logo" id="logo" class="custom-file-input" accept=".jpg, .jpeg, .png, .gif">
                    <label class="custom-file-label text-left" for="logo">اختر الشعار</label>
                </div>
            </div>

            <!-- Background Image Upload Input -->
            <div class="form-group">
                <label for="background">صورة الخلفية</label>
                <div class="custom-file">
                    <input type="file" name="background" id="background" class="custom-file-input" accept=".jpg, .jpeg, .png, .gif">
                    <label class="custom-file-label text-left" for="background">اختر صورة الخلفية</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-4">إنشاء الاستبيان</button>
        </form>
    </div>
<br>
    <?php include '../../includes/footer.php'; ?>
    
    <!-- jQuery, Popper.js, and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

    <!-- Custom Script to Update File Input Labels -->
    <script>
        // File input label update for Bootstrap 4
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            if(fileName){
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
            } else {
                $(this).siblings(".custom-file-label").html("اختر ملف");
            }
        });
    </script>
</body>
</html>