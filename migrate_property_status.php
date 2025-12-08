<?php
/**
 * فایل اجرای Migration برای اضافه کردن فیلدهای property_status و vacancy_months
 */

require_once 'config/database.php';

echo "در حال اجرای Migration برای فیلدهای وضعیت ملک...\n\n";

try {
    $conn = getDBConnection();
    
    // بررسی وجود جدول properties
    $result = $conn->query("SHOW TABLES LIKE 'properties'");
    if ($result->num_rows == 0) {
        die("❌ جدول 'properties' یافت نشد! لطفاً ابتدا schema.sql را اجرا کنید.\n");
    }
    
    echo "✅ جدول properties یافت شد\n\n";
    
    // بررسی وجود فیلد property_status
    $result = $conn->query("SHOW COLUMNS FROM properties LIKE 'property_status'");
    if ($result->num_rows == 0) {
        $sql_status = "ALTER TABLE properties ADD COLUMN property_status VARCHAR(20) DEFAULT 'empty' COMMENT 'وضعیت ملک (tenant=مستاجر, empty=خالی)'";
        if ($conn->query($sql_status)) {
            echo "✅ فیلد 'property_status' اضافه شد\n";
        } else {
            echo "⚠️ خطا در اضافه کردن فیلد 'property_status': " . $conn->error . "\n";
        }
    } else {
        echo "ℹ️ فیلد 'property_status' از قبل وجود دارد\n";
    }
    
    // بررسی وجود فیلد vacancy_months
    $result = $conn->query("SHOW COLUMNS FROM properties LIKE 'vacancy_months'");
    if ($result->num_rows == 0) {
        $sql_vacancy = "ALTER TABLE properties ADD COLUMN vacancy_months INT DEFAULT 0 COMMENT 'تعداد ماه تا خالی شدن (فقط برای مستاجر)'";
        if ($conn->query($sql_vacancy)) {
            echo "✅ فیلد 'vacancy_months' اضافه شد\n";
        } else {
            echo "⚠️ خطا در اضافه کردن فیلد 'vacancy_months': " . $conn->error . "\n";
        }
    } else {
        echo "ℹ️ فیلد 'vacancy_months' از قبل وجود دارد\n";
    }
    
    $conn->close();
    
    echo "\n✅ Migration با موفقیت انجام شد!\n\n";
    
} catch (Exception $e) {
    echo "❌ خطا: " . $e->getMessage() . "\n";
    exit(1);
}
?>

