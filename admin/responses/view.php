<?php
// /admin/responses/view.php
define('ALLOW_ACCESS', true);
include '../../includes/header.php';
include '../../includes/db_connect.php';
include '../../includes/functions.php';

// تحقق من تسجيل دخول المدير
checkAdminLogin();

// الحصول على معرف الرد من المعامل GET
$response_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// التحقق من صحة المعرف
if ($response_id <= 0) {
    echo "<div class='container mt-5'>
            <div class='alert alert-danger text-center' role='alert'>
                معرّف الرد غير صالح.
            </div>
          </div>";
    include '../../includes/footer.php';
    exit();
}

// جلب بيانات الرد من قاعدة البيانات
$stmt = $pdo->prepare('SELECT responses.*, questionnaires.title AS questionnaire_title FROM responses JOIN questionnaires ON responses.questionnaire_id = questionnaires.questionnaire_id WHERE responses.response_id = ?');
$stmt->execute([$response_id]);
$response = $stmt->fetch();

if (!$response) {
    echo "<div class='container mt-5'>
            <div class='alert alert-danger text-center' role='alert'>
                الرد غير موجود.
            </div>
          </div>";
    include '../../includes/footer.php';
    exit();
}

// جلب الأسئلة والإجابات المتعلقة بهذا الرد، بما في ذلك نوع السؤال
$stmt_questions = $pdo->prepare('SELECT questions.question_text, questions.question_type, answers.answer_text FROM answers JOIN questions ON answers.question_id = questions.question_id WHERE answers.response_id = ?');
$stmt_questions->execute([$response_id]);
$answers = $stmt_questions->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>عرض الرد</title>
    <!-- تضمين Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- أي أنماط مخصصة إضافية -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .response-details {
            margin-top: 30px;
        }
        .question {
            font-weight: bold;
        }
        .answer {
            margin-bottom: 20px;
        }
        .card{
            text-align:right;
            direction:rtl;
        }
    </style>
</head>
<body>

    <div class="container response-details">
        <div class="card">
            <div class="card-header bg-info text-white">
                عرض الرد
            </div>
            <div class="card-body">
                <h5 class="card-title">المعلومات الأساسية</h5>
                <p class="card-text"><strong>معرّف الرد:</strong> <?php echo htmlspecialchars($response['response_id']); ?></p>
                <p class="card-text"><strong>عنوان الاستبيان:</strong> <?php echo htmlspecialchars($response['questionnaire_title']); ?></p>
                <p class="card-text"><strong>تاريخ الرد:</strong> <?php echo htmlspecialchars($response['submitted_at']); ?></p>
                
                <hr>

                <h5 class="card-title">الإجابات</h5>
                <?php if (count($answers) > 0): ?>
                    <?php foreach ($answers as $answer): ?>
                        <?php
                        // معالجة الإجابة بناءً على نوع السؤال
                        $question_type = $answer['question_type'];
                        $display_answer = '';

                        if ($question_type == 'choice') {
                            // الإجابة تحتوي على معرف الخيار أو المعرفات
                            $choice_ids = explode(',', $answer['answer_text']);
                            $placeholders = implode(',', array_fill(0, count($choice_ids), '?'));

                            // جلب نصوص الخيارات
                            $stmt_choices = $pdo->prepare("SELECT choice_text FROM choices WHERE choice_id IN ($placeholders)");
                            $stmt_choices->execute($choice_ids);
                            $choices = $stmt_choices->fetchAll(PDO::FETCH_COLUMN);

                            // دمج نصوص الخيارات لعرضها
                            $display_answer = implode(', ', $choices);
                        } else if ($question_type == 'stars') {
                            // سؤال تقييم بالنجوم
                            $display_answer = $answer['answer_text'] . ' من 10';
                        } else {
                            // أنواع الأسئلة الأخرى (نصية)
                            $display_answer = $answer['answer_text'];
                        }
                        ?>
                        <div class="answer">
                            <p class="question"><?php echo htmlspecialchars($answer['question_text']); ?></p>
                            <p class="answer-text"><?php echo nl2br(htmlspecialchars($display_answer)); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">لا توجد إجابات لهذا الرد.</p>
                <?php endif; ?>

                <a href="../index.php" class="btn btn-primary mt-3">العودة إلى قائمة الاستبيانات</a>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <!-- تضمين Bootstrap JS والاعتمادات الأخرى إذا لزم الأمر -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>