<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$conn = getDBConnection();
$q = $_GET['q'] ?? '';
$q = trim($q);
header('Content-Type: application/json; charset=utf-8');

if ($q === '') {
    echo json_encode([]);
    exit;
}

$esc = $conn->real_escape_string($q);
$like = "%$esc%";

$userId = intval($_SESSION['user_id']);

$query = "SELECT title, city, address FROM properties WHERE user_id = $userId AND (title LIKE '$like' OR city LIKE '$like' OR address LIKE '$like') ORDER BY created_at DESC LIMIT 50";
$res = $conn->query($query);

$suggestions = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        // prefer title, but also include city/address when relevant
        if (!empty($row['title']) && stripos($row['title'], $q) !== false) {
            $val = $row['title'];
            if (!in_array($val, $suggestions)) $suggestions[] = $val;
        }
        if (!empty($row['city']) && stripos($row['city'], $q) !== false) {
            $val = $row['city'];
            if (!in_array($val, $suggestions)) $suggestions[] = $val;
        }
        if (!empty($row['address']) && stripos($row['address'], $q) !== false) {
            $val = $row['address'];
            if (!in_array($val, $suggestions)) $suggestions[] = $val;
        }
        if (count($suggestions) >= 8) break;
    }
}

echo json_encode(array_values(array_slice($suggestions, 0, 8)));
$conn->close();
exit;
