<?php
header('Content-Type: application/json');

$exportDir = 'exports/';
if (!is_dir($exportDir)) {
    mkdir($exportDir, 0755, true);
}

$filenameInput = $_POST['filename'] ?? '';
$timestamp = date('Ymd_His');
$fullFilename = $exportDir . $filenameInput ?: "books_$timestamp.csv";

$cmd = "php export_books_cli.php > $fullFilename";
shell_exec($cmd);
// Kiểm tra xem file có tồn tại không
if (file_exists($fullFilename)) {
    echo json_encode([
        'success' => true,
        'message' => "Exported successfully to $fullFilename",
        'download_link' => $fullFilename
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "Export failed."
    ]);
}
