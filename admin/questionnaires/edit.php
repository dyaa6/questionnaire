<?php
// /admin/questionnaires/edit.php
define('ALLOW_ACCESS', true);
include '../../includes/header.php';
include '../../includes/db_connect.php';
include '../../includes/functions.php';

// Check if admin is logged in
checkAdminLogin();

// Initialize error messages array
$error_messages = [];

// Get questionnaire ID
$questionnaire_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch questionnaire
$stmt = $pdo->prepare('SELECT * FROM questionnaires WHERE questionnaire_id = ?');
$stmt->execute([$questionnaire_id]);
$questionnaire = $stmt->fetch();

if (!$questionnaire) {
    echo "<p class='alert alert-danger text-right'>الاستبيان غير موجود.</p>";
    include '../../includes/footer.php';
    exit();
}

// Process form submission for updating questionnaire details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_details'])) {
    // Sanitize inputs
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $welcome = sanitizeInput($_POST['welcome']);
    $thanks = sanitizeInput($_POST['thanks']);
    $font_color = sanitizeInput($_POST['font_color']);

    // Validate font_color (basic validation to ensure it's a hex color)
    if (!preg_match('/^#[a-fA-F0-9]{6}$/', $font_color)) {
        $error_messages[] = "الرجاء اختيار لون خط صحيح.";
    }

    // Initialize variables for image paths
    $logo_path = $questionnaire['logo_path'];
    $background_path = $questionnaire['background_path'];

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
                $new_logo_name = 'logo_' . $questionnaire_id . '_' . $unique_id . '.' . $logo_ext;
                $logo_destination = '../../uploads/logos/' . $new_logo_name;

                if (move_uploaded_file($logo_tmp, $logo_destination)) {
                    // Delete old logo if exists
                    if (!empty($logo_path) && file_exists('../../' . $logo_path)) {
                        unlink('../../' . $logo_path);
                    }

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
                $new_background_name = 'background_' . $questionnaire_id . '_' . $unique_id . '.' . $background_ext;
                $background_destination = '../../uploads/backgrounds/' . $new_background_name;

                if (move_uploaded_file($background_tmp, $background_destination)) {
                    // Delete old background if exists
                    if (!empty($background_path) && file_exists('../../' . $background_path)) {
                        unlink('../../' . $background_path);
                    }

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

    // Only proceed if there are no errors
    if (empty($error_messages)) {
        try {
            // Begin transaction
            $pdo->beginTransaction();

            // Update the questionnaire details in the database, including welcome, thanks, and font_color
            $stmt = $pdo->prepare('UPDATE questionnaires SET title = ?, description = ?, welcome = ?, thanks = ?, font_color = ?, logo_path = ?, background_path = ? WHERE questionnaire_id = ?');
            $stmt->execute([$title, $description, $welcome, $thanks, $font_color, $logo_path, $background_path, $questionnaire_id]);

            // Commit transaction
            $pdo->commit();

            // Refresh page to show updated details
            header('Location: edit.php?id=' . $questionnaire_id);
            exit();
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $error_messages[] = "حدث خطأ أثناء تحديث التفاصيل: " . $e->getMessage();
        }
    }
}

// Process form submission for adding new question
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_question'])) {
    $question_text = sanitizeInput($_POST['question_text']);
    $question_type = sanitizeInput($_POST['question_type']);

    $stmt = $pdo->prepare('INSERT INTO questions (questionnaire_id, question_text, question_type) VALUES (?, ?, ?)');
    $stmt->execute([$questionnaire_id, $question_text, $question_type]);

    // Refresh page to show new question
    header('Location: edit.php?id=' . $questionnaire_id);
    exit();
}

// Process request to delete a question
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_question'])) {
    $question_id = (int)$_POST['question_id'];
    // Delete the question
    $stmt = $pdo->prepare('DELETE FROM questions WHERE question_id = ? AND questionnaire_id = ?');
    $stmt->execute([$question_id, $questionnaire_id]);

    // Optionally, delete associated choices if question type is 'choice'
    $stmt = $pdo->prepare('DELETE FROM choices WHERE question_id = ?');
    $stmt->execute([$question_id]);

    // Refresh page
    header('Location: edit.php?id=' . $questionnaire_id);
    exit();
}

