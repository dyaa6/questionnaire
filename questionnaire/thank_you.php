<?php
// /questionnaire/thank_you.php

define('ALLOW_ACCESS', true);
include '../includes/header.php';
include '../includes/functions.php';
?>

<div class="card text-center">
    <div class="card-body">
        <h2 class="card-title">Thank You!</h2>
        <p class="card-text">Your responses have been submitted successfully.</p>
        <a href="index.php" class="btn btn-success">Back to Questionnaires</a>
    </div>
</div>

<?php
include '../includes/footer.php';
?>