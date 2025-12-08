<?php
/**
 * فایل اجرای Migration برای اضافه کردن فیلدهای contacts و direction
 */

require_once 'config/database.php';

echo "در حال اجرای Migration برای فیلدهای تماس...\n\n";

try {
    $conn = getDBConnection();
    
    // بررسی وجود جدول properties
    $result = $conn->query("SHOW TABLES LIKE 'properties'");
    if ($result->num_rows == 0) {
        die("❌ جدول 'properties' یافت نشد! لطفاً ابتدا schema.sql را اجرا کنید.\n");
    }
    
    echo "✅ جدول properties یافت شد\n\n";
    
    // بررسی وجود فیلد contacts
    $result = $conn->query("SHOW COLUMNS FROM properties LIKE 'contacts'");
    if ($result->num_rows == 0) {
        $sql_contacts = "ALTER TABLE properties ADD COLUMN contacts TEXT COMMENT 'اطلاعات تماس مالک/مستاجر به صورت JSON'";
        if ($conn->query($sql_contacts)) {
            echo "✅ فیلد 'contacts' اضافه شد\n";
        } else {
            echo "⚠️ خطا در اضافه کردن فیلد 'contacts': " . $conn->error . "\n";
        }
    } else {
        echo "ℹ️ فیلد 'contacts' از قبل وجود دارد\n";
    }
    
    // بررسی وجود فیلد direction
    $result = $conn->query("SHOW COLUMNS FROM properties LIKE 'direction'");
    if ($result->num_rows == 0) {
        $sql_direction = "ALTER TABLE properties ADD COLUMN direction VARCHAR(20) DEFAULT '' COMMENT 'جهت بنا (شمالی/جنوبی)'";
        if ($conn->query($sql_direction)) {
            echo "✅ فیلد 'direction' اضافه شد\n";
        } else {
            echo "⚠️ خطا در اضافه کردن فیلد 'direction': " . $conn->error . "\n";
        }
    } else {
        echo "ℹ️ فیلد 'direction' از قبل وجود دارد\n";
    }
    
    // تغییر city به اختیاری (اگر required است)
    $sql_city = "ALTER TABLE properties MODIFY COLUMN city VARCHAR(100) DEFAULT '' COMMENT 'شهر (اختیاری)'";
    if ($conn->query($sql_city)) {
        echo "✅ فیلد 'city' به اختیاری تغییر یافت\n";
    } else {
        echo "ℹ️ فیلد 'city' قبلاً تنظیم شده یا خطایی رخ داد: " . $conn->error . "\n";
    }
    
    $conn->close();
    
    echo "\n✅ Migration با موفقیت انجام شد!\n\n";
    
} catch (Exception $e) {
    echo "❌ خطا: " . $e->getMessage() . "\n";
    exit(1);
}
?>

