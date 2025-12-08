<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';
requireLogin();

$pageTitle = 'افزودن ملک';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // دریافت انواع معامله (چند مورد)
    $types = $_POST['types'] ?? [];
    $type = is_array($types) && !empty($types) ? implode(',', $types) : '';
    
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $area = $_POST['area'] ?? 0;
    // قیمت فقط در صورتی ذخیره می‌شود که نوع شامل خرید/فروش باشد
    $hasSale = (is_array($types) && (in_array('buy', $types) || in_array('sell', $types)));
    $price = $hasSale ? (float)($_POST['price'] ?? 0) : 0;
    $rooms = $_POST['rooms'] ?? 0;
    $bedrooms = $_POST['bedrooms'] ?? 0;
    $floor = $_POST['floor'] ?? 0;
    $building_age = $_POST['building_age'] ?? 0;
    $max_tenants = $_POST['max_tenants'] ?? 0;
    $property_type = $_POST['property_type'] ?? 'apartment';
    $building_status = $_POST['building_status'] ?? '';
    // قیمت‌ها را به صورت رشته می‌پذیریم تا فرمت‌شده (با ویرگول یا فاصله) مشکلی نداشته باشند
    $convert_price_raw = $_POST['convert_price'] ?? '';
    $convert_price = floatval(str_replace([',', ' '], '', $convert_price_raw)) ?: 0;
    $mortgage_price_raw = $_POST['mortgage_price'] ?? '';
    $mortgage_price = (is_array($types) && in_array('mortgage', $types)) ? floatval(str_replace([',', ' '], '', $mortgage_price_raw)) : 0;
    $rent_price_raw = $_POST['rent_price'] ?? '';
    $rent_price = (is_array($types) && in_array('rent', $types)) ? floatval(str_replace([',', ' '], '', $rent_price_raw)) : 0;
    // دریافت اطلاعات تماس (آرایه‌ای از مالک/مستاجر)
    $contacts = [];
    if (isset($_POST['contact_name']) && is_array($_POST['contact_name'])) {
        foreach ($_POST['contact_name'] as $index => $name) {
            if (!empty($name) && !empty($_POST['contact_phone'][$index])) {
                $contacts[] = [
                    'name' => $name,
                    'party' => $_POST['contact_party'][$index] ?? 'owner',
                    'phone' => $_POST['contact_phone'][$index]
                ];
            }
        }
    }
    $contacts_json = json_encode($contacts, JSON_UNESCAPED_UNICODE);
    
    $direction = $_POST['direction'] ?? '';
    
    $property_status = $_POST['property_status'] ?? 'empty';
    $vacancy_months = $_POST['vacancy_months'] ?? 0;
    
    // امکانات - آب و برق و گاز همیشه 1
    $has_elevator = isset($_POST['has_elevator']) ? 1 : 0;
    $has_parking = isset($_POST['has_parking']) ? 1 : 0;
    $has_warehouse = isset($_POST['has_warehouse']) ? 1 : 0;
    $has_water = 1; // همیشه روشن
    $has_electricity = 1; // همیشه روشن
    $has_gas = 1; // همیشه روشن
    $has_phone = isset($_POST['has_phone']) ? 1 : 0;
    $has_cabinet = isset($_POST['has_cabinet']) ? 1 : 0;
    $has_water_heater = isset($_POST['has_water_heater']) ? 1 : 0;
    $has_cooler = isset($_POST['has_cooler']) ? 1 : 0;
    $has_carpet = isset($_POST['has_carpet']) ? 1 : 0;
    $has_ceramic = isset($_POST['has_ceramic']) ? 1 : 0;
    $has_paint = isset($_POST['has_paint']) ? 1 : 0;
    $has_radiator = isset($_POST['has_radiator']) ? 1 : 0;
    $has_video_intercom = isset($_POST['has_video_intercom']) ? 1 : 0;
    $has_antenna = isset($_POST['has_antenna']) ? 1 : 0;
    $has_remote_door = isset($_POST['has_remote_door']) ? 1 : 0;
    $has_package = isset($_POST['has_package']) ? 1 : 0;
    $has_hidden_light = isset($_POST['has_hidden_light']) ? 1 : 0;
    
    if ($type && $title && $address && $area) {
        $conn = getDBConnection();
        $city = ''; // شهر اختیاری است
        $stmt = $conn->prepare("INSERT INTO properties (user_id, type, title, description, address, city, area, price, rooms, bedrooms, floor, building_age, max_tenants, property_type, building_status, convert_price, mortgage_price, rent_price, contacts, direction, property_status, vacancy_months, has_elevator, has_parking, has_warehouse, has_water, has_electricity, has_gas, has_phone, has_cabinet, has_water_heater, has_cooler, has_carpet, has_ceramic, has_paint, has_radiator, has_video_intercom, has_antenna, has_remote_door, has_package, has_hidden_light) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssddiiiiisssddsssiiiiiiiiiiiiiiiiiiii", $_SESSION['user_id'], $type, $title, $description, $address, $city, $area, $price, $rooms, $bedrooms, $floor, $building_age, $max_tenants, $property_type, $building_status, $convert_price, $mortgage_price, $rent_price, $contacts_json, $direction, $property_status, $vacancy_months, $has_elevator, $has_parking, $has_warehouse, $has_water, $has_electricity, $has_gas, $has_phone, $has_cabinet, $has_water_heater, $has_cooler, $has_carpet, $has_ceramic, $has_paint, $has_radiator, $has_video_intercom, $has_antenna, $has_remote_door, $has_package, $has_hidden_light);
        
        if ($stmt->execute()) {
            $success = 'ملک با موفقیت اضافه شد';
            $_POST = array(); // پاک کردن فرم
        } else {
            $error = 'خطا در افزودن ملک: ' . $conn->error;
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $error = 'لطفاً تمام فیلدهای الزامی را پر کنید (حداقل یک نوع معامله انتخاب کنید)';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="w-full mx-auto md:px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl pr-4 font-bold text-gray-800">افزودن ملک جدید</h1>
    </div>
    
    <?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="bg-white rounded-lg shadow p-6 space-y-6"> 
        <!-- نوع معامله -->
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-3">نوع معامله <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <?php
                $selectedTypes = isset($_POST['types']) && is_array($_POST['types']) ? $_POST['types'] : [];
                $typeOptions = [
                    'buy' => ['label' => 'خرید', 'icon' => 'shopping-cart', 'color' => 'green'],
                    'sell' => ['label' => 'فروش', 'icon' => 'tag', 'color' => 'red'],
                    'mortgage' => ['label' => 'رهن', 'icon' => 'currency-dollar', 'color' => 'yellow'],
                    'rent' => ['label' => 'اجاره', 'icon' => 'key', 'color' => 'purple'],
                ];
                foreach ($typeOptions as $value => $option):
                ?>
                <label class="flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer transition-all <?php echo in_array($value, $selectedTypes) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'; ?>">
                    <input type="checkbox" name="types[]" value="<?php echo $value; ?>" class="type-input" <?php echo in_array($value, $selectedTypes) ? 'checked' : ''; ?>>
                    <span class="text-<?php echo $option['color']; ?>-600"><?php echo heroicon($option['icon'], 'w-5 h-5'); ?></span>
                    <span class="text-sm font-medium"><?php echo $option['label']; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- اطلاعات اصلی -->
        <div class="bg-white border border-gray-200 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">اطلاعات اصلی</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">عنوان ملک <span class="text-red-500">*</span></label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                       class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">نوع ملک</label>
                <select name="property_type" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="apartment" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] === 'apartment') ? 'selected' : ''; ?>>آپارتمان</option>
                    <option value="villa" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] === 'villa') ? 'selected' : ''; ?>>ویلا</option>
                    <option value="land" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] === 'land') ? 'selected' : ''; ?>>زمین</option>
                    <option value="shop" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] === 'shop') ? 'selected' : ''; ?>>مغازه</option>
                    <option value="office" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] === 'office') ? 'selected' : ''; ?>>دفتر</option>
                </select>
            </div>
            </div>
        </div>
        
        <!-- اطلاعات فنی -->
        <div class="bg-white border border-gray-200 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">اطلاعات فنی</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">متراژ (متر) <span class="text-red-500">*</span></label>
                <input type="number" name="area" step="0.01" required value="<?php echo htmlspecialchars($_POST['area'] ?? ''); ?>"
                       class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">تعداد خواب</label>
                <input type="number" name="bedrooms" min="0" value="<?php echo htmlspecialchars($_POST['bedrooms'] ?? '0'); ?>"
                       class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">طبقه</label>
                <input type="number" name="floor" value="<?php echo htmlspecialchars($_POST['floor'] ?? '0'); ?>"
                       class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">حداکثر تعداد مستاجر</label>
                <input type="number" name="max_tenants" min="0" value="<?php echo htmlspecialchars($_POST['max_tenants'] ?? '0'); ?>"
                       class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">وضعیت بنا</label>
                <select name="building_status" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">انتخاب کنید</option>
                    <option value="new" <?php echo (isset($_POST['building_status']) && $_POST['building_status'] === 'new') ? 'selected' : ''; ?>>نوساز</option>
                    <option value="untouched" <?php echo (isset($_POST['building_status']) && $_POST['building_status'] === 'untouched') ? 'selected' : ''; ?>>کلید نخورده</option>
                    <option value="normal" <?php echo (isset($_POST['building_status']) && $_POST['building_status'] === 'normal') ? 'selected' : ''; ?>>معمولی</option>
                    <option value="needs_repair" <?php echo (isset($_POST['building_status']) && $_POST['building_status'] === 'needs_repair') ? 'selected' : ''; ?>>نیاز به تعمیر</option>
                    <option value="old" <?php echo (isset($_POST['building_status']) && $_POST['building_status'] === 'old') ? 'selected' : ''; ?>>کلنگی</option>
                </select>
            </div>
            </div>
        </div>
        
        <!-- پارکینگ و انباری -->
        <div class="bg-white border border-gray-200 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">پارکینگ و انباری</h3>
        <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="has_parking" value="1" <?php echo (isset($_POST['has_parking'])) ? 'checked' : ''; ?> class="w-4 h-4">
                <span class="text-sm">پارکینگ دارد</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="has_warehouse" value="1" <?php echo (isset($_POST['has_warehouse'])) ? 'checked' : ''; ?> class="w-4 h-4">
                <span class="text-sm">انباری دارد</span>
            </label>
            </div>
        </div>
        
        <!-- قیمت رهن و اجاره -->
        <div class="bg-white border border-gray-200 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">قیمت‌ها</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div>
          <label class="block text-gray-700 text-sm font-bold mb-2">قیمت فروش/خرید (تومان)</label>
          <input type="text" name="price" id="price" value="<?php echo htmlspecialchars($_POST['price'] ?? '0'); ?>"
              class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="مثلاً: 1,200,000">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">تبدیل</label>
          <input type="text" name="convert_price" id="convert_price" value="<?php echo htmlspecialchars($_POST['convert_price'] ?? '0'); ?>"
              class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="مثلاً: 90">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">قیمت رهن (تومان)</label>
          <input type="text" name="mortgage_price" id="mortgage_price" value="<?php echo htmlspecialchars($_POST['mortgage_price'] ?? '0'); ?>"
              class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="مثلاً: 120,000">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">قیمت اجاره (تومان)</label>
          <input type="text" name="rent_price" step="1000" value="<?php echo htmlspecialchars($_POST['rent_price'] ?? '0'); ?>"
              class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            </div>
        </div>
        
        <!-- اطلاعات تماس (مالک/مستاجر) -->
        <div class="bg-white border border-gray-200 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">اطلاعات تماس</h3>
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-3">اطلاعات تماس</label>
            <div id="contacts-container" class="space-y-4">
                <!-- اولین ردیف تماس -->
                <div class="contact-row grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border border-gray-200 rounded-lg">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">نام</label>
                        <input type="text" name="contact_name[]" placeholder="مثلاً: آقای فلانی"
                               class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">طرفین</label>
                        <select name="contact_party[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="owner">مالک</option>
                            <option value="tenant">مستاجر</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-gray-700 text-sm font-bold mb-2">شماره تلفن</label>
                            <input type="text" name="contact_phone[]" placeholder="09123456789"
                                   class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <button type="button" class="remove-contact bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded text-sm mb-2" style="display: none;">
                            حذف
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" id="add-contact" class="mt-3 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded text-sm">
                + افزودن تماس دیگر
            </button>
            </div>
        </div>
        
        <!-- آدرس و جهت -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">اطلاعات آدرس</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">آدرس کامل <span class="text-red-500">*</span></label>
                    <input type="text" name="address" required value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"
                           class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">جهت بنا</label>
                    <select name="direction" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">انتخاب کنید</option>
                        <option value="north" <?php echo (isset($_POST['direction']) && $_POST['direction'] === 'north') ? 'selected' : ''; ?>>شمالی</option>
                        <option value="south" <?php echo (isset($_POST['direction']) && $_POST['direction'] === 'south') ? 'selected' : ''; ?>>جنوبی</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- وضعیت ملک -->
        <div class="bg-blue-50 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">وضعیت ملک</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">وضعیت ملک</label>
                    <select name="property_status" id="property_status" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="empty" <?php echo (isset($_POST['property_status']) && $_POST['property_status'] === 'empty') ? 'selected' : 'selected'; ?>>خالی</option>
                        <option value="tenant" <?php echo (isset($_POST['property_status']) && $_POST['property_status'] === 'tenant') ? 'selected' : ''; ?>>مستاجر</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">زمان خالی شدن (ماه)</label>
                    <input type="number" name="vacancy_months" id="vacancy_months" min="0" value="<?php echo htmlspecialchars($_POST['vacancy_months'] ?? '0'); ?>"
                           placeholder="مثلاً: 10"
                           class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           disabled>
                </div>
            </div>
        </div>
        
        <!-- توضیحات -->
        <div class="bg-white border border-gray-200 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">توضیحات</h3>
            <textarea name="description" rows="4" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        
        <!-- امکانات -->
        <div class="bg-white border border-gray-200 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">امکانات</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <?php
                $features = [
                    'has_phone' => ['icon' => 'phone', 'label' => 'تلفن'],
                    'has_cabinet' => ['icon' => 'utensils', 'label' => 'کابینت'],
                    'has_water_heater' => ['icon' => 'shower', 'label' => 'آبگرمکن'],
                    'has_cooler' => ['icon' => 'snowflake', 'label' => 'کولر'],
                    'has_carpet' => ['icon' => 'squares-2x2', 'label' => 'موکت'],
                    'has_ceramic' => ['icon' => 'square', 'label' => 'سرامیک'],
                    'has_paint' => ['icon' => 'paint-brush', 'label' => 'نقاشی'],
                    'has_radiator' => ['icon' => 'fire', 'label' => 'شوفاژ'],
                    'has_video_intercom' => ['icon' => 'video-camera', 'label' => 'آیفون تصویری'],
                    'has_antenna' => ['icon' => 'signal', 'label' => 'آنتن مرکزی'],
                    'has_elevator' => ['icon' => 'elevator', 'label' => 'آسانسور'],
                    'has_remote_door' => ['icon' => 'door-open', 'label' => 'درب ریموت دار'],
                    'has_package' => ['icon' => 'box', 'label' => 'پکیج'],
                    'has_hidden_light' => ['icon' => 'light-bulb', 'label' => 'نور مخفی'],
                ];
                
                foreach ($features as $key => $feature):
                    $checked = isset($_POST[$key]) ? 'checked' : '';
                ?>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="<?php echo $key; ?>" value="1" <?php echo $checked; ?> class="w-4 h-4">
                    <span class="text-gray-600"><?php echo heroicon($feature['icon'], 'w-4 h-4'); ?></span>
                    <span class="text-sm"><?php echo $feature['label']; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- دکمه‌ها -->
        <div class="flex flex-col sm:flex-row gap-4 pt-4 border-t">
            <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded text-center">
                ثبت ملک
            </button>
            <a href="<?php echo BASE_URL; ?>/index.php" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded text-center">
                انصراف
            </a>
        </div>
    </form>
