<?php
session_start();

// بررسی لاگین بودن کاربر
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// دریافت اطلاعات کاربر فعلی
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, email, full_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    return $user;
}

// لاگین کاربر
function login($username, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, password, full_name FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $stmt->close();
            $conn->close();
            return true;
        }
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

// خروج از سیستم
function logout() {
    session_unset();
    session_destroy();
}

// نیاز به لاگین
function requireLogin() {
    if (!isLoggedIn()) {
        require_once __DIR__ . '/../config/database.php';
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}
?>

