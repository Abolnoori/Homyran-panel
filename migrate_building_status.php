<?php
/**
 * فایل اجرای Migration برای اضافه کردن فیلد building_status
 */

require_once 'config/database.php';

echo "در حال اجرای Migration برای building_status...\n\n";

try {
    $conn = getDBConnection();
    
    // بررسی وجود جدول properties
    $result = $conn->query("SHOW TABLES LIKE 'properties'");
    if ($result->num_rows == 0) {
        die("❌ جدول 'properties' یافت نشد! لطفاً ابتدا schema.sql را اجرا کنید.\n");
    }
    
    echo "✅ جدول properties یافت شد\n\n";
    
    // بررسی وجود ستون building_status
    $result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'building_status'");
    if ($result->num_rows > 0) {
        echo "ℹ️ ستون 'building_status' از قبل وجود دارد\n";
    } else {
        // اضافه کردن ستون building_status
        $sql = "ALTER TABLE properties ADD COLUMN building_status VARCHAR(50) DEFAULT '' COMMENT 'وضعیت بنا (نوساز، کلید نخورده، معمولی، نیاز به تعمیر، کلنگی)'";
        if ($conn->query($sql)) {
            echo "✅ ستون 'building_status' با موفقیت اضافه شد\n";
        } else {
            echo "❌ خطا در اضافه کردن ستون 'building_status': " . $conn->error . "\n";
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




