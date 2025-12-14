<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';
requireLogin();

$conn = getDBConnection();

// دریافت پارامترها
$search = $_GET['search'] ?? '';
$price = $_GET['price'] ?? '';
$area = $_GET['area'] ?? '';
$propertyTypeFilter = $_GET['property_type'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$conditions = [];
$conditions[] = 'user_id = ' . intval($_SESSION['user_id']);

if ($typeFilter) {
    $escapedType = $conn->real_escape_string($typeFilter);
    $conditions[] = "(type = '$escapedType' OR type LIKE '$escapedType,%' OR type LIKE '%,$escapedType' OR type LIKE '%,$escapedType,%')";
}

if ($statusFilter) {
    $conditions[] = "status = '" . $conn->real_escape_string($statusFilter) . "'";
}

if ($propertyTypeFilter) {
    $conditions[] = "property_type = '" . $conn->real_escape_string($propertyTypeFilter) . "'";
}

if ($search) {
    $esc = $conn->real_escape_string($search);
    $conditions[] = "(title LIKE '%$esc%' OR city LIKE '%$esc%' OR address LIKE '%$esc%')";
}

if ($price !== '' && is_numeric($price)) {
    // Apply price filter based on selected transaction type(s).
    // If type is specified, map to the correct column(s): buy/sell -> price, mortgage -> mortgage_price, rent -> rent_price
    $priceInt = intval($price);
    if ($typeFilter) {
        $typeParts = array_map('trim', explode(',', $typeFilter));
        $priceClauses = [];
        foreach ($typeParts as $tp) {
            if (in_array($tp, ['buy', 'sell'])) {
                $priceClauses[] = 'price <= ' . $priceInt;
            } elseif ($tp === 'mortgage') {
                $priceClauses[] = 'mortgage_price <= ' . $priceInt;
            } elseif ($tp === 'rent') {
                $priceClauses[] = 'rent_price <= ' . $priceInt;
            }
        }
        if (!empty($priceClauses)) {
            // combine with OR because any matching price column should include the row
            $conditions[] = '(' . implode(' OR ', $priceClauses) . ')';
        }
    } else {
        // no specific type selected: default to sale price
        $conditions[] = 'price <= ' . $priceInt;
    }
}
if ($area !== '' && is_numeric($area)) {
    // Filter exact area match as requested (not <=)
    $conditions[] = 'area = ' . intval($area);
}

$where = '';
if (count($conditions) > 0) {
    $where = ' WHERE ' . implode(' AND ', $conditions);
}

$query = 'SELECT * FROM properties' . $where . ' ORDER BY created_at DESC';
$res = $conn->query($query);

// helper arrays (same as list.php)
$types = ['buy' => 'خرید', 'sell' => 'فروش', 'mortgage' => 'رهن', 'rent' => 'اجاره'];
$typeColors = ['buy' => 'green', 'sell' => 'red', 'mortgage' => 'yellow', 'rent' => 'purple'];
$propertyTypes = ['apartment' => 'آپارتمان', 'villa' => 'ویلا', 'land' => 'زمین', 'shop' => 'مغازه', 'office' => 'دفتر'];
$statuses = ['active' => 'فعال', 'sold' => 'فروخته شده', 'rented' => 'اجاره داده شده', 'inactive' => 'غیرفعال'];
$statusColors = ['active' => 'green', 'sold' => 'blue', 'rented' => 'purple', 'inactive' => 'gray'];

if ($res && $res->num_rows > 0) {
    while ($property = $res->fetch_assoc()) {
        $typeArray = explode(',', $property['type']);
        $status = $property['status'];
        ?>
        <tr>
            <td class="card-transaction px-6 py-4 whitespace-nowrap">
                <div class="mb-1">
                    <span class="text-xs text-gray-500"><?php echo $propertyTypes[$property['property_type']] ?? $property['property_type']; ?></span>
                </div>
                <div class="flex flex-row-reverse flex-wrap gap-1">
                    <?php foreach ($typeArray as $t): ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-<?php echo $typeColors[$t] ?? 'gray'; ?>-100 text-<?php echo $typeColors[$t] ?? 'gray'; ?>-800">
                            <?php echo $types[$t] ?? $t; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </td>
            <td class="card-title px-6 py-4">
                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($property['title']); ?></div>
                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($property['city']); ?></div>
            </td>
            <td class="card-area px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <?php echo number_format($property['area']); ?> متر
            </td>
            <td class="card-price px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                <?php
                $typeArray = explode(',', $property['type']);
                $showParts = [];
                // Sale price only if buy/sell is set for this property
                if ((in_array('buy', $typeArray) || in_array('sell', $typeArray)) && floatval($property['price']) > 0) {
                    $showParts[] = number_format($property['price']) . ' تومان';
                }
                if (in_array('mortgage', $typeArray) && floatval($property['mortgage_price']) > 0) {
                    $showParts[] = 'رهن: ' . number_format($property['mortgage_price']) . ' تومان';
                }
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
            <td class="col-address card-address px-6 py-4">
                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($property['address']); ?></div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <?php echo $property['bedrooms'] ?? 0; ?> خواب
            </td>
            <td class="card-convert px-6 py-4 whitespace-nowrap text-sm text-center">
                <?php if (floatval($property['convert_price'] ?? 0) > 0): ?>
                    <span class="text-green-600"><?php echo heroicon('check-circle', 'w-5 h-5'); ?></span>
                <?php else: ?>
                    <span class="text-red-600"><?php echo heroicon('x-mark', 'w-5 h-5'); ?></span>
                <?php endif; ?>
            </td>
            <td class="card-status px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-<?php echo $statusColors[$status] ?? 'gray'; ?>-100 text-<?php echo $statusColors[$status] ?? 'gray'; ?>-800">
                    <?php echo $statuses[$status] ?? $status; ?>
                </span>
            </td>
            <td class="card-actions px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="card-date text-sm text-gray-500 mb-2"><?php echo date('Y/m/d', strtotime($property['created_at'] ?? 'now')); ?></div>
                <details class="relative inline-block text-right">
                    <summary class="cursor-pointer text-blue-600">عملیات ▾</summary>
                    <div class="absolute left-0 mt-2 bg-white border rounded shadow p-2 z-10 min-w-[140px]">
                        <a href="<?php echo BASE_URL; ?>/properties/view.php?id=<?php echo $property['id']; ?>" class="block py-1 px-2 hover:bg-gray-100">مشاهده</a>
                        <a href="<?php echo BASE_URL; ?>/properties/edit.php?id=<?php echo $property['id']; ?>" class="block py-1 px-2 hover:bg-gray-100">ویرایش</a>
                        <a href="<?php echo BASE_URL; ?>/properties/delete.php?id=<?php echo $property['id']; ?>" onclick="return confirm('آیا مطمئن هستید؟')" class="block py-1 px-2 text-red-600 hover:bg-gray-100">حذف</a>
                    </div>
                </details>
            </td>
        </tr>
        <?php
    }
} else {
    ?>
    <tr>
        <td colspan="9" class="px-6 py-4 text-center text-gray-500">ملکی یافت نشد</td>
    </tr>
    <?php
}

$conn->close();

?>
