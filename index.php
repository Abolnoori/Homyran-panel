<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/icons.php';
requireLogin();

$pageTitle = 'داشبورد';
$currentUser = getCurrentUser();
$conn = getDBConnection();

// آمار کلی
$stats = [
    'total' => 0,
    'buy' => 0,
    'sell' => 0,
    'mortgage' => 0,
    'rent' => 0
];

// دریافت تمام املاک برای شمارش دقیق نوع معامله
$result = $conn->query("SELECT type FROM properties WHERE user_id = {$_SESSION['user_id']}");
while ($row = $result->fetch_assoc()) {
    // اگر type شامل کاما باشد، چند نوع معامله است
    $types = array_map('trim', explode(',', $row['type']));
    foreach ($types as $type) {
        if (isset($stats[$type])) {
            $stats[$type]++;
        }
    }
    $stats['total']++;
}

// آخرین املاک اضافه شده
$recentProperties = $conn->query("SELECT * FROM properties WHERE user_id = {$_SESSION['user_id']} ORDER BY created_at DESC LIMIT 5");

include 'includes/header.php';
?>

<div class="w-full mx-auto px-1 md:px-4 sm:px-6 lg:px-8 py-8">
    <!-- هدر داشبورد -->
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3 md:mb-2">داشبورد مدیریت</h1>
        <p class="text-gray-600">خوش آمدید، <?php echo htmlspecialchars($currentUser['full_name']); ?></p>
    </div>
    
    <!-- کارت‌های آمار -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <!-- کارت کل املاک -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-200 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm mb-1">کل املاک</p>
                    <p class="text-3xl font-bold"><?php echo $stats['total']; ?></p>
                </div>
                <div class="bg-white bg-opacity-20 p-4 rounded-full">
                    <?php echo heroicon('home', 'w-8 h-8'); ?>
                </div>
            </div>
        </div>
        
        <!-- کارت خرید -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-200 fade-in" style="animation-delay: 0.1s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm mb-1">خرید</p>
                    <p class="text-3xl font-bold"><?php echo $stats['buy']; ?></p>
                </div>
                <div class="bg-white bg-opacity-20 p-4 rounded-full">
                    <?php echo heroicon('shopping-cart', 'w-8 h-8'); ?>
                </div>
            </div>
        </div>
        
        <!-- کارت فروش -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-200 fade-in" style="animation-delay: 0.2s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm mb-1">فروش</p>
                    <p class="text-3xl font-bold"><?php echo $stats['sell']; ?></p>
                </div>
                <div class="bg-white bg-opacity-20 p-4 rounded-full">
                    <?php echo heroicon('tag', 'w-8 h-8'); ?>
                </div>
            </div>
        </div>
        
        <!-- کارت رهن -->
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-200 fade-in" style="animation-delay: 0.3s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm mb-1">رهن</p>
                    <p class="text-3xl font-bold"><?php echo $stats['mortgage']; ?></p>
                </div>
                <div class="bg-white bg-opacity-20 p-4 rounded-full">
                    <?php echo heroicon('currency-dollar', 'w-8 h-8'); ?>
                </div>
            </div>
        </div>
        
        <!-- کارت اجاره -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-200 fade-in" style="animation-delay: 0.4s">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm mb-1">اجاره</p>
                    <p class="text-3xl font-bold"><?php echo $stats['rent']; ?></p>
                </div>
                <div class="bg-white bg-opacity-20 p-4 rounded-full">
                    <?php echo heroicon('key', 'w-8 h-8'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- آخرین املاک -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <span class="ml-2 text-blue-600"><?php echo heroicon('clock', 'w-5 h-5'); ?></span>آخرین املاک اضافه شده
                </h2>
                <a href="<?php echo BASE_URL; ?>/properties/list.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    مشاهده همه <span class="mr-1 inline-block"><?php echo heroicon('arrow-left', 'w-4 h-4'); ?></span>
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="responsive-table min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">عنوان</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">نوع</th>
                                <th class="hidden md:block col-address px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">آدرس</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">تبدیل</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">قیمت</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">تاریخ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($recentProperties->num_rows > 0): ?>
                        <?php while ($property = $recentProperties->fetch_assoc()): ?>
                        <tr class="hover:bg-blue-50 transition-colors duration-150">
                            <td data-label="عنوان" class="card-title px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($property['title']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($property['city']); ?></div>
                            </td>
                            <td data-label="نوع" class="card-transaction px-6 py-4 whitespace-nowrap">
                                <?php
                                $types = ['buy' => 'خرید', 'sell' => 'فروش', 'mortgage' => 'رهن', 'rent' => 'اجاره'];
                                $typeColors = ['buy' => 'green', 'sell' => 'red', 'mortgage' => 'yellow', 'rent' => 'purple'];
                                // اگر type شامل کاما باشد، چند نوع معامله است
                                $typeArray = explode(',', $property['type']);
                                ?>
                                <div class=" flex flex-row-reverse flex-wrap gap-1">
                                    <?php foreach ($typeArray as $t): 
                                        $t = trim($t);
                                        if (isset($types[$t]) && isset($typeColors[$t])):
                                    ?>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-<?php echo $typeColors[$t]; ?>-100 text-<?php echo $typeColors[$t]; ?>-800">
                                        <?php echo $types[$t]; ?>
                                    </span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </td>
                            <td data-label="آدرس" class="hidden md:block col-address px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($property['address']); ?></div>
                            </td>
                            <td data-label="تبدیل" class="card-convert px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php if (floatval($property['convert_price']) > 0): ?>
                                    <?php echo heroicon('check-circle', 'w-5 h-5 text-green-500'); ?>
                                <?php else: ?>
                                    <?php echo heroicon('x-mark', 'w-5 h-5 text-gray-400'); ?>
                                <?php endif; ?>
                            </td>
                            <td data-label="قیمت" class="card-price px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php
                                $typeArray = explode(',', $property['type']);
                                $parts = [];
                                if ((in_array('buy', $typeArray) || in_array('sell', $typeArray)) && floatval($property['price']) > 0) {
                                    $parts[] = number_format($property['price']) . ' تومان';
                                }
                                if (in_array('mortgage', $typeArray) && floatval($property['mortgage_price']) > 0) {
                                    $parts[] = 'رهن: ' . number_format($property['mortgage_price']) . ' تومان';
                                }
                                if (in_array('rent', $typeArray) && floatval($property['rent_price']) > 0) {
                                    $parts[] = 'اجاره: ' . number_format($property['rent_price']) . ' تومان';
                                }
                                if (!empty($parts)) {
                                    echo '<div class="text-sm font-bold text-gray-900">' . implode(' - ', $parts) . '</div>';
                                } else {
                                    echo '<div class="text-sm font-bold text-gray-900">-</div>';
                                }
                                ?>
                            </td>
                            <td data-label="تاریخ" class="card-date px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex items-center">
                                <span class="ml-1"><?php echo heroicon('calendar', 'w-4 h-4'); ?></span>
                                <?php echo date('Y/m/d', strtotime($property['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-gray-300 mb-4"><?php echo heroicon('home', 'w-16 h-16'); ?></span>
                                    <p class="text-gray-500 text-lg">هنوز ملکی اضافه نشده است</p>
                                    <a href="<?php echo BASE_URL; ?>/properties/add.php" class="mt-4 text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                        <span class="ml-1"><?php echo heroicon('plus', 'w-4 h-4'); ?></span>افزودن اولین ملک
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>

