<?php
// /questionnaire/view.php

define('ALLOW_ACCESS', true);
include '../../includes/header.php';
include '../../includes/db_connect.php';
include '../../includes/functions.php';

// Get questionnaire ID from URL
if (isset($_GET['id'])) {
    $questionnaire_id = (int)$_GET['id'];

    // Get filters
    $filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'all';
    $start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : null;
    $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : null;

    // Validate filter_type
    $valid_filter_types = ['all', 'period', 'limit'];
    if (!in_array($filter_type, $valid_filter_types)) {
        $filter_type = 'all';
    }

    // Fetch questionnaire
    $stmt = $pdo->prepare('SELECT * FROM questionnaires WHERE questionnaire_id = ?');
    $stmt->execute([$questionnaire_id]);
    $questionnaire = $stmt->fetch();

    // Fetch questions
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE questionnaire_id = ? ORDER BY question_id');
    $stmt->execute([$questionnaire_id]);
    $questions = $stmt->fetchAll();

    // Prepare response IDs based on filter
    $response_ids = [];

    if ($filter_type === 'period') {
        if ($start_date && $end_date) {
            // Fetch response IDs within the period
            $stmt = $pdo->prepare('SELECT response_id FROM responses WHERE questionnaire_id = ? AND submitted_at BETWEEN ? AND ? ORDER BY submitted_at DESC');
            $stmt->execute([$questionnaire_id, $start_date . ' 00:00:00', $end_date . ' 23:59:59']);
        } elseif ($start_date) {
            // Fetch response IDs from start_date onwards
            $stmt = $pdo->prepare('SELECT response_id FROM responses WHERE questionnaire_id = ? AND submitted_at >= ? ORDER BY submitted_at DESC');
            $stmt->execute([$questionnaire_id, $start_date . ' 00:00:00']);
        } elseif ($end_date) {
            // Fetch response IDs up to end_date
            $stmt = $pdo->prepare('SELECT response_id FROM responses WHERE questionnaire_id = ? AND submitted_at <= ? ORDER BY submitted_at DESC');
            $stmt->execute([$questionnaire_id, $end_date . ' 23:59:59']);
        } else {
            // If no dates provided, treat as 'all'
            $filter_type = 'all';
        }

        if ($filter_type === 'period') {
            $responses = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $response_ids = $responses ? $responses : [];
        }
    } elseif ($filter_type === 'limit' && $limit > 0) {
        // Fetch the latest 'limit' response IDs
        $stmt = $pdo->prepare('SELECT response_id FROM responses WHERE questionnaire_id = ? ORDER BY submitted_at DESC LIMIT ?');
        $stmt->execute([$questionnaire_id, $limit]);
        $responses = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $response_ids = $responses ? $responses : [];
    }

    // Depending on filter_type, prepare the SQL condition for answers
    $apply_filters = false;
    $sql_filter = '';

    if ($filter_type === 'period' && !empty($response_ids)) {
        $apply_filters = true;
        // Use IN clause to filter by response_ids
        // To prevent SQL injection with variable number of parameters, use placeholders
        $in_placeholders = implode(',', array_fill(0, count($response_ids), '?'));
        $sql_filter = "AND a.response_id IN ($in_placeholders)";
    } elseif ($filter_type === 'limit' && !empty($response_ids)) {
        $apply_filters = true;
        // Use IN clause to filter by response_ids
        $in_placeholders = implode(',', array_fill(0, count($response_ids), '?'));
        $sql_filter = "AND a.response_id IN ($in_placeholders)";
    }

    // For each question, fetch answers and collect data
    $chartData = [];
    $textAnswers = [];

    foreach ($questions as &$question) {
        // Build the SQL query with possible filters
        $sql = 'SELECT a.* FROM answers a 
                JOIN responses r ON a.response_id = r.response_id 
                WHERE a.question_id = ? ';
        $params = [$question['question_id']];

        // Apply filters if any
        if ($apply_filters) {
            $sql .= $sql_filter;
            $params = array_merge($params, $response_ids);
        }

        // Order by submission date descending
        $sql .= ' ORDER BY r.submitted_at DESC';

        // Apply limit if 'all' is selected; Since 'limit' is already handled via response IDs, no need here

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
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
<link rel="stylesheet" href="../../assets/css/styles.css">
<div class="container mt-5" dir="rtl">
    <!-- Wrap content in a container for PDF generation -->
    <div id="pdf-content">
        <h2><?php echo htmlspecialchars($questionnaire['title']); ?> - الإحصائيات</h2>
        <p><?php echo htmlspecialchars($questionnaire['description']); ?></p>

        <!-- Filtering Form -->
        <form method="get" action="">
            <input type="hidden" name="id" value="<?php echo $questionnaire_id; ?>">
            <div class="mb-4">
                <label class="form-label">تحديد طريقة العرض:</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="filter_type" id="filter_all" value="all" <?php echo ($filter_type == 'all' || !$filter_type) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filter_all">
                        عرض كل الردود
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="filter_type" id="filter_period" value="period" <?php echo ($filter_type == 'period') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filter_period">
                        عرض الردود لفترة معينة
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="filter_type" id="filter_limit" value="limit" <?php echo ($filter_type == 'limit') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="filter_limit">
                        عرض آخر عدد من الردود
                    </label>
                </div>
            </div>

            <!-- Period Inputs -->
            <div id="period_inputs" class="row mb-4" style="display: none;">
                <div class="col-md-6">
                    <label for="start_date" class="form-label">من تاريخ:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">إلى تاريخ:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
            </div>

            <!-- Limit Input -->
            <div id="limit_input" class="mb-4" style="display: none;">
                <label for="limit" class="form-label">عدد الردود الأخيرة:</label>
                <input type="number" id="limit" name="limit" class="form-control" min="1" value="<?php echo htmlspecialchars($limit); ?>">
            </div>

            <button type="submit" class="btn btn-primary">تطبيق الفلاتر</button>
        </form>
        <br>
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
    </div> <!-- End of pdf-content -->

    <!-- Export Links -->
    <div class="mt-5 text-center">
        <a href="export_csv.php?id=<?php echo $questionnaire_id; ?>&format=excel&filter_type=<?php echo urlencode($filter_type); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&limit=<?php echo urlencode($limit); ?>" class="btn btn-primary">تصدير البيانات إلى ملف xls</a>
        <a href="export_csv.php?id=<?php echo $questionnaire_id; ?>&format=csv&filter_type=<?php echo urlencode($filter_type); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&limit=<?php echo urlencode($limit); ?>" class="btn btn-primary">تصدير البيانات إلى ملف csv</a>
        <!-- PDF Download Button -->
        <button id="download-pdf" class="btn btn-primary mt-3">تحميل التقرير كملف PDF</button>
    </div>
    <br>
</div>

<!-- Optional: Add custom CSS for small screens -->
<style>
@media (max-width: 576px) {
    .nav-tabs .nav-link {
        font-size: 0.9rem; /* Adjust the font size as needed */
    }
}
.form-label {
    font-weight: bold;
}
#period_inputs, #limit_input {
    margin-top: 15px;
}
</style>

<!-- Include Chart.js, Bootstrap JS, and html2pdf.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<!-- JavaScript to handle chart creation, filter form, and PDF generation -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter form logic
        const filterTypeRadios = document.getElementsByName('filter_type');
        const periodInputs = document.getElementById('period_inputs');
        const limitInput = document.getElementById('limit_input');

        function updateFilterFields() {
            const selectedFilter = document.querySelector('input[name="filter_type"]:checked').value;
            if (selectedFilter === 'all') {
                periodInputs.style.display = 'none';
                limitInput.style.display = 'none';
            } else if (selectedFilter === 'period') {
                periodInputs.style.display = 'flex';
                limitInput.style.display = 'none';
            } else if (selectedFilter === 'limit') {
                periodInputs.style.display = 'none';
                limitInput.style.display = 'block';
            }
        }

        // Initialize the form fields on page load
        updateFilterFields();

        filterTypeRadios.forEach(function(radio) {
            radio.addEventListener('change', updateFilterFields);
        });

        // Chart creation logic
        const chartData = <?php echo json_encode($chartData); ?>;
        const charts = {}; // To store chart instances
        let currentChartType = 'bar'; // Default chart type

        // Function to create charts based on the selected type
        function createCharts(chartType) {
            // Iterate over each question to create the appropriate chart
            Object.keys(chartData).forEach(function(questionId) {
                const ctx = document.getElementById('chart-' + questionId).getContext('2d');
                const data = chartData[questionId];

                // If a chart already exists, destroy it
                if (charts[questionId]) {
                    charts[questionId].destroy();
                }

                // Prepare the chart configuration
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
                                display: chartType !== 'bar' // Show legend except for bar charts
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

                // Create the chart and store the instance
                charts[questionId] = new Chart(ctx, chartConfig);
            });
        }

        // Function to generate different colors based on the number of categories and chart type
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
                // For bar and line charts, use a single color or gradient
                return 'rgba(54, 162, 235, 0.6)';
            }
        }

        // Create charts on page load with the default chart type
        createCharts(currentChartType);

        // Add event listeners to tabs to change the chart type
        const chartTypeTabs = document.querySelectorAll('#chartTypeTabs .nav-link');
        chartTypeTabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                chartTypeTabs.forEach(function(t) {
                    t.classList.remove('active');
                });
                // Add active class to the selected tab
                this.classList.add('active');
                // Update the current chart type
                currentChartType = this.getAttribute('data-chart-type');
                // Re-create charts with the new chart type
                createCharts(currentChartType);
            });
        });

        // PDF Generation Code
        document.getElementById('download-pdf').addEventListener('click', function() {
            const element = document.getElementById('pdf-content');

            const opt = {
                margin:       [10, 10, 10, 10], // Margins: [top, left, bottom, right] in mm
                filename:     'report.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, scrollY: 0 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            const downloadButton = document.getElementById('download-pdf');
            downloadButton.disabled = true;
            downloadButton.innerText = 'جارٍ إنشاء الملف...';

            html2pdf().set(opt).from(element).save().then(() => {
                downloadButton.disabled = false;
                downloadButton.innerText = 'تحميل التقرير كملف PDF';
            }).catch((error) => {
                console.error('Error generating PDF:', error);
                alert('حدث خطأ أثناء إنشاء ملف PDF. يرجى المحاولة مرة أخرى.');

                downloadButton.disabled = false;
                downloadButton.innerText = 'تحميل التقرير كملف PDF';
            });
        });
    });
</script>

<?php
include '../../includes/footer.php';
?>