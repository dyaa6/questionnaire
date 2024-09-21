<?php
// /questionnaire/submit.php
define('ALLOW_ACCESS', true);
include '../includes/db_connect.php';
include '../includes/functions.php'; // Add this line to include functions.php

// Check if POST data is received
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $questionnaire_id = (int)$_POST['questionnaire_id'];
    $answers = $_POST['answers'];

    // Insert into responses table
    $stmt = $pdo->prepare('INSERT INTO responses (questionnaire_id, user_ip, submitted_at) VALUES (?, ?, NOW())');
    $stmt->execute([$questionnaire_id, $_SERVER['REMOTE_ADDR']]);

    // Get the response ID
    $response_id = $pdo->lastInsertId();

    // Insert answers
    $stmt = $pdo->prepare('INSERT INTO answers (response_id, question_id, answer_text) VALUES (?, ?, ?)');
    foreach ($answers as $question_id => $answer_text) {
        $question_id = (int)$question_id;
        $answer_text = sanitizeInput($answer_text);
        $stmt->execute([$response_id, $question_id, $answer_text]);
    }

    // Redirect or show confirmation
    header('Location: thank_you.php');
    exit();
} else {
    // Invalid access
    header('Location: index.php');
    exit();
}
?>