<?php
// Lấy tham số ?page=... từ URL, mặc định là "home"
$page = $_GET['page'] ?? 'home';

$title = ucfirst($page); // Đặt tiêu đề động

ob_start();
switch ($page) {
    case 'home':
        include "src/Views/trangChu.php";
        break;
    case 'about':
        include "src/Views/danhSachMon.php";
        break;
    case 'contact':
        include "src/Views/lienHe.php";
        break;
    default:
        echo "<h2>404 - Không tìm thấy trang</h2>";
}
$content = ob_get_clean();

include "layout.php";
