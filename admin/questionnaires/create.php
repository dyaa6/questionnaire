<?php
// /admin/questionnaires/create.php
define('ALLOW_ACCESS', true); 
include '../../includes/header.php';
include '../../includes/db_connect.php';
include '../../includes/functions.php';

// Check if admin is logged in
checkAdminLogin();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);

    // Insert into questionnaires table
    $stmt = $pdo->prepare('INSERT INTO questionnaires (title, description, created_at) VALUES (?, ?, NOW())');
    $stmt->execute([$title, $description]);

    $questionnaire_id = $pdo->lastInsertId();

    // Redirect to edit page to add questions
    header('Location: edit.php?id=' . $questionnaire_id);
    exit();
}
?>

<h2>Create New Questionnaire</h2>

<form action="create.php" method="post">
    <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" required>
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" required></textarea>
    </div>
    <input type="submit" value="Create Questionnaire">
</form>

<?php
include '../../includes/footer.php';
?>