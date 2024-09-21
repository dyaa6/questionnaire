<?php
// /questionnaire/view.php

define('ALLOW_ACCESS', true);
include '../../includes/header.php';
include '../../includes/db_connect.php';
include '../../includes/functions.php';

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

    // For each question, fetch answers and collect data
    $chartData = [];
    $textAnswers = [];

    foreach ($questions as &$question) {
        // Fetch answers for each question
        $stmt = $pdo->prepare('SELECT * FROM answers WHERE question_id = ?');
        $stmt->execute([$question['question_id']]);
        $question['answers'] = $stmt->fetchAll();

        if ($question['question_type'] == 'stars') {
            // Prepare data for star rating questions
            $ratings = array_fill(1, 10, 0); // Initialize counts for ratings 1-10
            foreach ($question['answers'] as $answer) {
                $rating = (int)$answer['answer_text'];
                if ($rating >= 1 && $rating <= 10) {
                    $ratings[$rating]++;
                }
            }
            $chartData[$question['question_id']] = $ratings;
        } elseif ($question['question_type'] == 'textarea') {
            // Collect text answers for display at the end
            $textAnswers[] = [
                'question_text' => $question['question_text'],
                'answers' => $question['answers']
            ];
        }
    }
} else {
    // Redirect to index if no ID is provided
    header('Location: index.php');
    exit();
}
?>
<div class="container mt-5" dir="rtl">
    <h2><?php echo htmlspecialchars($questionnaire['title']); ?> - الإحصائيات</h2>
    <p><?php echo htmlspecialchars($questionnaire['description']); ?></p>

    <?php foreach ($questions as $question): ?>
        <?php if ($question['question_type'] == 'stars'): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($question['question_text']); ?></h5>

                    <?php
                    // Calculate average rating
                    $total = 0;
                    $count = count($question['answers']);
                    foreach ($question['answers'] as $answer) {
                        $total += (int)$answer['answer_text'];
                    }
                    $average = $count ? round($total / $count, 2) : 0;
                    ?>

                    <p>متوسط التقييم: <strong><?php echo $average; ?></strong> من 10</p>

                    <!-- Graph Placeholder -->
                    <canvas id="chart-<?php echo $question['question_id']; ?>"></canvas>

                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Display text answers at the end -->
    <div class="mt-5">
        <h3>الإجابات النصية</h3>
        <?php if (!empty($textAnswers)): ?>
            <?php foreach ($textAnswers as $textAnswer): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($textAnswer['question_text']); ?></h5>
                        <?php if (!empty($textAnswer['answers'])): ?>
                            <ul>
                                <?php foreach ($textAnswer['answers'] as $answer): ?>
                                    <li><?php echo htmlspecialchars($answer['answer_text']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>لا توجد إجابات حتى الآن.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>لا توجد إجابات نصية.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const chartData = <?php echo json_encode($chartData); ?>;

    // Iterate over each question's data to create charts
    Object.keys(chartData).forEach(function(questionId) {
        const ctx = document.getElementById('chart-' + questionId).getContext('2d');
        const data = chartData[questionId];
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    label: 'عدد الإجابات',
                    data: Object.values(data),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'توزيع التقييمات'
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>

<?php
include '../../includes/footer.php';
?>