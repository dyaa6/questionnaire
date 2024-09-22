<?php
// /admin/responses/delete.php
define('ALLOW_ACCESS', true);
include '../../includes/header.php';
include '../../includes/db_connect.php';
include '../../includes/functions.php';

// Check if admin is logged in
checkAdminLogin();

// Get response ID from GET parameter
$response_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate the ID
if ($response_id <= 0) {
    echo "<div class='container mt-5'>
            <div class='alert alert-danger text-center' role='alert'>
                معرّف الرد غير صالح.
            </div>
          </div>";
    include '../../includes/footer.php';
    exit();
}

// Fetch the response to verify existence
$stmt = $pdo->prepare('SELECT * FROM responses WHERE response_id = ?');
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

// Delete the response and its associated answers
try {
    // Begin transaction to ensure atomicity
    $pdo->beginTransaction();

    // Delete answers associated with the response
    $stmt_answers = $pdo->prepare('DELETE FROM answers WHERE response_id = ?');
    $stmt_answers->execute([$response_id]);

    // Delete the response itself
    $stmt_delete = $pdo->prepare('DELETE FROM responses WHERE response_id = ?');
    $stmt_delete->execute([$response_id]);

    // Commit the transaction
    $pdo->commit();

    // Redirect to the responses list with a success message
    header('Location: index.php?msg=deleted');
    exit();

} catch (Exception $e) {
    // Rollback the transaction in case of error
    $pdo->rollBack();
    echo "<div class='container mt-5'>
            <div class='alert alert-danger text-center' role='alert'>
                حدث خطأ أثناء حذف الرد: " . htmlspecialchars($e->getMessage()) . "
            </div>
          </div>";
    include '../../includes/footer.php';
    exit();
}
?>