<?php
$host = 'localhost';
$user = 'root';
$pass = 'thanh2005';
$db   = 'chiasetailieudb';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass); // Dùng biến đúng tên
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}

// Hàm tiện ích
function lam_sach_chuoi($chuoi) {
    return htmlspecialchars(trim($chuoi), ENT_QUOTES, 'UTF-8');
}

function dinh_dang_ngay($ngay) {
    return date('d/m/Y H:i', strtotime($ngay));
}

function kiem_tra_loai_file($ten_file) {
    $duoi_file = strtolower(pathinfo($ten_file, PATHINFO_EXTENSION));
    $loai_file_hop_le = array('pdf', 'doc', 'docx');
    return in_array($duoi_file, $loai_file_hop_le);
}

function tao_ten_file_duy_nhat($ten_file_goc) {
    $thong_tin_file = pathinfo($ten_file_goc);
    $ten_co_ban = $thong_tin_file['filename'];
    $duoi_file = $thong_tin_file['extension'];
    $timestamp = time();
    $chuoi_ngau_nhien = substr(md5(rand()), 0, 6);
    return $ten_co_ban . '_' . $timestamp . '_' . $chuoi_ngau_nhien . '.' . $duoi_file;
}
?>
