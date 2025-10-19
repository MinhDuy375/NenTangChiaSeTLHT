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
        include "src/Views/TrangChu.php";
        break;
    case 'monhoc':
        include "src/Views/danhSachMon.php";
        break;
    case 'tailieumon':
        include "src/Views/taiLieuMon.php";
        break;
    case 'chitiettailieu':
        include "src/Views/chiTietTaiLieu.php";
        break;
    case 'contact':
        include "src/Views/lienHe.php";
        break;
    case 'upload':
        include "src/Views/dangTaiTaiLieu.php";
        break;
    case 'source':
        include "src/Views/thuVienNguon.php";
        break;
    case 'source_upload':
        include "src/Views/dangTaiNguon.php";
        break;
    case 'source_detail':
        include "src/Views/chiTietNguon.php";
        break;
    case 'login':
        include "src/Views/login.php";
        break;
    case 'logout':
        include "src/Views/logout.php";
        break;
    case 'register':
        include "src/Views/register.php";
        break;
    case 'reset_password':
        include "src/Views/reset_password.php";
        break;
    case 'verify_forgot':
        include "src/Views/verify_forgot.php";
        break;
    case 'verify':
        include "src/Views/verify.php";
        break;
    case 'forgotpass':
        include "src/Views/forgotpass.php";
        break;
    case 'them_mon':
        include "src/Views/dangTaiMonHoc.php";
        break;
    case 'them_danh_muc':
        include "src/Views/dangTaiDanhMuc.php";
        break;
    case 'thu_vien':
        include "src/Views/ThuVienCaNhan.php";
        break;
    case 'thu_vien_personal':
        include "src/Views/ThuVienCaNhan.php";
        break;
    case 'admin':
        include "src/Views/admin_config/index.php";
        break;
    case 'adminMon':
        include "src/Views/admin_config/mon_hoc.php";
        break;
    case 'adminDanhmuc':
        include "src/Views/admin_config/adminDanhmuc.php";
        break;
    case 'adminBai':
        include "src/Views/admin_config/adminBai.php";
        break;
    case 'adminUser':
        include "src/Views/admin_config/nguoi_dung.php";
        break;
    case 'adminDocs':
        include "src/Views/admin_config/tai_lieu_mon.php";
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
include "src/Views/layout.php";
