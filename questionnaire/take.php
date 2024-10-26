<?php
// /questionnaire/take.php

define('ALLOW_ACCESS', true);
include '../includes/db_connect.php';
include '../includes/functions.php';

// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get questionnaire ID from URL
if (isset($_GET['id'])) {
    $questionnaire_id = (int)$_GET['id'];

    // Fetch questionnaire
    $stmt = $pdo->prepare('SELECT * FROM questionnaires WHERE questionnaire_id = ?');
    $stmt->execute([$questionnaire_id]);
    $questionnaire = $stmt->fetch();

    // Fetch questions
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE questionnaire_id = ? ORDER BY question_id');
    $stmt->execute([$questionnaire_id]);
    $questions = $stmt->fetchAll();

    if (!$questionnaire) {
        // If no questionnaire found, redirect to index
        header('Location: index.php');
        exit();
    }
} else {
    // Redirect to index if no ID is provided
    header('Location: index.php');
    exit();
}

// Handle form submission
$submission_success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_survey'])) {
    $answers = $_POST['answers'] ?? [];
    $questionnaire_id = (int)$_POST['questionnaire_id'];

    // Validate all required answers
    foreach ($questions as $question) {
        if (empty($answers[$question['question_id']])) {
            $errors[] = "الرجاء الإجابة على السؤال: " . htmlspecialchars($question['question_text']);
        }
    }

    if (empty($errors)) {
        try {
            // Begin transaction
            $pdo->beginTransaction();

            // Insert into responses table
            $stmt = $pdo->prepare('INSERT INTO responses (questionnaire_id) VALUES (?)');
            $stmt->execute([$questionnaire_id]);
            $response_id = $pdo->lastInsertId();

            // Insert each answer
            $stmt = $pdo->prepare('INSERT INTO answers (response_id, question_id, answer_text) VALUES (?, ?, ?)');
            foreach ($answers as $question_id => $answer) {
                // Sanitize inputs
                $sanitized_answer = sanitizeInput($answer);

                $stmt->execute([$response_id, $question_id, $sanitized_answer]);
            }

            // Commit transaction
            $pdo->commit();

            // Mark submission as successful
            $submission_success = true;
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $errors[] = "حدث خطأ أثناء تقديم الاستبيان: " . $e->getMessage();
        }
    }
}
?>
<?php
// /includes/header.php

if (!defined('ALLOW_ACCESS')) {
    die('Direct access not permitted.');
}

// Session already started above

