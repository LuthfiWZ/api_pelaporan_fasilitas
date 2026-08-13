@echo off
REM Menjalankan Laravel dev server memakai PHP 8.3 dari Laragon
REM (PHP default di PATH kemungkinan 8.1 yang tidak kompatibel dengan Laravel 13)
set PHP_BIN=C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe

if not exist "%PHP_BIN%" (
    echo PHP 8.3 tidak ditemukan di %PHP_BIN%
    echo Sesuaikan PATH PHP_BIN di file ini dengan versi PHP anda.
    exit /b 1
)

echo Memulai server di http://localhost:8000
"%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8000
