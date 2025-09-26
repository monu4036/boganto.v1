<?php
// Remove any existing output buffering
while (ob_get_level()) ob_end_clean();

// Get the image filename from the URL
$filename = basename($_GET['file'] ?? '');
if (empty($filename)) {
    header("HTTP/1.0 404 Not Found");
    exit("File not specified");
}

// Define the uploads directory path
$uploads_dir = __DIR__ . '/../uploads/';
$file_path = $uploads_dir . $filename;

// Verify the file exists and is within uploads directory
if (!file_exists($file_path) || !is_file($file_path) || strpos(realpath($file_path), realpath($uploads_dir)) !== 0) {
    header("HTTP/1.0 404 Not Found");
    exit("File not found");
}

// Get the file's mime type
$mime_type = mime_content_type($file_path);
if (!preg_match('/^image\//', $mime_type)) {
    header("HTTP/1.0 400 Bad Request");
    exit("Not an image file");
}

// Set proper headers for image serving
header("Content-Type: " . $mime_type);
header("Content-Length: " . filesize($file_path));
header("Cache-Control: public, max-age=31536000");
header("Expires: " . gmdate("D, d M Y H:i:s", time() + 31536000) . " GMT");
header("Pragma: public");

// Output the file
readfile($file_path);
exit();
?>