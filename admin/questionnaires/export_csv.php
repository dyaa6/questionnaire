<?php
// export_data.php

define('ALLOW_ACCESS', true);
include '../../includes/db_connect.php';
include '../../includes/functions.php';

if (isset($_GET['id'])) {
    $questionnaire_id = (int)$_GET['id'];
    
    // Fetch questionnaire
    $stmt = $pdo->prepare('SELECT * FROM questionnaires WHERE questionnaire_id = ?');
    $stmt->execute([$questionnaire_id]);
    $questionnaire = $stmt->fetch();

    if (!$questionnaire) {
        die('الاستبيان غير موجود.');
    }

    // Fetch questions
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE questionnaire_id = ? ORDER BY question_id');
    $stmt->execute([$questionnaire_id]);
    $questions = $stmt->fetchAll();

    $format = $_GET['format'] ?? 'csv'; // Default to CSV if no format specified

    if ($format === 'excel') {
        // Excel export
        header('Content-Type: application/vnd.ms-excel; charset=UTF-16LE');
        header('Content-Disposition: attachment; filename="questionnaire_' . $questionnaire_id . '.xls"');
        
        echo '<html>';
        echo '<head><meta charset="UTF-16LE"></head>';
        echo '<body>';
        echo '<table border="1">';
        echo '<tr><th>السؤال</th><th>الإجابة</th></tr>';

        foreach ($questions as $question) {
            // Fetch answers for each question
            $stmt = $pdo->prepare('SELECT * FROM answers WHERE question_id = ?');
            $stmt->execute([$question['question_id']]);
            $answers = $stmt->fetchAll();

            // Add the question row
            echo '<tr>';
            echo '<td>' . htmlspecialchars($question['question_text']) . '</td>';
            echo '<td></td>';
            echo '</tr>';

            // Add answer rows
            if (!empty($answers)) {
                foreach ($answers as $answer) {
                    echo '<tr>';
                    echo '<td></td>';
                    echo '<td>' . htmlspecialchars($answer['answer_text']) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr>';
                echo '<td></td>';
                echo '<td>لا توجد إجابات</td>';
                echo '</tr>';
            }

            // Add empty row for separation
            echo '<tr><td></td><td></td></tr>';
        }

        echo '</table>';
        echo '</body>';
        echo '</html>';

    } else {
        // CSV export
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="questionnaire_' . $questionnaire_id . '.csv"');
        
        // Add BOM for UTF-8
        echo "\xEF\xBB\xBF";
        
        // Create a file pointer
        $file = fopen('php://output', 'w');
        
        // Set the column headers
        fputcsv($file, ['السؤال', 'الإجابة']);

        foreach ($questions as $question) {
            // Fetch answers for each question
            $stmt = $pdo->prepare('SELECT * FROM answers WHERE question_id = ?');
            $stmt->execute([$question['question_id']]);
            $answers = $stmt->fetchAll();

            // Add the question
            fputcsv($file, [$question['question_text'], '']);

            // Add answers
            if (!empty($answers)) {
                foreach ($answers as $answer) {
                    fputcsv($file, ['', $answer['answer_text']]);
                }
            } else {
                fputcsv($file, ['', 'لا توجد إجابات']);
            }

            // Add empty row for separation
            fputcsv($file, ['', '']);
        }

        fclose($file);
    }

    exit();
} else {
    header('Location: index.php');
    exit();
}
?>