<?php
// Định nghĩa BASE_URL
define('BASE_URL', '/');

// Include các hàm helper cần thiết
session_start();

// Lấy tham số ?page=... từ URL, mặc định là "home"
$page = $_GET['page'] ?? 'home';

// Đặt tiêu đề động
$title = ucfirst($page);

// Bắt đầu output buffering
ob_start();

// Route các trang
switch ($page) {
    case 'home':
        include "TrangChu.php";
        break;
    case 'monhoc':
        include "danhSachMon.php";
        break;
    case 'tailieumon':
        include "taiLieuMon.php";
        break;
    case 'chitiettailieu':
        include "chiTietTaiLieu.php";
        break;     
    case 'contact':
        include "lienHe.php";
        break;
    case 'upload':
        include "dangTaiTaiLieu.php";
        break;
    case 'source':
        include "ThuVienNguon.php";
        break;
    case 'source_upload':
        include "dangTaiNguon.php";
        break;    
    case 'source_detail':
        include "chiTietNguon.php";
        break;  
     case 'login':
        include "login.php";
        break;  
    case 'logout':
        include "logout.php";
        break;
    case 'register':
        include "register.php";
        break;
    case 'reset_password':
        include "reset_password.php";
        break;
    case 'verify_forgot':
        include "verify_forgot.php";
        break;
    case 'verify':
        include "verify.php";
        break;
    case 'forgotpass':
        include "forgotpass.php";
        break;
    case 'userinfo':
        include "user_profile.php";
        break;

    default:
        echo "<div style='text-align: center; padding: 50px;'>";
        echo "<h2>404 - Không tìm thấy trang</h2>";
        echo "<p>Trang bạn đang tìm kiếm không tồn tại.</p>";
        echo "<a href='index.php?page=home' style='color: #007bff; text-decoration: none;'>← Về trang chủ</a>";
        echo "</div>";
}

// Lấy nội dung đã được tạo
$content = ob_get_clean();

// Include layout
include "layout.php";
