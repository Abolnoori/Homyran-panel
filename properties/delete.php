<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = $_GET['id'] ?? 0;

if ($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM properties WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header('Location: ' . BASE_URL . '/properties/list.php');
exit();
?>

