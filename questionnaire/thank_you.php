<?php
// Enable error reporting during testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ALLOW_ACCESS', true);
include __DIR__ . '/../../includes/db_connect.php';
include __DIR__ . '/../../includes/functions.php';

// Include Composer's autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

use Mpdf\Mpdf;

// Check if class exists
if (!class_exists('Mpdf\Mpdf')) {
    die('Class Mpdf\Mpdf not found. Please ensure mPDF is installed via Composer and autoloaded correctly.');
}

// Get questionnaire ID from URL
if (isset($_GET['id'])) {
    $questionnaire_id = (int)$_GET['id'];

    // Fetch questionnaire data
    // ...

    // Build HTML content
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($questionnaire['title'], ENT_QUOTES, 'UTF-8'); ?> - الإحصائيات</title>
        <style>
            @font-face {
                font-family: 'Amiri';
                font-style: normal;
                font-weight: normal;
                src: url('<?php echo __DIR__; ?>/fonts/Amiri-Regular.ttf') format('truetype');
            }
            body {
                font-family: 'Amiri', sans-serif;
                direction: rtl;
                text-align: right;
            }
            h2, h3, h5 {
                text-align: center;
            }
            /* Add additional styles */
        </style>
    </head>
    <body>
        <!-- Your HTML content -->
    </body>
    </html>
    <?php
    $html = ob_get_clean();

    // Create an instance of mPDF
    $mpdf = new Mpdf([
        'default_font' => 'Amiri',
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'default_font_size' => 12,
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
    ]);

    // Set the directionality
    $mpdf->SetDirectionality('rtl');

    // Write the HTML content
    $mpdf->WriteHTML($html);

    // Output the PDF
    $mpdf->Output($questionnaire['title'] . '_الإحصائيات.pdf', 'I');

} else {
    // Redirect to index if no ID is provided
    header('Location: index.php');
    exit();
}
?>