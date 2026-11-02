<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// File statis `/storage` harus dilayani di sini. `return false` membuat PHP
// built-in server mengirim file tanpa header CORS, sehingga Flutter Web gagal
// memuat foto (CanvasKit memakai fetch).
if (str_starts_with($uri, '/storage/')) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Max-Age: 86400');

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $file = $publicPath.$uri;
    $storageRoot = realpath($publicPath.DIRECTORY_SEPARATOR.'storage');
    $realFile = realpath($file);

    if ($realFile && $storageRoot && str_starts_with($realFile, $storageRoot) && is_file($realFile)) {
        $ext = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => mime_content_type($realFile) ?: 'application/octet-stream',
        };
        header('Content-Type: '.$mime);
        header('Content-Length: '.filesize($realFile));
        readfile($realFile);
        exit;
    }
}

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