// Fetch questions
$stmt = $pdo->prepare('SELECT * FROM questions WHERE questionnaire_id = ?');
$stmt->execute([$questionnaire_id]);
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تحرير الاستبيان</title>
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Font Awesome for Icons (Optional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">

    <style>
        /* Custom Styles for Image Previews */
        .image-preview {
            max-height: 100px;
            margin: 10px auto;
            display: block;
        }

        /* Ensure text is aligned to the right */
        body, .card, .card-body, .form-group, label {
            text-align: right;
        }
    </style>
</head>
<body dir="rtl">

    <div class="container-fluid mt-5">
        <div class="row">
            <!-- Main Content Column -->
            <div class="col-md-12">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h2 class="mb-4 text-center">تحرير الاستبيان</h2>

                        <!-- Display Error Messages -->
                        <?php if (!empty($error_messages)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($error_messages as $message): ?>
                                    <p><?php echo htmlspecialchars($message); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Questionnaire Details Form -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                تفاصيل الاستبيان
                            </div>
                            <div class="card-body">
                                <form action="edit.php?id=<?php echo $questionnaire_id; ?>" method="post" enctype="multipart/form-data">
                                    <!-- Title Input -->
                                    <div class="form-group">
                                        <label for="title">العنوان</label>
                                        <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($questionnaire['title']); ?>" required>
                                    </div>
                                    <!-- Description Input -->
                                    <div class="form-group">
                                        <label for="description">الوصف</label>
                                        <textarea name="description" id="description" class="form-control" rows="3" required><?php echo htmlspecialchars($questionnaire['description']); ?></textarea>
                                    </div>

                                    <!-- Welcome Message Input -->
                                    <div class="form-group">
                                        <label for="welcome">رسالة الترحيب</label>
                                        <textarea name="welcome" id="welcome" class="form-control" rows="3" required><?php echo htmlspecialchars($questionnaire['welcome']); ?></textarea>
                                    </div>

                                    <!-- Thank You Message Input -->
                                    <div class="form-group">
                                        <label for="thanks">رسالة الشكر</label>
                                        <textarea name="thanks" id="thanks" class="form-control" rows="3" required><?php echo htmlspecialchars($questionnaire['thanks']); ?></textarea>
                                    </div>

                                    <!-- Font Color Picker -->
                                    <div class="form-group">
                                        <label for="font_color">لون الخط</label>
                                        <input type="color" name="font_color" id="font_color" class="form-control" value="<?php echo htmlspecialchars($questionnaire['font_color']); ?>" required>
                                        <small class="form-text text-muted">اختر لوناً مناسباً للخط.</small>
                                    </div>

                                    <!-- Logo Upload Input -->
                                    <div class="form-group">
                                        <label for="logo">الشعار</label>
                                        <div class="custom-file">
                                            <input type="file" name="logo" id="logo" class="custom-file-input" accept=".jpg, .jpeg, .png, .gif">
                                            <label class="custom-file-label text-left" for="logo">اختر الشعار</label>
                                        </div>
                                        <?php if (!empty($questionnaire['logo_path']) && file_exists('../../' . $questionnaire['logo_path'])): ?>
                                            <img src="<?php echo '../../' . htmlspecialchars($questionnaire['logo_path']); ?>" alt="Logo" class="image-preview">
                                        <?php endif; ?>
                                    </div>

                                    <!-- Background Image Upload Input -->
                                    <div class="form-group">
                                        <label for="background">صورة الخلفية</label>
                                        <div class="custom-file">
                                            <input type="file" name="background" id="background" class="custom-file-input" accept=".jpg, .jpeg, .png, .gif">
                                            <label class="custom-file-label text-left" for="background">اختر صورة الخلفية</label>
                                        </div>
                                        <?php if (!empty($questionnaire['background_path']) && file_exists('../../' . $questionnaire['background_path'])): ?>
                                            <img src="<?php echo '../../' . htmlspecialchars($questionnaire['background_path']); ?>" alt="Background" class="image-preview">
                                        <?php endif; ?>
                                    </div>

                                    <button type="submit" name="update_details" class="btn btn-success mt-3">تحديث التفاصيل</button>
                                </form>
                            </div>
                        </div>

                        <!-- Add New Question Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-secondary text-white">
                                إضافة سؤال جديد
                            </div>
                            <div class="card-body">
                                <form action="edit.php?id=<?php echo $questionnaire_id; ?>" method="post">
                                    <div class="form-group">
                                        <label for="question_text">نص السؤال</label>
                                        <input type="text" name="question_text" id="question_text" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="question_type">نوع السؤال</label>
                                        <select name="question_type" id="question_type" class="form-control" required>
                                            <option value="">اختر نوع السؤال</option>
                                            <option value="text">نص</option>
                                            <option value="textarea">مساحة نص</option>
                                            <option value="choice">اختيار من متعدد</option>
                                            <option value="stars">تقييم بالنجوم</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="add_question" class="btn btn-primary mt-3">إضافة سؤال</button>
                                </form>
                            </div>
                        </div>

                        <!-- Display Existing Questions with Delete Button -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                الأسئلة الحالية
                            </div>
                            <div class="card-body">
                                <?php if (count($questions) > 0): ?>
                                    <ul class="list-group">
                                        <?php foreach ($questions as $question): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>
                                                    <?php echo htmlspecialchars($question['question_text']); ?> 
                                                    <span class="badge badge-pill badge-secondary"><?php echo htmlspecialchars($question['question_type']); ?></span>
                                                </span>
                                                <form action="edit.php?id=<?php echo $questionnaire_id; ?>" method="post" onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال؟');">
                                                    <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                                                    <button type="submit" name="delete_question" class="btn btn-danger btn-sm">حذف</button>
                                                </form>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted text-right">لم يتم إضافة أسئلة بعد.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Back to List Button -->
                        <a href="../index.php" class="btn btn-link">العودة إلى قائمة الاستبيانات</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery, Popper.js, and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Font Awesome (Optional for Icons) -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>

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