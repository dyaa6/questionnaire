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
    echo "<p class='alert alert-danger'>Questionnaire not found.</p>";
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

<div class="container mt-5" dir="rtl">
    <h2>تحرير الاستبيان</h2>

    <h3>تفاصيل الاستبيان</h3>

    <form action="edit.php?id=<?php echo $questionnaire_id; ?>" method="post">
        <div class="form-group">
            <label for="title">العنوان</label>
            <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($questionnaire['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">الوصف</label>
            <textarea name="description" id="description" class="form-control" required><?php echo htmlspecialchars($questionnaire['description']); ?></textarea>
        </div>
        <button type="submit" name="update_details" class="btn btn-primary">تحديث التفاصيل</button>
    </form>

    <h3 class="mt-4">الأسئلة</h3>

    <div id="question-slider" class="mb-4">
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
        <button id="prev-btn" class="btn btn-secondary">السابق</button>
        <button id="next-btn" class="btn btn-primary">التالي</button>
    </div>

    <h3>إضافة سؤال جديد</h3>

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
        <button type="submit" name="add_question" class="btn btn-success">إضافة سؤال</button>
    </form>
</div>

<style>
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
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.question-slide');
        let currentSlide = 0;

        // Show the first slide
        slides[currentSlide].style.display = 'block';

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

<?php
include '../../includes/footer.php';
?>