<?php
// /admin/questionnaires/edit.php
define('ALLOW_ACCESS', true); 
include '../../includes/header.php';
include '../../includes/db_connect.php';
include '../../includes/functions.php';

// Check if admin is logged in
checkAdminLogin();

$questionnaire_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch questionnaire
$stmt = $pdo->prepare('SELECT * FROM questionnaires WHERE questionnaire_id = ?');
$stmt->execute([$questionnaire_id]);
$questionnaire = $stmt->fetch();

if (!$questionnaire) {
    echo "<p class='alert alert-danger'>الاستبيان غير موجود.</p>";
    include '../../includes/footer.php';
    exit();
}

// Process form submission for updating questionnaire details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_details'])) {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);

    $stmt = $pdo->prepare('UPDATE questionnaires SET title = ?, description = ? WHERE questionnaire_id = ?');
    $stmt->execute([$title, $description, $questionnaire_id]);

    // Refresh page to show updated details
    header('Location: edit.php?id=' . $questionnaire_id);
    exit();
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
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .star-rating {
            direction: rtl;
            display: inline-block;
        }
        .star {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
        }
        input[type="radio"] {
            display: none;
        }
        input[type="radio"]:checked ~ .star {
            color: #FFD700;
        }
        .question-slide {
            margin-bottom: 20px;
        }
        .question-navigation {
            text-align: center;
            margin-top: 20px;
        }
        .dashboard-card {
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border: none;
            text-align:right;
        }
    </style>
</head>
<body>

    <div class="container-fluid mt-5">
        <div class="row">
            <!-- Sidebar (optional) -->
            <!-- <div class="col-md-2">
                <?php // include '../../includes/sidebar.php'; ?>
            </div> -->
            <div class="col-md-12">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h2 class="mb-4">تحرير الاستبيان</h2>

                        <!-- Questionnaire Details -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                تفاصيل الاستبيان
                            </div>
                            <div class="card-body">
                                <form action="edit.php?id=<?php echo $questionnaire_id; ?>" method="post">
                                    <div class="form-group">
                                        <label for="title">العنوان</label>
                                        <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($questionnaire['title']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="description">الوصف</label>
                                        <textarea name="description" id="description" class="form-control" rows="3" required><?php echo htmlspecialchars($questionnaire['description']); ?></textarea>
                                    </div>
                                    <button type="submit" name="update_details" class="btn btn-success">تحديث التفاصيل</button>
                                </form>
                            </div>
                        </div>

                        <!-- Questions Slider -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                الأسئلة
                            </div>
                            <div class="card-body">
                                <?php if (count($questions) > 0): ?>
                                    <div id="question-slider">
                                        <div class="question-container">
                                            <?php foreach ($questions as $index => $question): ?>
                                                <div class="question-slide" data-index="<?php echo $index; ?>" style="display: none;">
                                                    <h4><?php echo htmlspecialchars($question['question_text']); ?></h4>
                                                    <?php if ($question['question_type'] == 'stars'): ?>
                                                        <div class="star-rating">
                                                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                                                <input type="radio" id="star<?php echo $i; ?>_<?php echo $index; ?>" name="rating_<?php echo $index; ?>" value="<?php echo $i; ?>" />
                                                                <label for="star<?php echo $i; ?>_<?php echo $index; ?>" class="star">&#9733;</label>
                                                            <?php endfor; ?>
                                                        </div>
                                                    <?php elseif ($question['question_type'] == 'textarea'): ?>
                                                        <textarea class="form-control" rows="3" placeholder="أدخل إجابتك هنا"></textarea>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="question-navigation">
                                            <button id="prev-btn" class="btn btn-secondary"><i class="fas fa-arrow-right"></i> السابق</button>
                                            <button id="next-btn" class="btn btn-secondary">التالي <i class="fas fa-arrow-left"></i></button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">لم يتم إضافة أسئلة بعد.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Add New Question -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
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
                                            <option value="stars">تقييم بالنجوم</option>
                                            <option value="textarea">مساحة نص</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="add_question" class="btn btn-primary">إضافة سؤال</button>
                                </form>
                            </div>
                        </div>

                        <!-- Back Button -->
                        <a href="../index.php" class="btn btn-link">العودة إلى قائمة الاستبيانات</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <!-- If you need Popper.js for Bootstrap tooltips and popovers -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Font Awesome for icons (optional) -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <!-- Custom Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.question-slide');
            let currentSlide = 0;

            if (slides.length > 0) {
                // Show the first slide
                slides[currentSlide].style.display = 'block';
            }

            // Function to show the current slide
            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.style.display = (i === index) ? 'block' : 'none';
                });
            }

            // Next button functionality
            document.getElementById('next-btn').addEventListener('click', function() {
                if (currentSlide < slides.length - 1) {
                    currentSlide++;
                    showSlide(currentSlide);
                }
            });

            // Previous button functionality
            document.getElementById('prev-btn').addEventListener('click', function() {
                if (currentSlide > 0) {
                    currentSlide--;
                    showSlide(currentSlide);
                }
            });

            // Star rating functionality
            const starLabels = document.querySelectorAll('.star');
            starLabels.forEach(star => {
                star.addEventListener('click', function() {
                    const stars = this.parentElement.querySelectorAll('.star');
                    stars.forEach(s => s.style.color = '#ddd');
                    this.style.color = '#FFD700';
                    let prevSibling = this.previousElementSibling;
                    while(prevSibling) {
                        if (prevSibling.tagName === 'LABEL') {
                            prevSibling.style.color = '#FFD700';
                        }
                        prevSibling = prevSibling.previousElementSibling;
                    }
                });
            });
        });
    </script>
</body>
</html>