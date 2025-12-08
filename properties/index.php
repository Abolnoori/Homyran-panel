<?php
// هدایت به صفحه لیست املاک
require_once __DIR__ . '/../config/database.php';
header('Location: ' . BASE_URL . '/properties/list.php');
exit();
?>

