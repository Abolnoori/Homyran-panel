# پنل مدیریت املاک هومیران

پنل مدیریت املاک برای مدیریت خرید، فروش، رهن و اجاره املاک

## ویژگی‌ها

- سیستم احراز هویت (ورود/خروج)
- افزودن ملک با 4 نوع معامله (خرید، فروش، رهن، اجاره)
- لیست و مدیریت املاک
- ویرایش و حذف املاک
- فیلتر بر اساس نوع و وضعیت
- داشبورد با آمار کلی

## نصب و راه‌اندازی

### پیش‌نیازها

- PHP 7.4 یا بالاتر
- MySQL 5.7 یا بالاتر
- Apache/Nginx با mod_rewrite

### مراحل نصب

1. کلون یا دانلود پروژه
2. ایجاد دیتابیس MySQL:
   ```sql
   CREATE DATABASE homyran_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. اجرای فایل SQL:
   ```bash
   mysql -u root -p homyran_panel < database/schema.sql
   ```

4. تنظیمات دیتابیس در `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'homyran_panel');
   ```

5. قرار دادن لوگو در پوشه `assets/logo.png` (اختیاری)

6. راه‌اندازی سرور:
   ```bash
   php -S localhost:8000
   ```

## اطلاعات ورود پیش‌فرض

- **نام کاربری:** admin
- **رمز عبور:** admin123

## ساختار پروژه

```
Homyran-panel/
├── assets/          # فایل‌های استاتیک (لوگو، تصاویر)
├── config/          # تنظیمات
│   └── database.php
├── database/        # فایل‌های دیتابیس
│   └── schema.sql
├── includes/        # فایل‌های مشترک
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── properties/      # صفحات مدیریت املاک
│   ├── add.php
│   ├── list.php
│   ├── view.php
│   ├── edit.php
│   └── delete.php
├── index.php        # داشبورد
├── login.php        # صفحه ورود
├── logout.php       # خروج
└── README.md
```

## تکنولوژی‌ها

- **Backend:** PHP (Vanilla)
- **Frontend:** Tailwind CSS
- **Database:** MySQL
- **Icons:** Font Awesome

## توسعه آینده

- آپلود تصویر برای املاک
- جستجوی پیشرفته
- گزارش‌گیری و آمار
- مدیریت کاربران
- سیستم پیام‌رسانی
- API برای اپلیکیشن موبایل

## مجوز

این پروژه برای استفاده در بنگاه املاک هومیران توسعه یافته است.

