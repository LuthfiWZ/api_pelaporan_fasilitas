<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "Testing S3 connection...\n";
echo "Bucket : " . env('AWS_BUCKET') . "\n";
echo "Region : " . env('AWS_DEFAULT_REGION') . "\n";
echo "Key    : " . substr(env('AWS_ACCESS_KEY_ID'), 0, 8) . "...\n\n";

try {
    $result = Storage::disk('s3')->put('test-connection.txt', 'AWS S3 connected from Laravel! ' . date('Y-m-d H:i:s'));
    if ($result) {
        $url = Storage::disk('s3')->url('test-connection.txt');
        echo "SUCCESS! File uploaded.\n";
        echo "URL: " . $url . "\n";
    } else {
        echo "FAILED: put() returned false\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
