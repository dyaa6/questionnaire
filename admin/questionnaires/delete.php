<?php
// /admin/questionnaires/delete.php
define('ALLOW_ACCESS', true);
include '../../includes/header.php';
include '../../includes/db_connect.php';
include '../../includes/functions.php';

// Check if admin is logged in
checkAdminLogin();

// Get the questionnaire ID from the GET parameter
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $questionnaire_id = (int)$_GET['id'];

    // Confirm that the questionnaire exists
    $stmt = $pdo->prepare('SELECT * FROM questionnaires WHERE questionnaire_id = ?');
    $stmt->execute([$questionnaire_id]);
    $questionnaire = $stmt->fetch();

    if ($questionnaire) {
        // Delete the questionnaire from the database
        // Also, delete related questions and responses
        // Start a transaction
        $pdo->beginTransaction();
        try {
            // Delete responses related to this questionnaire
            $stmt = $pdo->prepare('DELETE FROM responses WHERE questionnaire_id = ?');
            $stmt->execute([$questionnaire_id]);

            // Delete questions related to this questionnaire
            $stmt = $pdo->prepare('DELETE FROM questions WHERE questionnaire_id = ?');
            $stmt->execute([$questionnaire_id]);

            // Delete the questionnaire itself
            $stmt = $pdo->prepare('DELETE FROM questionnaires WHERE questionnaire_id = ?');
            $stmt->execute([$questionnaire_id]);

            // Commit the transaction
            $pdo->commit();

            // Redirect to the list of questionnaires with a success message
            header('Location: ../index.php?msg=deleted');
            exit();

        } catch (Exception $e) {
            // An error occurred, roll back
            $pdo->rollBack();
            echo '<div class="alert alert-danger">حدث خطأ أثناء حذف الاستبيان: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        // Questionnaire not found
        echo '<div class="alert alert-danger">الاستبيان غير موجود.</div>';
    }
} else {
    // Invalid ID
    echo '<div class="alert alert-danger">معرّف الاستبيان غير صالح.</div>';
}
?>

<?php include '../../includes/footer.php'; ?>