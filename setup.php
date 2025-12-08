<?php
/**
 * فایل راه‌اندازی خودکار دیتابیس
 * این فایل دیتابیس را ایجاد کرده و جداول را می‌سازد
 */

// تنظیمات دیتابیس
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'homyran_panel';

echo "در حال راه‌اندازی دیتابیس...\n\n";

try {
    // اتصال به MySQL بدون انتخاب دیتابیس
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    if ($conn->connect_error) {
        die("❌ خطا در اتصال به MySQL: " . $conn->connect_error . "\n");
    }
    
    echo "✅ اتصال به MySQL موفق بود\n";
    
    // ایجاد دیتابیس
    $sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql)) {
        echo "✅ دیتابیس '$db_name' ایجاد شد\n";
    } else {
        echo "⚠️ دیتابیس '$db_name' از قبل وجود دارد یا خطایی رخ داد: " . $conn->error . "\n";
    }
    
    // انتخاب دیتابیس
    $conn->select_db($db_name);
    $conn->set_charset("utf8mb4");
    
    echo "✅ دیتابیس انتخاب شد\n\n";
    
    // ایجاد جداول
    echo "در حال ایجاد جداول...\n";
    
    // جدول کاربران
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql_users)) {
        echo "  ✅ جدول 'users' ایجاد شد\n";
    } else {
        echo "  ⚠️ خطا در ایجاد جدول users: " . $conn->error . "\n";
    }
    
    // جدول املاک
    $sql_properties = "CREATE TABLE IF NOT EXISTS properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('buy', 'sell', 'mortgage', 'rent') NOT NULL,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        address VARCHAR(300) NOT NULL,
        city VARCHAR(100) NOT NULL,
        area DECIMAL(10,2) NOT NULL COMMENT 'متراژ',
        price DECIMAL(15,2) NOT NULL COMMENT 'قیمت',
        rooms INT DEFAULT 0 COMMENT 'تعداد اتاق',
        floor INT DEFAULT 0 COMMENT 'طبقه',
        building_age INT DEFAULT 0 COMMENT 'سن بنا',
        property_type VARCHAR(50) DEFAULT 'apartment' COMMENT 'نوع ملک (آپارتمان، ویلا، زمین و...)',
        has_elevator BOOLEAN DEFAULT FALSE COMMENT 'آسانسور',
        has_parking BOOLEAN DEFAULT FALSE COMMENT 'پارکینگ',
        has_warehouse BOOLEAN DEFAULT FALSE COMMENT 'انباری',
        image_path VARCHAR(500),
        status ENUM('active', 'sold', 'rented', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql_properties)) {
        echo "  ✅ جدول 'properties' ایجاد شد\n";
    } else {
        echo "  ⚠️ خطا در ایجاد جدول properties: " . $conn->error . "\n";
    }
    
    // بررسی وجود کاربر admin
    $check_user = $conn->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    $user_exists = $check_user->fetch_assoc()['count'] > 0;
    
    if (!$user_exists) {
        // افزودن کاربر پیش‌فرض (رمز: admin123)
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql_insert = "INSERT INTO users (username, email, password, full_name) VALUES 
            ('admin', 'admin@homyran.com', '$admin_password', 'مدیر سیستم')";
        
        if ($conn->query($sql_insert)) {
            echo "  ✅ کاربر پیش‌فرض 'admin' اضافه شد\n";
        } else {
            echo "  ⚠️ خطا در افزودن کاربر: " . $conn->error . "\n";
        }
    } else {
        echo "  ℹ️ کاربر 'admin' از قبل وجود دارد\n";
    }
    
    echo "\n";
    
    $conn->close();
    
    echo "\n✅ راه‌اندازی با موفقیت انجام شد!\n\n";
    echo "📝 اطلاعات ورود پیش‌فرض:\n";
    echo "   نام کاربری: admin\n";
    echo "   رمز عبور: admin123\n\n";
    echo "🌐 حالا می‌توانید به صفحه login.php بروید\n";
    
} catch (Exception $e) {
    echo "❌ خطا: " . $e->getMessage() . "\n";
    exit(1);
}
?>

