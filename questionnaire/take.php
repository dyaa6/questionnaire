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
?>

<?php
// /includes/header.php

if (!defined('ALLOW_ACCESS')) {
    die('Direct access not permitted.');
}

// Session already started above
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>لوحة التحكم - برنامج الاستبيان</title>

    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/styles.css">

    <style>
        /* Dynamic Background Image */
        .cover {
            <?php if (!empty($questionnaire['background_path'])): ?>
                background-image: url('<?php echo "../" . htmlspecialchars($questionnaire['background_path']); ?>');
            <?php else: ?>
                background-image: url('../assets/images/default-background.jpg'); /* Ensure you have a default background image */
            <?php endif; ?>;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
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
            background-color: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 50px;
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
           background: rgba(255, 255, 255, 0.58) !important;
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
    </style>
</head>
<body>

<main class="cover">

    <div class="container take-container" dir="rtl">
        <div class="card mb-4 bg-light">
            <div class="card-body">
                <h2 class="card-title text-center"><?php echo htmlspecialchars($questionnaire['title']); ?></h2>
                
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

                <p class="card-text text-center"><?php echo htmlspecialchars($questionnaire['description']); ?></p>
            </div>
        </div>

        <form action="submit.php" method="post" id="questionForm" class="needs-validation" novalidate>
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
                                    ?>
                                    <?php foreach ($choices as $choice): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="answers[<?php echo $question['question_id']; ?>]" value="<?php echo htmlspecialchars($choice['choice_text']); ?>" id="choice<?php echo $question['question_id'] . '_' . $choice['choice_id']; ?>" required>
                                            <label class="form-check-label" for="choice<?php echo $question['question_id'] . '_' . $choice['choice_id']; ?>">
                                                <?php echo htmlspecialchars($choice['choice_text']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="invalid-feedback">
                                        الرجاء اختيار خيار.
                                    </div>
                                <?php elseif ($question['question_type'] == 'stars'): ?>
                                    <?php
                                        // Fetch choices for star rating if applicable
                                        // Assuming star rating is numerical from 1 to 10
                                    ?>
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
                <button type="button" id="prevBtn" class="btn btn-secondary" style="display:none;">السابق</button>
                <div>
                    <button type="button" id="nextBtn" class="btn btn-primary">التالي</button>
                    <button type="submit" id="submitBtn" class="btn btn-success" style="display:none;">إرسال</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                }
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
        });
    </script>

    <?php
    // include '../includes/footer.php';
    ?>

</main>

</body>
</html>