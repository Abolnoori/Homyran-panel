<?php
/**
 * فایل اجرای Migration برای اضافه کردن فیلد convert_price
 */

require_once 'config/database.php';

echo "در حال اجرای Migration برای convert_price...\n\n";

try {
    $conn = getDBConnection();
    
    // بررسی وجود جدول properties
    $result = $conn->query("SHOW TABLES LIKE 'properties'");
    if ($result->num_rows == 0) {
        die("❌ جدول 'properties' یافت نشد! لطفاً ابتدا schema.sql را اجرا کنید.\n");
    }
    
    echo "✅ جدول properties یافت شد\n\n";
    
    // بررسی وجود ستون convert_price
    $result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'convert_price'");
    if ($result->num_rows > 0) {
        echo "ℹ️ ستون 'convert_price' از قبل وجود دارد\n";
    } else {
        // اضافه کردن ستون convert_price
        $sql = "ALTER TABLE properties ADD COLUMN convert_price DECIMAL(15,2) DEFAULT 0 COMMENT 'قیمت تبدیل'";
        if ($conn->query($sql)) {
            echo "✅ ستون 'convert_price' با موفقیت اضافه شد\n";
        } else {
            echo "❌ خطا در اضافه کردن ستون 'convert_price': " . $conn->error . "\n";
            exit(1);
        }
    }
    
    $conn->close();
    
    echo "\n✅ Migration با موفقیت انجام شد!\n\n";
    
} catch (Exception $e) {
    echo "❌ خطا: " . $e->getMessage() . "\n";
    exit(1);
}
?>




