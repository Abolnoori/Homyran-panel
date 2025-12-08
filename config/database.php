<?php
// تنظیمات اتصال به دیتابیس
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'homyran_panel');

// Base URL برای مسیرهای نسبی
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // پیدا کردن ریشه پروژه با استفاده از DOCUMENT_ROOT
    if (isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_FILENAME'])) {
        $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        $scriptFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
        
        // پیدا کردن مسیر نسبی پروژه از DOCUMENT_ROOT
        $projectPath = str_replace($docRoot, '', dirname($scriptFile));
        
        // اگر در properties هستیم، یک سطح بالا می‌رویم
        if (strpos($projectPath, '/properties') !== false) {
            $projectPath = dirname($projectPath);
        }
        
        $projectPath = rtrim($projectPath, '/');
        return $protocol . '://' . $host . ($projectPath ? $projectPath : '');
    }
    
    // روش جایگزین: استفاده از SCRIPT_NAME
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $scriptDir = dirname($script);
    
    // اگر در properties هستیم، یک سطح بالا می‌رویم
    if (strpos($script, '/properties/') !== false) {
        $scriptDir = dirname($scriptDir);
    }
    
    $scriptDir = rtrim($scriptDir, '/');
    return $protocol . '://' . $host . ($scriptDir === '/' ? '' : $scriptDir);
}

define('BASE_URL', getBaseUrl());

// اتصال به دیتابیس
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("خطا در اتصال به دیتابیس: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>

