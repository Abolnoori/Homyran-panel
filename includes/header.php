<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/icons.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>پنل مدیریت املاک هومیران</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/react@2.0.18/24/outline/index.js" type="module"></script>
    <!-- فونت ایران سنس -->
    <style>
        @font-face {
            font-family: 'IRANSans';
            src: url('<?php echo BASE_URL; ?>/fonts/IRANSans-Regular.woff') format('woff');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'IRANSans';
            src: url('<?php echo BASE_URL; ?>/fonts/IRANSans-Regular.woff') format('woff');
            font-weight: bold;
            font-style: normal;
            font-display: swap;
        }
        [dir="rtl"] {
            direction: rtl;
        }
        * {
            font-family: 'IRANSans', 'Tahoma', 'Arial', sans-serif !important;
        }
        body {
            font-family: 'IRANSans', 'Tahoma', 'Arial', sans-serif !important;
        }
        .hover-scale:hover {
            transform: scale(1.05);
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            min-width: 200px;
            z-index: 50;
        }
        .dropdown.active .dropdown-menu {
            display: block;
        }
        .dropdown-toggle {
            background: none;
            border: none;
            outline: none;
        }
        .dropdown-item {
            display: block;
            padding: 0.75rem 1rem;
            color: #374151;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .dropdown-item:hover {
            background-color: #f3f4f6;
        }
        .dropdown-item.danger {
            color: #dc2626;
        }
        .dropdown-item.danger:hover {
            background-color: #fee2e2;
        }
        .sidebar {
            position: fixed;
            top: 0;
            right: -240px;
            width: 240px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar.active {
            right: 0;
        }
        .main-content {
            transition: margin-right 0.3s ease;
        }
        body.sidebar-open .main-content {
            margin-right: 240px;
        }
        body.sidebar-open nav {
            margin-right: 240px;
            transition: margin-right 0.3s ease;
        }
        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #374151;
            text-decoration: none;
            transition: background-color 0.2s;
            border-right: 3px solid transparent;
        }
        .menu-item:hover {
            background-color: #f3f4f6;
            border-right-color: #3b82f6;
        }
        .menu-item.active {
            background-color: #eff6ff;
            border-right-color: #3b82f6;
            color: #3b82f6;
        }
        .menu-category {
            padding: 1rem 1rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e5e7eb;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        /* (responsive styles removed — user will provide their own mobile CSS) */
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Vazir:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="loader-spinner"></div>
    </div>
    
    <?php if (isLoggedIn()): ?>
    <!-- سایدبار -->
    <div class="sidebar" id="sidebar">
        <div class="p-3 border-b">
            <div class="flex items-center justify-between">
                <img src="<?php echo BASE_URL; ?>/assets/logo.svg" alt="هومیران" class="h-10 w-auto" onerror="this.style.display='none'">
                <button onclick="toggleSidebar()" class="text-gray-500 hover:text-gray-700">
                    <?php echo heroicon('x-mark', 'w-5 h-5'); ?>
                </button>
            </div>
        </div>
        
        <div class="py-4">
            <!-- دسته اصلی -->
            <div class="menu-category">اصلی</div>
            <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            $isIndex = ($currentPage == 'index.php');
            $isAdd = ($currentPage == 'add.php');
            $isList = ($currentPage == 'list.php' || $currentPage == 'view.php' || $currentPage == 'edit.php');
            ?>
            <a href="<?php echo BASE_URL; ?>/index.php" class="menu-item <?php echo $isIndex ? 'active' : ''; ?>">
                <span class="ml-3 text-gray-400"><?php echo heroicon('home', 'w-5 h-5'); ?></span>
                <span>داشبورد</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/properties/add.php" class="menu-item <?php echo $isAdd ? 'active' : ''; ?>">
                <span class="ml-3 text-gray-400"><?php echo heroicon('plus', 'w-5 h-5'); ?></span>
                <span>افزودن ملک</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/properties/list.php" class="menu-item <?php echo $isList ? 'active' : ''; ?>">
                <span class="ml-3 text-gray-400"><?php echo heroicon('list', 'w-5 h-5'); ?></span>
                <span>لیست املاک</span>
            </a>
            
            <!-- دسته مدیریت -->
            <div class="menu-category mt-4">مدیریت</div>
            <div class="dropdown relative">
                <button type="button" onclick="toggleUserDropdown(event)" class="menu-item w-full text-right">
                    <span class="ml-3 text-gray-400"><?php echo heroicon('user', 'w-5 h-5'); ?></span>
                    <span class="flex-1"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                    <span class="text-xs"><?php echo heroicon('chevron-down', 'w-4 h-4'); ?></span>
                </button>
                <div class="dropdown-menu" style="position: relative; display: none; margin-top: 0; box-shadow: none; background: #f9fafb;">
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="dropdown-item danger">
                        <span class="ml-2"><?php echo heroicon('arrow-left-on-rectangle', 'w-4 h-4'); ?></span>خروج از سیستم
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- هدر اصلی -->
    <nav class="bg-gradient-to-r from-blue-600 to-blue-700 shadow-xl fixed top-0 left-0 right-0 z-50">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- سمت راست: آیکون منو و لوگو -->
                <div class="flex items-center gap-4 ">
                    <button onclick="toggleSidebar()" class="text-white hover:text-blue-200 p-2 rounded-lg hover:bg-blue-800 transition-all duration-200">
                        <?php echo heroicon('squares-2x2', 'w-6 h-6'); ?>
                    </button>

                </div>
                <div>
                    <img src="<?php echo BASE_URL; ?>/assets/logo.svg" alt="هومیران" class="h-10 w-auto md:block" onerror="this.style.display='none'">
                    </div>
                <!-- سمت چپ: اطلاعات کاربر -->
                <div class="dropdown relative" id="userDropdown">
                    <button type="button" class="dropdown-toggle flex items-center gap-3 text-white hover:text-blue-200 transition-all duration-200 cursor-pointer px-3 py-2 rounded-lg hover:bg-blue-800" onclick="toggleDropdown(event)">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white">
                            <?php echo heroicon('user', 'w-5 h-5'); ?>
                        </div>
                        <div class="text-right hidden md:block">
                            <p class="text-white text-sm font-medium"><?php echo htmlspecialchars($currentUser['full_name']); ?></p>
                            <p class="text-blue-200 text-xs">مدیر سیستم</p>
                        </div>
                        <span class="text-sm mr-2 hidden md:block transition-transform duration-200" id="chevronIcon"><?php echo heroicon('chevron-down', 'w-4 h-4'); ?></span>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <div class="px-4 py-3 border-b border-gray-200 md:hidden">
                            <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($currentUser['full_name']); ?></p>
                            <p class="text-xs text-gray-500">مدیر سیستم</p>
                        </div>
                        <a
                        style="display: flex; align-items: center;" 
                        href="<?php echo BASE_URL; ?>/logout.php" class="dropdown-item danger">
                            <span class="ml-2"><?php echo heroicon('arrow-left-on-rectangle', 'w-4 h-4'); ?></span>خروج
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- فاصله برای هدر ثابت -->
    <div class="h-16"></div>
    
    <!-- Wrapper برای محتوای اصلی -->
    <div class="main-content">
    
    <script>
        // بازگردانی وضعیت سایدبار از localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarState = localStorage.getItem('sidebarOpen');
            // On mobile always keep sidebar closed on page load to avoid covering content
            if (window.innerWidth > 640 && sidebarState === 'true') {
                sidebar.classList.add('active');
                document.body.classList.add('sidebar-open');
            } else {
                // ensure closed on mobile or when no state
                sidebar.classList.remove('active');
                document.body.classList.remove('sidebar-open');
                localStorage.setItem('sidebarOpen', 'false');
            }
        });
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            const isActive = sidebar.classList.contains('active');
            // On small screens show sidebar as overlay without shifting content
            if (window.innerWidth <= 640) {
                if (isActive) {
                    sidebar.classList.remove('active');
                    body.classList.remove('sidebar-open');
                    localStorage.setItem('sidebarOpen', 'false');
                } else {
                    sidebar.classList.add('active');
                    // don't add body.sidebar-open margin on mobile
                    localStorage.setItem('sidebarOpen', 'true');
                }
                return;
            }

            if (isActive) {
                sidebar.classList.remove('active');
                body.classList.remove('sidebar-open');
                localStorage.setItem('sidebarOpen', 'false');
            } else {
                sidebar.classList.add('active');
                body.classList.add('sidebar-open');
                localStorage.setItem('sidebarOpen', 'true');
            }
        }
        
        function toggleDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('userDropdown');
            const chevron = document.getElementById('chevronIcon');
            
            const isActive = dropdown.classList.contains('active');
            
            document.querySelectorAll('.dropdown').forEach(function(d) {
                if (d !== dropdown) {
                    d.classList.remove('active');
                }
            });
            
            if (isActive) {
                dropdown.classList.remove('active');
                chevron.style.transform = 'rotate(0deg)';
            } else {
                dropdown.classList.add('active');
                chevron.style.transform = 'rotate(180deg)';
            }
        }
        
        function toggleUserDropdown(event) {
            event.stopPropagation();
            const menu = event.target.closest('.dropdown').querySelector('.dropdown-menu');
            if (menu.style.display === 'none' || !menu.style.display) {
                menu.style.display = 'block';
            } else {
                menu.style.display = 'none';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // مخفی کردن loader بعد از لود شدن صفحه
            window.addEventListener('load', function() {
                const loader = document.getElementById('pageLoader');
                if (loader) {
                    setTimeout(function() {
                        loader.classList.add('hidden');
                        setTimeout(function() {
                            loader.style.display = 'none';
                        }, 300);
                    }, 300);
                }
            });
            
            document.addEventListener('click', function(event) {
                const dropdown = document.getElementById('userDropdown');
                if (!dropdown.contains(event.target)) {
                    dropdown.classList.remove('active');
                    const chevron = document.getElementById('chevronIcon');
                    if (chevron) {
                        chevron.style.transform = 'rotate(0deg)';
                    }
                }
            });

            // close sidebar when a link is clicked on mobile so it doesn't stay open
            document.querySelectorAll('#sidebar a').forEach(function(a) {
                a.addEventListener('click', function() {
                    if (window.innerWidth <= 640) {
                        const sb = document.getElementById('sidebar');
                        sb.classList.remove('active');
                        document.body.classList.remove('sidebar-open');
                        localStorage.setItem('sidebarOpen', 'false');
                    }
                });
            });
        });
    </script>
    <?php endif; ?>

