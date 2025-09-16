<?php

$servername = "localhost";
$username = "root";
$password = "thanh2005";
$dbname = "chiasetailieudb";
//tạo các bến chứa thông tin của conections 

// kết nối tới db
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password); //tạo đối tượng pdo với tham số dsn , user, pass
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // đặt 2 thuộc tính hay đi cùng nhau cho pdo
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());  // nếu ko kết nối đc trả lỗi
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