<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';
requireLogin();

$pageTitle = 'مشاهده ملک';
$conn = getDBConnection();

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
$stmt->close();

if (!$property) {
    header('Location: ' . BASE_URL . '/properties/list.php');
    exit();
}

$types = ['buy' => 'خرید', 'sell' => 'فروش', 'mortgage' => 'رهن', 'rent' => 'اجاره'];
$typeColors = ['buy' => 'green', 'sell' => 'red', 'mortgage' => 'yellow', 'rent' => 'purple'];
$statuses = ['active' => 'فعال', 'sold' => 'فروخته شده', 'rented' => 'اجاره داده شده', 'inactive' => 'غیرفعال'];
$propertyTypes = ['apartment' => 'آپارتمان', 'villa' => 'ویلا', 'land' => 'زمین', 'shop' => 'مغازه', 'office' => 'دفتر'];

include __DIR__ . '/../includes/header.php';
?>

<div class="w-full mx-auto px-1 md:px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="<?php echo BASE_URL; ?>/properties/list.php" class="text-blue-600 hover:text-blue-800 flex items-center">
            <span class="ml-2"><?php echo heroicon('arrow-right', 'w-4 h-4'); ?></span>بازگشت به لیست
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <?php if ($property['image_path']): ?>
        <img src="<?php echo htmlspecialchars($property['image_path']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>" class="w-full h-64 object-cover">
        <?php endif; ?>
        
        <div class="p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-xl md:text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($property['title']); ?></h1>
                    <p class="text-sm md:text-base text-gray-600 mb-2"><?php echo !empty($property['city']) ? htmlspecialchars($property['city']) . ' - ' : ''; ?><?php echo htmlspecialchars($property['address']); ?></p>
                    <?php
                    // نمایش خلاصه نوع/وضعیت/جهت به صورت کوچک و خطی زیر عنوان
                    $smallLabels = [];
                    $smallLabels[] = '<span class="text-xs inline-block mr-2 px-2 py-1 rounded-full bg-gray-50 text-gray-600">' . ($propertyTypes[$property['property_type']] ?? $property['property_type']) . '</span>';
                    $smallLabels[] = '<span class="text-xs inline-block mr-2 px-2 py-1 rounded-full bg-gray-50 text-gray-600">' . ($statuses[$property['status']] ?? $property['status']) . '</span>';
                    if (!empty($property['direction'])) {
                        $smallLabels[] = '<span class="text-xs inline-block mr-2 px-2 py-1 rounded-full bg-gray-50 text-gray-600">' . ($property['direction'] === 'north' ? 'شمالی' : 'جنوبی') . '</span>';
                    }
                    echo implode('', $smallLabels);
                    ?>
                </div>
                <div class="text-left">
                    <?php
                    // اگر type شامل کاما باشد، چند نوع معامله است
                    $typeArray = explode(',', $property['type']);
                    ?>
                    <div class="flex flex-row-reverse flex-wrap gap-1">
                        <?php foreach ($typeArray as $t): 
                            $t = trim($t);
                            if (isset($types[$t]) && isset($typeColors[$t])):
                        ?>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-<?php echo $typeColors[$t]; ?>-100 text-<?php echo $typeColors[$t]; ?>-800">
                            <?php echo $types[$t]; ?>
                        </span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-600 text-sm mb-1">قیمت</p>
                    <?php if (floatval($property['mortgage_price']) > 0): ?>
                        <p class="text-xl md:text-2xl font-bold text-gray-800">رهن: <?php echo number_format($property['mortgage_price']); ?></p>
                    <?php elseif (floatval($property['rent_price']) > 0): ?>
                        <p class="text-xl md:text-2xl font-bold text-gray-800">اجاره: <?php echo number_format($property['rent_price']); ?></p>
                    <?php else: ?>
                        <p class="text-xl md:text-2xl font-bold text-gray-800"><?php echo number_format($property['price']); ?></p>
                    <?php endif; ?>
                    <div class="text-xs text-gray-500">تومان</div>
                    <?php if (floatval($property['convert_price']) > 0): ?>
                        <div class="mt-2 text-sm text-gray-700">تبدیل: <?php echo number_format($property['convert_price']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-600 text-sm mb-1">متراژ</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-800"><?php echo number_format($property['area']); ?> متر مربع</p>
                </div>
            </div>

           

            <?php
            // نمایش اطلاعات تماس همیشه بیرون از بخش "بیشتر"
            $contacts = [];
            if (!empty($property['contacts'])) {
                $contacts = json_decode($property['contacts'], true);
                if (!is_array($contacts)) {
                    $contacts = [];
                }
            }
            // اگر اطلاعات تماس قدیمی وجود دارد
            if (empty($contacts)) {
                if (!empty($property['owner_name']) || !empty($property['owner_phone'])) {
                    $contacts[] = [
                        'name' => $property['owner_name'] ?? '',
                        'party' => 'owner',
                        'phone' => $property['owner_phone'] ?? ''
                    ];
                }
                if (!empty($property['tenant_phone'])) {
                    $contacts[] = [
                        'name' => '',
                        'party' => 'tenant',
                        'phone' => $property['tenant_phone']
                    ];
                }
            }
             ?>

            <div id="moreSection" class="hidden">
            <div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-xl md:text-2xl font-bold text-gray-800" ><?php echo (int)$property['bedrooms']; ?></div>
                        <div class="text-sm text-gray-600 mb-2">تعداد خواب</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-xl md:text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($property['floor']); ?></div>
                        <div class="text-sm text-gray-600 mb-2">طبقه</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-xl md:text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($property['vacancy_months']); ?></div>
                        <div class="text-sm text-gray-600 mb-2">تخلیه (ماه)</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-xl md:text-2xl font-bold text-gray-800"><?php echo date('Y/m/d', strtotime($property['created_at'])); ?></div>
                        <div class="text-sm text-gray-600 mb-2">تاریخ ثبت</div>
                    </div>
                </div>

                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">امکانات</h3>
                    <div class="flex flex-wrap gap-3">
                        <?php
                        $featureMap = [
                            'has_elevator' => ['icon' => 'elevator', 'label' => 'آسانسور'],
                            'has_parking' => ['icon' => 'car', 'label' => 'پارکینگ'],
                            'has_warehouse' => ['icon' => 'box', 'label' => 'انباری'],
                            'has_phone' => ['icon' => 'phone', 'label' => 'تلفن'],
                            'has_cooler' => ['icon' => 'snowflake', 'label' => 'کولر'],
                            'has_carpet' => ['icon' => 'squares-2x2', 'label' => 'موکت'],
                            'has_ceramic' => ['icon' => 'square', 'label' => 'سرامیک'],
                        ];
                        $hasAny = false;
                        foreach ($featureMap as $k => $f) {
                            if (!empty($property[$k])) {
                                $hasAny = true;
                                echo '<span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm">' . heroicon($f['icon'], 'w-4 h-4') . '<span>' . $f['label'] . '</span></span>';
                            }
                        }
                        if (!$hasAny) {
                            echo '<span class="text-gray-500">امکاناتی ثبت نشده است</span>';
                        }
                        ?>
                    </div>
                </div>

                <?php if ($property['description']): ?>
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">توضیحات</h3>
                    <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                </div>
                <?php endif; ?>

                <?php
                // نمایش اطلاعات تماس
                $contacts = [];
                if (!empty($property['contacts'])) {
                    $contacts = json_decode($property['contacts'], true);
                    if (!is_array($contacts)) {
                        $contacts = [];
                    }
                }
                // اگر اطلاعات تماس قدیمی وجود دارد
                if (empty($contacts)) {
                    if (!empty($property['owner_name']) || !empty($property['owner_phone'])) {
                        $contacts[] = [
                            'name' => $property['owner_name'] ?? '',
                            'party' => 'owner',
                            'phone' => $property['owner_phone'] ?? ''
                        ];
                    }
                    if (!empty($property['tenant_phone'])) {
                        $contacts[] = [
                            'name' => '',
                            'party' => 'tenant',
                            'phone' => $property['tenant_phone']
                        ];
                    }
                }
                ?>

            </div>
            </div>

 <div class="mb-4 text-left">
                <button id="toggleMore" type="button" class="bg-blue-100 text-blue-800 px-4 py-2 rounded hover:bg-blue-200">
                    بیشتر
                </button>
            </div>


            <?php
            if (!empty($contacts)): ?>
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-3">اطلاعات تماس</h3>
                <div class="space-y-3">
                    <?php foreach ($contacts as $contact): ?>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-600 mb-2"><?php echo !empty($contact['name']) ? htmlspecialchars($contact['name']) : 'بدون نام'; ?></p>
                                <p class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($contact['phone']); ?></p>
                            </div>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full <?php echo ($contact['party'] ?? 'owner') === 'owner' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                <?php echo ($contact['party'] ?? 'owner') === 'owner' ? 'مالک' : 'مستاجر'; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif;?>





            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t">
                <a href="<?php echo BASE_URL; ?>/properties/edit.php?id=<?php echo $property['id']; ?>" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded flex items-center justify-center">
                    <span class="ml-2"><?php echo heroicon('edit', 'w-4 h-4'); ?></span>ویرایش
                </a>
                <a href="<?php echo BASE_URL; ?>/properties/list.php" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded text-center">
                    بازگشت
                </a>
            </div>
        </div>
    </div>

<script>
    // Toggle 'بیشتر' section
    (function(){
        var btn = document.getElementById('toggleMore');
        var sec = document.getElementById('moreSection');
        if (btn && sec) {
            btn.addEventListener('click', function(){
                if (sec.classList.contains('hidden')) {
                    sec.classList.remove('hidden');
                    btn.textContent = 'کمتر';
                } else {
                    sec.classList.add('hidden');
                    btn.textContent = 'بیشتر';
                }
            });
        }
    })();
</script>
        </div>
    </div>
</div>

<?php
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>

