<?php
// /admin/responses/index.php
define('ALLOW_ACCESS', true);
include '../../includes/header.php';
include '../../includes/db_connect.php';
include '../../includes/functions.php';

// Check if admin is logged in
checkAdminLogin();

// Fetch all responses from the database
$stmt = $pdo->prepare('SELECT responses.response_id, responses.submitted_at, questionnaires.title AS questionnaire_title FROM responses JOIN questionnaires ON responses.questionnaire_id = questionnaires.questionnaire_id ORDER BY responses.submitted_at DESC');
$stmt->execute();
$responses = $stmt->fetchAll();

// Check for success message
$success_msg = '';
if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $success_msg = 'تم حذف الرد بنجاح.';
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>قائمة الردود</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Additional custom styles -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .table-responsive {
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="mt-5">
            <h2 class="text-center mb-4">قائمة الردود</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success text-center" role="alert">
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>

            <?php if (count($responses) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>معرّف الرد</th>
                                <th>عنوان الاستبيان</th>
                                <th>تاريخ التقديم</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($responses as $response): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($response['response_id']); ?></td>
                                    <td><?php echo htmlspecialchars($response['questionnaire_title']); ?></td>
                                    <td><?php echo htmlspecialchars($response['submitted_at']); ?></td>
                                    <td>
                                        <a href="view.php?id=<?php echo $response['response_id']; ?>" class="btn btn-sm btn-info">عرض</a>
                                        <a href="delete.php?id=<?php echo $response['response_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا الرد؟');">حذف</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">لا توجد ردود متاحة.</p>
            <?php endif; ?>
            <a href="../index.php" class="btn btn-link" style="text-align:right; width:100%;">العودة إلى لوحة التحكم</a>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>