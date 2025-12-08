<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';
requireLogin();

$pageTitle = 'لیست املاک';
$conn = getDBConnection();

// فیلترها
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$query = "SELECT * FROM properties WHERE user_id = {$_SESSION['user_id']}";
if ($typeFilter) {
    // اگر type شامل کاما باشد، باید با LIKE یا FIND_IN_SET جستجو کنیم
    // چون type می‌تواند "mortgage,rent" باشد و ما می‌خواهیم "mortgage" را پیدا کنیم
    $escapedType = $conn->real_escape_string($typeFilter);
    $query .= " AND (type = '$escapedType' OR type LIKE '$escapedType,%' OR type LIKE '%,$escapedType' OR type LIKE '%,$escapedType,%')";
}
if ($statusFilter) {
    $query .= " AND status = '" . $conn->real_escape_string($statusFilter) . "'";
}
// area exact match filter
$areaFilter = $_GET['area'] ?? '';
if ($areaFilter !== '' && is_numeric($areaFilter)) {
    $query .= " AND area = " . intval($areaFilter);
}
$query .= " ORDER BY created_at DESC";

$properties = $conn->query($query);

include __DIR__ . '/../includes/header.php';
?>

<div class="w-full mx-auto px-1 md:px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl md:text-3xl px-2 md:px-0 font-bold text-gray-800">لیست املاک</h1>
        <a href="<?php echo BASE_URL; ?>/properties/add.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
            <span class="ml-2"><?php echo heroicon('plus', 'w-4 h-4'); ?></span>افزودن ملک جدید
        </a>
    </div>
    
    <?php
    $search = $_GET['search'] ?? '';
    $price = $_GET['price'] ?? '';
    $area = $_GET['area'] ?? '';
    $propertyTypeFilter = $_GET['property_type'] ?? '';
        // $typeFilter and $statusFilter already set earlier
        ?>
            <style>
            /* Responsive filter bar for mobile: horizontal, scrollable, and taller so labels are visible
                 - Scoped to this page only.
                 - On small screens the form becomes a horizontal strip with swipe support.
                 - Labels are shown (smaller font) and inputs are slightly taller for readability.
            */
                @media (max-width: 640px) {
                    #filterForm { display: flex !important; flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; gap: .5rem; padding: .5rem; align-items: flex-start; }
                    /* default narrower items so more controls fit on screen */
                    #filterForm > div { flex: 0 0 auto; min-width: 150px; margin-bottom: 0 !important; padding: .25rem 0; box-sizing: border-box; }
                    /* show labels and reduce their size to fit the bar */
                    #filterForm label { display: block; font-size: 11px; color: #4b5563; margin-bottom: .25rem; }
                    /* make inputs/selects a bit taller and ensure a sensible min-width */
                    #filterForm input, #filterForm select { min-width: 130px; padding: .45rem .5rem; height: auto; box-sizing: border-box; }
                    /* keep search larger so it's easy to type and read - it can grow */
                    #filterForm #searchInput { min-width: 260px; flex: 1 0 260px; }
                }
            </style>
    <!-- فیلترها -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form id="filterForm" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="md:col-span-1">
                <label class="block text-gray-700 text-sm font-bold mb-2">جستجو</label>
                <div class="relative">
                    <input id="searchInput" type="text" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES); ?>"
                        placeholder="عنوان، شهر یا آدرس..." class="w-full border rounded px-3 py-2 text-sm shadow-sm" autocomplete="off" />
                    <div id="searchSuggestions" class="absolute z-50 right-0 left-0 mt-1 bg-white border rounded shadow max-h-60 overflow-auto hidden"></div>
                </div>
            </div>

                    <div class="grid grid-cols-1 gap-3 md:col-span-1">
                <div class="md:col-span-1">
                    <label class="block text-gray-700 text-sm font-bold mb-2">حداکثر قیمت</label>
                    <input type="number" name="price" id="priceInput" value="<?php echo htmlspecialchars($price, ENT_QUOTES); ?>"
                        placeholder="مثال: 900000" class="w-full border rounded px-3 py-2 text-sm shadow-sm" />
                </div>

            </div>

            <div class="md:col-span-1">

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">متراژ دقیق</label>
                    <input type="number" name="area" id="areaInput" value="<?php echo htmlspecialchars($area, ENT_QUOTES); ?>"
                        placeholder="مثال: 120" class="w-full border rounded px-3 py-2 text-sm shadow-sm" />
                </div>



            </div>

            <div class="md:col-span-1">
                <label class="block text-gray-700 text-sm font-bold mb-2">نوع معامله</label>
                <select name="type" class="w-full border rounded px-3 py-2 text-sm shadow-sm">
                    <option value="">همه</option>
                    <option value="buy" <?php echo ($typeFilter === 'buy') ? 'selected' : ''; ?>>خرید</option>
                    <option value="sell" <?php echo ($typeFilter === 'sell') ? 'selected' : ''; ?>>فروش</option>
                    <option value="mortgage" <?php echo ($typeFilter === 'mortgage') ? 'selected' : ''; ?>>رهن</option>
                    <option value="rent" <?php echo ($typeFilter === 'rent') ? 'selected' : ''; ?>>اجاره</option>
                </select>
            </div>

            <div class="md:col-span-1">
                <label class="block text-gray-700 text-sm font-bold mb-2">نوع ملک</label>
                <select name="property_type" class="w-full border rounded px-3 py-2 text-sm shadow-sm">
                    <option value="">همه</option>
                    <option value="apartment" <?php echo ($propertyTypeFilter === 'apartment') ? 'selected' : ''; ?>>آپارتمان</option>
                    <option value="villa" <?php echo ($propertyTypeFilter === 'villa') ? 'selected' : ''; ?>>ویلا</option>
                    <option value="land" <?php echo ($propertyTypeFilter === 'land') ? 'selected' : ''; ?>>زمین</option>
                    <option value="shop" <?php echo ($propertyTypeFilter === 'shop') ? 'selected' : ''; ?>>مغازه</option>
                    <option value="office" <?php echo ($propertyTypeFilter === 'office') ? 'selected' : ''; ?>>دفتر</option>
                </select>
            </div>

            <div class="md:col-span-1">
                <label class="block text-gray-700 text-sm font-bold mb-2">وضعیت</label>
                <select name="status" class="w-full border rounded px-3 py-2 text-sm shadow-sm">
                    <option value="">همه</option>
                    <option value="active" <?php echo ($statusFilter === 'active') ? 'selected' : ''; ?>>فعال</option>
                    <option value="sold" <?php echo ($statusFilter === 'sold') ? 'selected' : ''; ?>>فروخته شده</option>
                    <option value="rented" <?php echo ($statusFilter === 'rented') ? 'selected' : ''; ?>>اجاره داده شده</option>
                    <option value="inactive" <?php echo ($statusFilter === 'inactive') ? 'selected' : ''; ?>>غیرفعال</option>
                </select>
            </div>
        </form>
    </div>
    
    <!-- جدول املاک -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="responsive-table min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع ملک</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">عنوان</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">متراژ</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">قیمت</th>
                        <th class="hidden md:block col-address px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">آدرس</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تعداد خواب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تبدیل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">وضعیت</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">عملیات</th>
                    </tr>
                </thead>
                <tbody id="propertiesTbody" class="bg-white divide-y divide-gray-200">
                    <?php if ($properties->num_rows > 0): ?>
                        <?php while ($property = $properties->fetch_assoc()): ?>
                        <tr>
                            <td data-label="نوع ملک" class="card-transaction px-6 py-4 whitespace-nowrap">
                                <?php
                                $types = ['buy' => 'خرید', 'sell' => 'فروش', 'mortgage' => 'رهن', 'rent' => 'اجاره'];
                                $typeColors = ['buy' => 'green', 'sell' => 'red', 'mortgage' => 'yellow', 'rent' => 'purple'];
                                $propertyTypes = ['apartment' => 'آپارتمان', 'villa' => 'ویلا', 'land' => 'زمین', 'shop' => 'مغازه', 'office' => 'دفتر'];
                                
                                // اگر type شامل کاما باشد، چند نوع است
                                $typeArray = explode(',', $property['type']);
                                $firstType = $typeArray[0];
                                ?>
                                <div class="mb-1">
                                    <span class="text-xs text-gray-500"><?php echo $propertyTypes[$property['property_type']] ?? $property['property_type']; ?></span>
                                </div>
                                <div class="flex  flex-wrap gap-1">
                                    <?php foreach ($typeArray as $t): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-<?php echo $typeColors[$t] ?? 'gray'; ?>-100 text-<?php echo $typeColors[$t] ?? 'gray'; ?>-800">
                                        <?php echo $types[$t] ?? $t; ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td data-label="عنوان" class="card-title px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($property['title']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($property['city']); ?></div>
                            </td>
                            <td data-label="متراژ" class="card-area px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo number_format($property['area']); ?> متر
                            </td>
                            <td data-label="قیمت" class="card-price px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php
                                // نمایش هوشمند قیمت: اگر رهن/اجاره فعال است آن‌ها را نمایش بده، وگرنه قیمت فروش/خرید
                                $typeArray = explode(',', $property['type']);
                                $showParts = [];
                                // اگر نوع شامل خرید یا فروش باشد، قیمت فروش را نمایش بده
                                if ((in_array('buy', $typeArray) || in_array('sell', $typeArray)) && floatval($property['price']) > 0) {
                                    $showParts[] = number_format($property['price']) . ' تومان';
                                }
                                // رهن فقط وقتی نمایش داده می‌شود که در نوع آمده باشد
                                if (in_array('mortgage', $typeArray) && floatval($property['mortgage_price']) > 0) {
                                    $showParts[] = 'رهن: ' . number_format($property['mortgage_price']) . ' تومان';
                                }
                                // اگر اجاره فعال است، رهن و اجاره را نمایش بده (در صورت وجود)
                                if (in_array('rent', $typeArray) && floatval($property['rent_price']) > 0) {
                                    $showParts[] = 'اجاره: ' . number_format($property['rent_price']) . ' تومان';
                                }
                                if (!empty($showParts)) {
                                    echo implode(' - ', $showParts);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td data-label="آدرس" class="hidden md:block col-address px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($property['address']); ?></div>
                            </td>
                            <td data-label="تعداد خواب" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $property['bedrooms'] ?? 0; ?> خواب
                            </td>
                            <td data-label="تبدیل" class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                <?php if (floatval($property['convert_price'] ?? 0) > 0): ?>
                                    <span class="text-green-600"><?php echo heroicon('check-circle', 'w-5 h-5'); ?></span>
                                <?php else: ?>
                                    <span class="text-red-600"><?php echo heroicon('x-mark', 'w-5 h-5'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="وضعیت" class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statuses = ['active' => 'فعال', 'sold' => 'فروخته شده', 'rented' => 'اجاره داده شده', 'inactive' => 'غیرفعال'];
                                $statusColors = ['active' => 'green', 'sold' => 'blue', 'rented' => 'purple', 'inactive' => 'gray'];
                                $status = $property['status'];
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-<?php echo $statusColors[$status]; ?>-100 text-<?php echo $statusColors[$status]; ?>-800">
                                    <?php echo $statuses[$status]; ?>
                                </span>
                            </td>
                            <td data-label="عملیات" class="card-actions px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="card-date text-sm text-gray-500 mb-2"><?php echo date('Y/m/d', strtotime($property['created_at'] ?? 'now')); ?></div>
                                <details class="relative inline-block text-right">
                                    <summary class="cursor-pointer text-blue-600">عملیات ▾</summary>
                                    <div class="absolute left-0 mt-2 bg-white border rounded shadow p-2 z-10 min-w-[140px] actions-menu">
                                        <a href="<?php echo BASE_URL; ?>/properties/view.php?id=<?php echo $property['id']; ?>" class="block py-1 px-2 hover:bg-gray-100">مشاهده</a>
                                        <a href="<?php echo BASE_URL; ?>/properties/edit.php?id=<?php echo $property['id']; ?>" class="block py-1 px-2 hover:bg-gray-100">ویرایش</a>
                                        <a href="<?php echo BASE_URL; ?>/properties/delete.php?id=<?php echo $property['id']; ?>" onclick="return confirm('آیا مطمئن هستید؟')" class="block py-1 px-2 text-red-600 hover:bg-gray-100">حذف</a>
                                    </div>
                                </details>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-gray-500">ملکی یافت نشد</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$conn->close();
include __DIR__ . '/../includes/footer.php';
?>

<script>
// AJAX filter: submit the form via fetch and replace tbody
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    const tbody = document.getElementById('propertiesTbody');
    if (!form || !tbody) return;
    const endpoint = '<?php echo BASE_URL; ?>/properties/list_ajax.php';

    // debounce helper
    function debounce(fn, ms) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    async function fetchAndRender() {
        try {
            const params = new URLSearchParams(new FormData(form));
            const res = await fetch(endpoint + '?' + params.toString(), { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Network response was not ok');
            const html = await res.text();
            tbody.innerHTML = html;
        } catch (err) {
            console.error('Failed to fetch filtered properties', err);
        }
    }

    const debouncedFetch = debounce(fetchAndRender, 300);

    // intercept submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchAndRender();
    });

    // auto fetch on change for better UX
    form.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', debouncedFetch);
        el.addEventListener('change', debouncedFetch);
    });

    // Search suggestions (autocomplete)
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('searchSuggestions');
    const suggestEndpoint = '<?php echo BASE_URL; ?>/properties/list_suggest.php';

    if (searchInput && suggestionsBox) {
        const fetchSuggestions = debounce(async function() {
            const q = searchInput.value.trim();
            if (!q) {
                suggestionsBox.innerHTML = '';
                suggestionsBox.classList.add('hidden');
                return;
            }
            try {
                const res = await fetch(suggestEndpoint + '?q=' + encodeURIComponent(q), { credentials: 'same-origin' });
                if (!res.ok) throw new Error('Network error');
                const list = await res.json();
                if (!Array.isArray(list) || list.length === 0) {
                    suggestionsBox.innerHTML = '';
                    suggestionsBox.classList.add('hidden');
                    return;
                }
                suggestionsBox.innerHTML = list.map(item => {
                    // escape
                    const safe = item.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    return `<div class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm">${safe}</div>`;
                }).join('');
                suggestionsBox.classList.remove('hidden');

                // click handlers
                suggestionsBox.querySelectorAll('div').forEach(div => {
                    div.addEventListener('click', function() {
                        searchInput.value = this.textContent;
                        suggestionsBox.classList.add('hidden');
                        fetchAndRender();
                    });
                });
            } catch (err) {
                console.error('Suggestion fetch failed', err);
            }
        }, 250);

        searchInput.addEventListener('input', fetchSuggestions);

        // hide on outside click
        document.addEventListener('click', function(e) {
            if (!suggestionsBox.contains(e.target) && e.target !== searchInput) {
                suggestionsBox.classList.add('hidden');
            }
        });

        // keyboard navigation: Enter selects first suggestion
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                // if suggestions visible, pick first
                const first = suggestionsBox.querySelector('div');
                if (first) {
                    e.preventDefault();
                    searchInput.value = first.textContent;
                    suggestionsBox.classList.add('hidden');
                    fetchAndRender();
                }
            }
        });
    }
});
</script>

