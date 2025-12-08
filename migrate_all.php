<?php
/**
 * فایل اجرای Migration جامع برای اضافه کردن تمام فیلدهای مورد نیاز
 */

require_once 'config/database.php';

echo "در حال اجرای Migration جامع...\n\n";

try {
    $conn = getDBConnection();
    
    // بررسی وجود جدول properties
    $result = $conn->query("SHOW TABLES LIKE 'properties'");
    if ($result->num_rows == 0) {
        die("❌ جدول 'properties' یافت نشد! لطفاً ابتدا schema.sql را اجرا کنید.\n");
    }
    
    echo "✅ جدول properties یافت شد\n\n";
    
    // لیست تمام ستون‌های مورد نیاز
    $columns_to_add = [
        // فیلدهای اضافی
        'bedrooms' => "INT DEFAULT 0 COMMENT 'تعداد خواب'",
        'max_tenants' => "INT DEFAULT 0 COMMENT 'حداکثر تعداد مستاجر'",
        'mortgage_price' => "DECIMAL(15,2) DEFAULT 0 COMMENT 'قیمت رهن'",
        'rent_price' => "DECIMAL(15,2) DEFAULT 0 COMMENT 'قیمت اجاره'",
        'phone' => "VARCHAR(20) DEFAULT '' COMMENT 'تلفن'",
        'mobile' => "VARCHAR(20) DEFAULT '' COMMENT 'تلفن همراه'",
        'owner_phone' => "VARCHAR(20) DEFAULT '' COMMENT 'تلفن مالک'",
        'tenant_phone' => "VARCHAR(20) DEFAULT '' COMMENT 'تلفن مستاجر'",
        'owner_name' => "VARCHAR(100) DEFAULT '' COMMENT 'نام مالک'",
        
        // امکانات
        'has_water' => "BOOLEAN DEFAULT FALSE COMMENT 'آب'",
        'has_electricity' => "BOOLEAN DEFAULT FALSE COMMENT 'برق'",
        'has_gas' => "BOOLEAN DEFAULT FALSE COMMENT 'گاز'",
        'has_phone' => "BOOLEAN DEFAULT FALSE COMMENT 'تلفن'",
        'has_cabinet' => "BOOLEAN DEFAULT FALSE COMMENT 'کابینت'",
        'has_water_heater' => "BOOLEAN DEFAULT FALSE COMMENT 'آبگرمکن'",
        'has_cooler' => "BOOLEAN DEFAULT FALSE COMMENT 'کولر'",
        'has_carpet' => "BOOLEAN DEFAULT FALSE COMMENT 'موکت'",
        'has_ceramic' => "BOOLEAN DEFAULT FALSE COMMENT 'سرامیک'",
        'has_paint' => "BOOLEAN DEFAULT FALSE COMMENT 'نقاشی'",
        'has_radiator' => "BOOLEAN DEFAULT FALSE COMMENT 'شوفاژ'",
        'has_video_intercom' => "BOOLEAN DEFAULT FALSE COMMENT 'آیفون تصویری'",
        'has_antenna' => "BOOLEAN DEFAULT FALSE COMMENT 'آنتن مرکزی'",
        'has_remote_door' => "BOOLEAN DEFAULT FALSE COMMENT 'درب ریموت دار'",
        'has_package' => "BOOLEAN DEFAULT FALSE COMMENT 'پکیج'",
        'has_hidden_light' => "BOOLEAN DEFAULT FALSE COMMENT 'نور مخفی'",
    ];
    
    // دریافت لیست ستون‌های موجود
    $existing_columns = [];
    $result = $conn->query("SHOW COLUMNS FROM properties");
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    echo "در حال بررسی و اضافه کردن ستون‌ها...\n\n";
    
    $added_count = 0;
    $skipped_count = 0;
    
    foreach ($columns_to_add as $column_name => $column_definition) {
        if (in_array($column_name, $existing_columns)) {
            echo "  ℹ️ ستون '$column_name' از قبل وجود دارد\n";
            $skipped_count++;
        } else {
            $sql = "ALTER TABLE properties ADD COLUMN `$column_name` $column_definition";
            if ($conn->query($sql)) {
                echo "  ✅ ستون '$column_name' اضافه شد\n";
                $added_count++;
            } else {
                echo "  ❌ خطا در اضافه کردن ستون '$column_name': " . $conn->error . "\n";
            }
        }
    }
    
    // تغییر نوع ستون type از ENUM به VARCHAR (اگر هنوز ENUM است)
    $result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'type'");
    if ($row = $result->fetch_assoc()) {
        if (strpos($row['Type'], 'enum') !== false || strpos($row['Type'], 'ENUM') !== false) {
            $sql = "ALTER TABLE properties MODIFY COLUMN type VARCHAR(50) NOT NULL COMMENT 'نوع معامله (ممکن است چند مورد باشد با جداکننده کاما)'";
            if ($conn->query($sql)) {
                echo "  ✅ ستون 'type' از ENUM به VARCHAR تغییر یافت\n";
            } else {
                echo "  ⚠️ خطا در تغییر ستون 'type': " . $conn->error . "\n";
            }
        }
    }
    
    echo "\n📊 خلاصه:\n";
    echo "  ✅ ستون‌های اضافه شده: $added_count\n";
    echo "  ℹ️ ستون‌های موجود (رد شد): $skipped_count\n";
    
    $conn->close();
    
    echo "\n✅ Migration با موفقیت انجام شد!\n\n";
    
} catch (Exception $e) {
    echo "❌ خطا: " . $e->getMessage() . "\n";
    exit(1);
}
?>