// Set background style
if (!empty($questionnaire['background_color'])) {
    // User has selected a background color
    $background_style = "background-color: " . htmlspecialchars($questionnaire['background_color']) . ";";
} elseif (!empty($questionnaire['background_path'])) {
    // User has provided a background image
    $background_style = "background-image: url('" . "../" . htmlspecialchars($questionnaire['background_path']) . "'); background-size: cover; background-repeat: no-repeat; background-position: center;";
} else {
    // Use default background image
    $background_style = "background-image: url('../assets/images/default-background.jpg'); background-size: cover; background-repeat: no-repeat; background-position: center;";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>برنامج الاستبيان</title>

    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/styles.css">

    <style>
        /* Dynamic Background Style */
        .cover {
            <?php echo $background_style; ?>
            min-height: 100vh; /* Ensure it covers the viewport height */
            margin:0px;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        /* Logo Styling */
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            max-height: 100px;
        }

        /* Additional Custom Styles */
        .take-container {
            background-color: <?php
        $color = htmlspecialchars($questionnaire['font_color']);
        // Convert hex to rgb values
        $rgb = sscanf($color, "#%02x%02x%02x");
        echo "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, 0.2)";
    ?> !important;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 50px;
            width: 100%;
            max-width: 800px;
        }

        /* Apply Selected Font Color */
        body, .card, .card-body, .form-group, .card-title, .card-text {
            color: <?php echo htmlspecialchars($questionnaire['font_color']); ?> !important;
        }

        /* Star Rating Styling */
        .star-rating .form-check-label {
            font-size: 1.5rem;
            cursor: pointer;
            color: #aaa;
            transition: color 0.2s;
        }

        .star-rating .form-check-input:checked ~ .form-check-label,
        .star-rating .form-check-label:hover,
        .star-rating .form-check-label:hover ~ .form-check-label {
            color: #ffc107; /* Golden color for selected stars */
        }

        #godOrNot{
            width: 98%;
            display: flex;
            justify-content:space-between;
            padding-left:40px;
            padding-right:40px;
        }

        #godOrNot .rating-label {
            color: #555;
        }
        .card{
           background: rgba(255, 255, 255, 0.95) !important;
        }

        /* Responsive star rating styles */
        .star-rating-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            flex-wrap:wrap;
        }

        .star-rating {
            display: flex !important;
            justify-content: center;
            flex: 1;
            margin: 0 10px;
        }

        .star-rating .form-check {
            margin: 0;
            padding: 0;
            flex: 1;
        }

        .star-rating .form-check-label {
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            color: #aaa;
            font-size: calc(1rem + 1vw);
            transition: color 0.2s, transform 0.2s;
        }

        .star-rating .form-check-input:checked ~ .form-check-label,
        .star-rating .form-check-input:focus ~ .form-check-label,
        .star-rating .form-check-input:checked + .form-check-label,
        .star-rating .form-check-input:focus + .form-check-label {
            color: #ffc107;
        }

        .rating-label {
            white-space: nowrap;
            font-weight: bold;
            font-size: calc(0.8rem + 0.5vw);
        }

        /* Hover effect */
        .star-rating .form-check-label:hover {
            transform: scale(1.1);
        }

        @media (max-width: 576px) {
            .star-rating-container {
                flex-direction: column;
            }
            .rating-label {
                margin: 5px 0;
            }
        }

        /* Welcome and Thank You Styles */
        .message-container {
            display: none; /* Hidden by default */
            text-align: center;
        }

        .message-container.active {
            display: block;
        }

        .message-container button {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<main class="cover">

    <div class="container take-container" dir="rtl">

        <!-- Display Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Welcome Message Section -->
        <?php if (!$submission_success && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
            <div id="welcomeSection" class="message-container active">
                <div class="card mb-4 bg-light">
                    <div class="card-body">
                        <!-- Dynamic Logo -->
                        <?php if (!empty($questionnaire['logo_path']) && file_exists('../' . $questionnaire['logo_path'])): ?>
                            <div class="logo-container">
                                <img class="logo" src="<?php echo "../" . htmlspecialchars($questionnaire['logo_path']); ?>" alt="Logo"/>
                            </div>
                        <?php else: ?>
                            <div class="logo-container">
                                <img class="logo" src="https://picsum.photos/id/1/200/300" width="80px" alt="logo"/>
                            </div>
                        <?php endif; ?>

                        <!-- Title and Description -->
                        <h2 class="card-title text-center"><?php echo htmlspecialchars($questionnaire['title']); ?></h2>
                        <p class="card-text text-center"><?php echo htmlspecialchars($questionnaire['description']); ?></p>

                        <!-- Welcome Message -->
                        <p class="card-text text-center fw-bold"><?php echo htmlspecialchars($questionnaire['welcome']); ?></p>
                        <button id="startBtn" class="btn btn-primary">ابدأ</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Survey Form Section -->
        <?php if (!$submission_success): ?>
            <div id="surveySection" class="message-container" style="display: <?php echo ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) ? 'none' : 'none'; ?>;">
                <form action="take.php?id=<?php echo $questionnaire_id; ?>" method="post" id="questionForm" class="needs-validation" novalidate>
                    <div class="card mb-4 bg-light">
                        <div class="card-body">
                            <!-- Dynamic Logo -->
                            <?php if (!empty($questionnaire['logo_path']) && file_exists('../' . $questionnaire['logo_path'])): ?>
                                <div class="logo-container">
                                    <img class="logo" src="<?php echo "../" . htmlspecialchars($questionnaire['logo_path']); ?>" alt="Logo"/>
                                </div>
                            <?php else: ?>
                                <div class="logo-container">
                                    <img class="logo" src="https://picsum.photos/id/1/200/300" width="80px" alt="logo"/>
                                </div>
                            <?php endif; ?>

                            <!-- Title and Description -->
                            <h2 class="card-title text-center"><?php echo htmlspecialchars($questionnaire['title']); ?></h2>
                            <p class="card-text text-center"><?php echo htmlspecialchars($questionnaire['description']); ?></p>
                        </div>
                    </div>

                    <div class="slider">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="slider-item" data-index="<?php echo $index; ?>" style="<?php echo $index === 0 ? '' : 'display:none;'; ?>">
                                <div class="card mb-4 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo ($index + 1) . '. ' . htmlspecialchars($question['question_text']); ?></h5>
                                        <?php if ($question['question_type'] == 'text'): ?>
                                            <input type="text" name="answers[<?php echo $question['question_id']; ?>]" class="form-control" required>
                                            <div class="invalid-feedback">
                                                الرجاء تقديم إجابة.
                                            </div>
                                        <?php elseif ($question['question_type'] == 'textarea'): ?>
                                            <textarea name="answers[<?php echo $question['question_id']; ?>]" class="form-control" rows="4" required></textarea>
                                            <div class="invalid-feedback">
                                                الرجاء تقديم إجابة.
                                            </div>
                                        <?php elseif ($question['question_type'] == 'choice'): ?>
                                            <?php
                                                // Fetch choices for the question
                                                $stmt_choices = $pdo->prepare('SELECT * FROM choices WHERE question_id = ?');
                                                $stmt_choices->execute([$question['question_id']]);
                                                $choices = $stmt_choices->fetchAll();

                                                // Get the questionnaire font color and create rgba version
                                                $color = htmlspecialchars($questionnaire['font_color']);
                                                $rgb = sscanf($color, "#%02x%02x%02x");
                                                $rgba_bg = "rgba({$rgb[0]}, {$rgb[1]}, {$rgb[2]}, 0.05)";
                                            ?>
                                            <div class="choices-container">
                                                <?php foreach ($choices as $choice): ?>
                                                    <div class="choice-wrapper">
                                                        <input class="choice-input" type="radio"
                                                               name="answers[<?php echo $question['question_id']; ?>]"
                                                               value="<?php echo $choice['choice_id']; ?>"
                                                               id="choice<?php echo $question['question_id'] . '_' . $choice['choice_id']; ?>"
                                                               required>
                                                        <label class="choice-label"
                                                               for="choice<?php echo $question['question_id'] . '_' . $choice['choice_id']; ?>"
                                                               style="--font-color: <?php echo $color; ?>; --bg-color: <?php echo $rgba_bg; ?>">
                                                            <div class="choice-radio"></div>
                                                            <span class="choice-text"><?php echo htmlspecialchars($choice['choice_text']); ?></span>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                                <div class="invalid-feedback">
                                                    الرجاء اختيار خيار.
                                                </div>
                                            </div>
                                        <?php elseif ($question['question_type'] == 'stars'): ?>
                                            <div class="star-rating-container">
                                                <div class="star-rating d-flex justify-content-center" id="star-rating-<?php echo $question['question_id']; ?>">
                                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" style='display:none' type="radio" id="star<?php echo $i; ?>_<?php echo $question['question_id']; ?>" name="answers[<?php echo $question['question_id']; ?>]" value="<?php echo $i; ?>" required />
                                                            <label class="form-check-label" for="star<?php echo $i; ?>_<?php echo $question['question_id']; ?>">
                                                                &#9733;
                                                            </label>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>
                                                <div id="godOrNot">
                                                    <span class="rating-label">سيء</span>
                                                    <span class="rating-label">جيد</span>
                                                </div>
                                                <div class="invalid-feedback" style="display: none;">
                                                    الرجاء اختيار تقييم.
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <input type="hidden" name="questionnaire_id" value="<?php echo $questionnaire_id; ?>">

                    <div class="d-flex justify-content-between">
                        <button type="button" id="nextBtn" class="btn btn-primary">التالي</button>
                        <div>
                            <button type="button" id="prevBtn" class="btn btn-secondary" style="display:none;">السابق</button>
                            <button type="submit" name="submit_survey" id="submitBtn" class="btn btn-success" style="display:none;">إرسال</button>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Thank You Message Section -->
        <?php if ($submission_success): ?>
            <div id="thankYouSection" class="message-container active">
                <div class="card mb-4 bg-light">
                    <div class="card-body">
                        <!-- Dynamic Logo -->
                        <?php if (!empty($questionnaire['logo_path']) && file_exists('../' . $questionnaire['logo_path'])): ?>
                            <div class="logo-container">
                                <img class="logo" src="<?php echo "../" . htmlspecialchars($questionnaire['logo_path']); ?>" alt="Logo"/>
                            </div>
                        <?php else: ?>
                            <div class="logo-container">
                                <img class="logo" src="https://picsum.photos/id/1/200/300" width="80px" alt="logo"/>
                            </div>
                        <?php endif; ?>

                        <!-- Thank You Message -->
                        <h2 class="card-title text-center">شكرًا لك!</h2>
                        <p class="card-text text-center"><?php echo htmlspecialchars($questionnaire['thanks']); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>


    <!-- Enhanced Multiple Choice Styling with Dynamic Colors -->
    <style>
        .choices-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .choice-wrapper {
            position: relative;
            transition: all 0.3s ease;
        }

        .choice-input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .choice-label {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            background-color: rgba(255, 255, 255, 0.9);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0;
            gap: 1rem;
        }

        .choice-radio {
            width: 24px;
            height: 24px;
            border: 2px solid #cbd5e0;
            border-radius: 50%;
            position: relative;
            transition: all 0.3s ease;
        }

        .choice-radio::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            width: 12px;
            height: 12px;
            background-color: var(--font-color);
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .choice-text {
            flex: 1;
            font-size: 1rem;
            color: #4a5568;
            transition: all 0.3s ease;
        }

        /* Hover State */
        .choice-label:hover {
            border-color: var(--font-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .choice-label:hover .choice-radio {
            border-color: var(--font-color);
        }

        /* Selected State */
        .choice-input:checked + .choice-label {
            border-color: var(--font-color);
            background-color: var(--bg-color);
        }

        .choice-input:checked + .choice-label .choice-radio {
            border-color: var(--font-color);
        }

        .choice-input:checked + .choice-label .choice-radio::after {
            transform: translate(-50%, -50%) scale(1);
        }

        .choice-input:checked + .choice-label .choice-text {
            color: var(--font-color);
            font-weight: 500;
        }

        /* Focus State */
        .choice-input:focus + .choice-label {
            outline: 2px solid var(--font-color);
            outline-offset: 2px;
        }

        /* Error State */
        .choice-input.is-invalid + .choice-label {
            border-color: #dc3545;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .choice-label {
                padding: 0.75rem 1rem;
            }

            .choice-radio {
                width: 20px;
                height: 20px;
            }

            .choice-radio::after {
                width: 10px;
                height: 10px;
            }

            .choice-text {
                font-size: 0.9rem;
            }
        }

        /* Animation for selection */
        @keyframes selectChoice {
            0% {
                transform: scale(0.95);
            }
            50% {
                transform: scale(1.02);
            }
            100% {
                transform: scale(1);
            }
        }

        .choice-input:checked + .choice-label {
            animation: selectChoice 0.3s ease forwards;
        }
    </style>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!$submission_success && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
                const startBtn = document.getElementById('startBtn');
                const welcomeSection = document.getElementById('welcomeSection');
                const surveySection = document.getElementById('surveySection');

                startBtn.addEventListener('click', function() {
                    // Hide welcome section
                    welcomeSection.style.display = 'none';
                    // Show survey section
                    surveySection.style.display = 'block';
                });
            <?php endif; ?>

            <?php if (!$submission_success): ?>
                const totalQuestions = <?php echo count($questions); ?>;
                let currentQuestion = 0;

                const sliderItems = document.querySelectorAll('.slider-item');
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const submitBtn = document.getElementById('submitBtn');
                const form = document.getElementById('questionForm');

                function showQuestion(index) {
                    sliderItems.forEach((item, idx) => {
                        item.style.display = idx === index ? 'block' : 'none';
                    });
                    // Update button visibility
                    if (index === 0) {
                        prevBtn.style.display = 'none';
                    } else {
                        prevBtn.style.display = 'inline-block';
                    }

                    if (index === totalQuestions - 1) {
                        nextBtn.style.display = 'none';
                        submitBtn.style.display = 'inline-block';
                    } else {
                        nextBtn.style.display = 'inline-block';
                        submitBtn.style.display = 'none';
                    }
                }

                nextBtn.addEventListener('click', function() {
                    // Validate current question before moving to next
                    const currentSlider = sliderItems[currentQuestion];
                    const inputs = currentSlider.querySelectorAll('input, textarea');
                    let valid = true;
                    inputs.forEach(input => {
                        if (!input.checkValidity()) {
                            valid = false;
                            input.classList.add('is-invalid');
                            // Show invalid feedback for star rating
                            if (input.type === 'radio' && input.name.startsWith('answers[')) {
                                const starRatingContainer = input.closest('.star-rating-container');
                                if (starRatingContainer) {
                                    const invalidFeedback = starRatingContainer.querySelector('.invalid-feedback');
                                    if (invalidFeedback) {
                                        invalidFeedback.style.display = 'block';
                                    }
                                }
                            }
                        } else {
                            input.classList.remove('is-invalid');
                            // Hide invalid feedback for star rating if input is valid
                            if (input.type === 'radio' && input.name.startsWith('answers[')) {
                                const starRatingContainer = input.closest('.star-rating-container');
                                if (starRatingContainer) {
                                    const invalidFeedback = starRatingContainer.querySelector('.invalid-feedback');
                                    if (invalidFeedback) {
                                        invalidFeedback.style.display = 'none';
                                    }
                                }
                            }
                        }
                    });
                    if (!valid) {
                        return;
                    }
                    currentQuestion++;
                    if (currentQuestion >= totalQuestions) currentQuestion = totalQuestions - 1;
                    showQuestion(currentQuestion);
                });

                prevBtn.addEventListener('click', function() {
                    currentQuestion--;
                    if (currentQuestion < 0) currentQuestion = 0;
                    showQuestion(currentQuestion);
                });

                showQuestion(currentQuestion);

                // Form validation on submit
                form.addEventListener('submit', function(event) {
                    // Validate all questions before submitting
                    let valid = true;
                    sliderItems.forEach(item => {
                        const inputs = item.querySelectorAll('input, textarea');
                        inputs.forEach(input => {
                            if (!input.checkValidity()) {
                                valid = false;
                                input.classList.add('is-invalid');
                            } else {
                                input.classList.remove('is-invalid');
                            }
                        });
                    });
                    if (!valid) {
                        event.preventDefault();
                        event.stopPropagation();
                        form.classList.add('was-validated');
                        // Optionally, navigate to the first invalid question
                        sliderItems.forEach((item, idx) => {
                            const invalidInput = item.querySelector('.is-invalid');
                            if (invalidInput && idx !== currentQuestion) {
                                currentQuestion = idx;
                                showQuestion(currentQuestion);
                                invalidInput.focus();
                                return false;
                            }
                        });
                        return;
                    }

                    // Allow form to submit normally
                    // After submission, the page reloads and shows the thank you message
                }, false);

                // Enhanced star rating interactivity
                const starRatings = document.querySelectorAll('.star-rating');
                starRatings.forEach(rating => {
                    const inputs = rating.querySelectorAll('input[type="radio"]');
                    const labels = rating.querySelectorAll('label');

                    inputs.forEach(input => {
                        input.addEventListener('change', function() {
                            // Reset all stars to gray
                            labels.forEach(label => {
                                label.style.color = '#aaa';
                            });
                            // Highlight selected stars
                            const selectedValue = parseInt(this.value, 10);
                            labels.forEach((label, index) => {
                                if (index < selectedValue) {
                                    label.style.color = '#ffc107'; // Yellow color for selected stars
                                }
                            });
                            // Hide invalid feedback when a star is selected
                            const starRatingContainer = this.closest('.star-rating-container');
                            if (starRatingContainer) {
                                const invalidFeedback = starRatingContainer.querySelector('.invalid-feedback');
                                if (invalidFeedback) {
                                    invalidFeedback.style.display = 'none';
                                }
                            }
                        });
                    });

                    // Add hover effect
                    labels.forEach((label, index) => {
                        label.addEventListener('mouseover', () => {
                            labels.forEach((l, i) => {
                                l.style.color = i <= index ? '#ffc107' : '#aaa';
                            });
                        });
                        label.addEventListener('mouseout', () => {
                            const selectedInput = rating.querySelector('input:checked');
                            if (selectedInput) {
                                const selectedValue = parseInt(selectedInput.value, 10);
                                labels.forEach((l, i) => {
                                    l.style.color = i < selectedValue ? '#ffc107' : '#aaa';
                                });
                            } else {
                                labels.forEach(l => l.style.color = '#aaa');
                            }
                        });
                    });
                });
            <?php endif; ?>
        });
    </script>
</main>

</body>
</html>