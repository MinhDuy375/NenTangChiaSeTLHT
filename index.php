<?php
// Định nghĩa BASE_URL
define('BASE_URL', '/');

// Include các hàm helper cần thiết


// Lấy tham số ?page=... từ URL, mặc định là "home"
$page = $_GET['page'] ?? 'home';

// Đặt tiêu đề động
$title = ucfirst($page);

// Bắt đầu output buffering
ob_start();

// Route các trang
switch ($page) {
    case 'home':
        include "src/Views/trangChu.php";
        break;
    case 'monhoc':
        include "src/Views/danhSachMon.php";
        break;
    case 'contact':
        include "src/Views/lienHe.php";
        break;
    case 'upload':
        include "src/Views/dangTaiTaiLieu.php";
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
?>