<?php
// /questionnaire/index.php

define('ALLOW_ACCESS', true);
include '../includes/header.php';
include '../includes/db_connect.php';
include '../includes/functions.php';

// Fetch questionnaires
$stmt = $pdo->query('SELECT * FROM questionnaires ORDER BY created_at DESC');
$questionnaires = $stmt->fetchAll();
?>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="card-title">
            الاستبيانات المتوفرة
        </h2>
    </div>
</div>

<?php if (count($questionnaires) > 0): ?>
    <div class="list-group">
        <?php foreach ($questionnaires as $questionnaire): ?>
            <a href="take.php?id=<?php echo $questionnaire['questionnaire_id']; ?>" class="list-group-item list-group-item-action">
                <?php echo htmlspecialchars($questionnaire['title']); ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        No questionnaires are available at the moment.
    </div>
<?php endif; ?>

<?php
include '../includes/footer.php';
?>