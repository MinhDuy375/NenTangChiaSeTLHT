<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../../config/ketNoiDB.php';

// Cập nhật trạng thái và xóa token nếu user đang đăng nhập
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    try {
        // Cập nhật trạng thái và xóa remember token
        $stmt = $pdo->prepare("UPDATE nguoi_dung 
                               SET trang_thai = 'hoat_dong', 
                                   ngay_cap_nhat = NOW(), 
                                   remember_token = NULL 
                               WHERE id = ?");
        $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        error_log("Logout error: " . $e->getMessage());
    }
}

// Xóa toàn bộ session
$_SESSION = [];

// Xóa cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Xóa cookie remember_token
if (isset($_COOKIE['remember_token'])) {
    setcookie("remember_token", "", time() - 3600, "/");
    unset($_COOKIE['remember_token']);
}

// Chuyển hướng về trang đăng nhập
header("Location: ../../index.php?page=login");
exit;