</div>

<script>
    // تغییر استایل کارت‌ها هنگام انتخاب/لغو انتخاب
    document.querySelectorAll('.type-input').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const label = this.closest('label');
            if (this.checked) {
                label.classList.add('border-blue-500', 'bg-blue-50');
                label.classList.remove('border-gray-200');
            } else {
                label.classList.remove('border-blue-500', 'bg-blue-50');
                label.classList.add('border-gray-200');
            }
        });
    });
    
    // اعتبارسنجی فرم
    document.querySelector('form').addEventListener('submit', function(e) {
        const checkedTypes = document.querySelectorAll('.type-input:checked');
        if (checkedTypes.length === 0) {
            e.preventDefault();
            alert('لطفاً حداقل یک نوع معامله را انتخاب کنید');
            return false;
        }
    });
    
    // مدیریت وضعیت ملک و زمان خالی شدن
    const propertyStatusSelect = document.getElementById('property_status');
    const vacancyMonthsInput = document.getElementById('vacancy_months');
    
    function updateVacancyInput() {
        if (propertyStatusSelect.value === 'tenant') {
            vacancyMonthsInput.disabled = false;
            vacancyMonthsInput.required = true;
            vacancyMonthsInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
        } else {
            vacancyMonthsInput.disabled = true;
            vacancyMonthsInput.required = false;
            vacancyMonthsInput.value = '0';
            vacancyMonthsInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        }
    }
    
    // اجرای اولیه
    updateVacancyInput();
    
    // تغییر وضعیت
    propertyStatusSelect.addEventListener('change', updateVacancyInput);
    
    // تابع برای به‌روزرسانی نمایش دکمه‌های حذف
    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.contact-row');
        const removeButtons = document.querySelectorAll('.remove-contact');
        if (rows.length > 1) {
            removeButtons.forEach(btn => btn.style.display = 'block');
        } else {
            removeButtons.forEach(btn => btn.style.display = 'none');
        }
    }
    
    // افزودن ردیف تماس جدید
    document.getElementById('add-contact').addEventListener('click', function() {
        const container = document.getElementById('contacts-container');
        const newRow = document.createElement('div');
        newRow.className = 'contact-row grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border border-gray-200 rounded-lg';
        newRow.innerHTML = `
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">نام</label>
                <input type="text" name="contact_name[]" placeholder="مثلاً: آقای فلانی"
                       class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">طرفین</label>
                <select name="contact_party[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="owner">مالک</option>
                    <option value="tenant">مستاجر</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-gray-700 text-sm font-bold mb-2">شماره تلفن</label>
                    <input type="text" name="contact_phone[]" placeholder="09123456789"
                           class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="button" class="remove-contact bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded text-sm mb-2">
                    حذف
                </button>
            </div>
        `;
        container.appendChild(newRow);
        updateRemoveButtons();
        
        // اضافه کردن event listener برای دکمه حذف
        newRow.querySelector('.remove-contact').addEventListener('click', function() {
            newRow.remove();
            updateRemoveButtons();
        });
    });
    
    // حذف ردیف تماس
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-contact')) {
            e.target.closest('.contact-row').remove();
            updateRemoveButtons();
        }
    });
    
    // به‌روزرسانی اولیه
    updateRemoveButtons();
    
    // محاسبه تبدیل قیمت رهن
    const convertPriceInput = document.getElementById('convert_price');
    const mortgagePriceInput = document.getElementById('mortgage_price');
    
    if (convertPriceInput && mortgagePriceInput) {
        // محاسبه خودکار تبدیل (در صورت نیاز)
        function calculateConvert() {
            const mortgage = parseFloat(mortgagePriceInput.value) || 0;
            const convert = parseFloat(convertPriceInput.value) || 0;
            // می‌توانید منطق تبدیل را اینجا اضافه کنید
        }
        
        mortgagePriceInput.addEventListener('input', calculateConvert);
        convertPriceInput.addEventListener('input', calculateConvert);
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
