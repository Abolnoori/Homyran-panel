-- ساخت دیتابیس
CREATE DATABASE IF NOT EXISTS homyran_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE homyran_panel;

-- جدول کاربران
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول املاک
CREATE TABLE IF NOT EXISTS properties (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- کاربر پیش‌فرض (رمز: admin123)
INSERT INTO users (username, email, password, full_name) VALUES 
('admin', 'admin@homyran.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر سیستم');

