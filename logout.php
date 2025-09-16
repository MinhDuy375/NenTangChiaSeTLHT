<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'ketNoiDB.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Cập nhật trạng thái và xóa token
    $stmt = $pdo->prepare("UPDATE nguoi_dung 
                           SET trang_thai = 'khoa', 
                               ngay_cap_nhat = NOW(), 
                               remember_token = NULL 
                           WHERE id = ?");
    $stmt->execute([$user_id]);
}

// Xóa toàn bộ session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Xóa cookie remember_token nếu có
if (isset($_COOKIE['remember_token'])) {
    setcookie("remember_token", "", time() - 3600, "/");
}

header("Location: index.php?page=login");
exit;