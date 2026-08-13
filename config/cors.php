<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | `storage/*` ditambahkan agar foto laporan yang disimpan di disk publik
    | bisa ditampilkan di aplikasi Flutter web (CanvasKit memuat gambar via
    | fetch, sehingga butuh header Access-Control-Allow-Origin).
    |
    */

    'paths' => ['api/*', 'storage/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
