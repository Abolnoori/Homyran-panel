<?php
/**
 * فایل اجرای Migration برای اضافه کردن فیلدها و امکانات جدید
 */

require_once 'config/database.php';

echo "در حال اجرای Migration...\n\n";

try {
    $conn = getDBConnection();
    
    // لیست فایل‌های migration برای اجرا
    $migration_files = [
        __DIR__ . '/database/migration_add_fields.sql',
        __DIR__ . '/database/migration_add_features.sql'
    ];
    
    $total_success = 0;
    $total_errors = 0;
    
    foreach ($migration_files as $sql_file) {
        if (!file_exists($sql_file)) {
            echo "⚠️ فایل " . basename($sql_file) . " یافت نشد، رد می‌شود...\n";
            continue;
        }
        
        echo "📄 در حال اجرای " . basename($sql_file) . "...\n";
        
        $sql_content = file_get_contents($sql_file);
        
        // تقسیم دستورات SQL
        $statements = array_filter(
            array_map('trim', explode(';', $sql_content)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt) && !preg_match('/^\/\*/', $stmt);
            }
        );
        
        foreach ($statements as $statement) {
            if (empty(trim($statement))) {
                continue;
            }
            
            // اجرای هر دستور SQL
            if ($conn->query($statement . ';')) {
                $total_success++;
                // نمایش نام فیلد در صورت ALTER TABLE
                if (preg_match('/ADD COLUMN.*?(\w+)/i', $statement, $matches)) {
                    echo "  ✅ فیلد '{$matches[1]}' اضافه شد\n";
                } elseif (preg_match('/MODIFY COLUMN.*?(\w+)/i', $statement, $matches)) {
                    echo "  ✅ فیلد '{$matches[1]}' تغییر یافت\n";
                }
            } else {
                // اگر خطا مربوط به وجود داشتن فیلد باشد، نادیده بگیر
                if (strpos($conn->error, 'Duplicate column') !== false || 
                    strpos($conn->error, 'already exists') !== false ||
                    strpos($conn->error, 'Duplicate key') !== false) {
                    echo "  ℹ️ فیلد از قبل وجود دارد\n";
                } else {
                    $total_errors++;
                    echo "  ⚠️ خطا: " . $conn->error . "\n";
                }
            }
        }
        
        echo "\n";
    }
    
    echo "📊 خلاصه:\n";
    echo "  ✅ دستورات موفق: $total_success\n";
    if ($total_errors > 0) {
        echo "  ⚠️ دستورات با خطا: $total_errors\n";
    }
    
    $conn->close();
    
    echo "\n✅ Migration با موفقیت انجام شد!\n\n";
    
} catch (Exception $e) {
    echo "❌ خطا: " . $e->getMessage() . "\n";
    exit(1);
}
?>

