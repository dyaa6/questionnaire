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

    <!-- Modified Tabs -->
    <ul class="nav nav-tabs mb-4 flex-column flex-sm-row" id="chartTypeTabs" role="tablist">
        <li class="nav-item flex-sm-fill text-sm-center" role="presentation">
            <button class="nav-link active" id="bar-tab" data-bs-toggle="tab" data-chart-type="bar" type="button" role="tab" aria-controls="bar" aria-selected="true">الرسم البياني العمودي</button>
        </li>
        <li class="nav-item flex-sm-fill text-sm-center" role="presentation">
            <button class="nav-link" id="pie-tab" data-bs-toggle="tab" data-chart-type="pie" type="button" role="tab" aria-controls="pie" aria-selected="false">الرسم البياني الدائري</button>
        </li>
        <li class="nav-item flex-sm-fill text-sm-center" role="presentation">
            <button class="nav-link" id="line-tab" data-bs-toggle="tab" data-chart-type="line" type="button" role="tab" aria-controls="line" aria-selected="false">الرسم البياني الخطي</button>
        </li>
    </ul>

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

                    <canvas id="chart-<?php echo $question['question_id']; ?>"></canvas>

                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

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

    <div class="mt-5 text-center">
        <a href="export_csv.php?id=<?php echo $questionnaire_id; ?>&format=excel" class="btn btn-primary">تصدير البيانات إلى ملف xls</a>
    </div>
    <div class="mt-5 text-center">
        <a href="export_csv.php?id=<?php echo $questionnaire_id; ?>&format=csv" class="btn btn-primary">تصدير البيانات إلى ملف csv</a>
    </div>

</div>

<!-- Optional: Add custom CSS for small screens -->
<style>
@media (max-width: 576px) {
    .nav-tabs .nav-link {
        font-size: 0.9rem; /* Adjust the font size as needed */
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const chartData = <?php echo json_encode($chartData); ?>;
    const charts = {}; // لتخزين مراجع الرسوم البيانية
    let currentChartType = 'bar'; // النوع الافتراضي

    // وظيفة لإنشاء الرسم البياني بناءً على النوع المحدد
    function createCharts(chartType) {
        // تكرار لكل سؤال لإنشاء الرسم البياني المناسب
        Object.keys(chartData).forEach(function(questionId) {
            const ctx = document.getElementById('chart-' + questionId).getContext('2d');
            const data = chartData[questionId];

            // إذا كان هناك رسم بياني موجود بالفعل، قم بتدميره
            if (charts[questionId]) {
                charts[questionId].destroy();
            }

            // إعداد البيانات للرسم البياني
            let chartConfig = {
                type: chartType,
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        label: 'عدد الإجابات',
                        data: Object.values(data),
                        backgroundColor: generateColors(chartType, Object.keys(data).length),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        fill: chartType === 'line' ? false : true,
                        tension: 0.1
                    }]
                },
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: 'توزيع التقييمات'
                        },
                        legend: {
                            display: chartType !== 'bar' // عرض الأسطورة باستثناء الرسم البياني العمودي
                        }
                    },
                    scales: chartType === 'pie' ? {} : {
                        y: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }
                }
            };

            // إنشاء الرسم البياني وتخزين المرجع
            charts[questionId] = new Chart(ctx, chartConfig);
        });
    }

    // وظيفة لتوليد ألوان مختلفة بناءً على عدد الفئات ونوع الرسم البياني
    function generateColors(chartType, num) {
        const colors = [
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)',
            'rgba(199, 199, 199, 0.6)',
            'rgba(83, 102, 255, 0.6)',
            'rgba(255, 102, 255, 0.6)',
            'rgba(102, 255, 178, 0.6)'
        ];
        if (chartType === 'pie' || chartType === 'doughnut') {
            return colors.slice(0, num);
        } else {
            // للأعمدة والخط، يمكن استخدام لون واحد أو تدرج ألوان
            return 'rgba(54, 162, 235, 0.6)';
        }
    }

    // إنشاء الرسوم البيانية عند تحميل الصفحة بالنوع الافتراضي
    document.addEventListener('DOMContentLoaded', function() {
        createCharts(currentChartType);

        // إضافة مستمع للنقر على التبويبات لتغيير نوع الرسم البياني
        const chartTypeTabs = document.querySelectorAll('#chartTypeTabs .nav-link');
        chartTypeTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                // إزالة الفئة النشطة من جميع التبويبات
                chartTypeTabs.forEach(function(t) {
                    t.classList.remove('active');
                });
                // إضافة الفئة النشطة إلى التبويبة المحددة
                this.classList.add('active');
                // تحديث نوع الرسم البياني الحالي
                currentChartType = this.getAttribute('data-chart-type');
                // إعادة إنشاء الرسوم البيانية بالنوع الجديد
                createCharts(currentChartType);
            });
        });
    });
</script>

<?php
include '../../includes/footer.php';
?>