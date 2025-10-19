<?php

$servername = "localhost";
$username = "root";
$password = "duy3725";
$dbname = "chiasetailieudb2";

// $servername = "sql312.infinityfree.com";
// $username = "if0_39838965";
// $password = "k6eZQbnkGMUO6MY";
// $dbname = "if0_39838965_chiasetailieudb";
//tạo các bến chứa thông tin của conections 

// kết nối tới db
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password); //tạo đối tượng pdo với tham số dsn , user, pass
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // đặt 2 thuộc tính hay đi cùng nhau cho pdo
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());  // nếu ko kết nối đc trả lỗi
}

// Hàm tiện ích
function lam_sach_chuoi($chuoi)
{
    return htmlspecialchars(trim($chuoi), ENT_QUOTES, 'UTF-8');
}

function dinh_dang_ngay($ngay)
{
    return date('d/m/Y H:i', strtotime($ngay));
}

function kiem_tra_loai_file($ten_file)
{
    $duoi_file = strtolower(pathinfo($ten_file, PATHINFO_EXTENSION));
    $loai_file_hop_le = array('pdf', 'doc', 'docx');
    return in_array($duoi_file, $loai_file_hop_le);
}

function tao_ten_file_duy_nhat($ten_file_goc)
{
    $thong_tin_file = pathinfo($ten_file_goc);
    $ten_co_ban = $thong_tin_file['filename'];
    $duoi_file = $thong_tin_file['extension'];
    $timestamp = time();
    $chuoi_ngau_nhien = substr(md5(rand()), 0, 6);
    return $ten_co_ban . '_' . $timestamp . '_' . $chuoi_ngau_nhien . '.' . $duoi_file;
}
function lay_tong_reaction($pdo, $id_bai)
{
    $sql = "SELECT COUNT(*) as tong FROM reaction WHERE id_bai_chia_se = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_bai]);
    $row = $stmt->fetch();
    return $row ? $row['tong'] : 0;
}
function lay_chi_tiet_reaction($pdo, $id_bai)
{
    $sql = "SELECT loai_cam_xuc, COUNT(*) as so_luong 
            FROM reaction 
            WHERE id_bai_chia_se = ?
            GROUP BY loai_cam_xuc";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_bai]);
    $rows = $stmt->fetchAll();

    $ketqua = [
        'like' => 0,
        'love' => 0,
        'care' => 0,
        'haha' => 0,
        'wow' => 0,
        'sad' => 0,
        'angry' => 0
    ];
    foreach ($rows as $row) {
        $ketqua[$row['loai_cam_xuc']] = $row['so_luong'];
    }
    return $ketqua;
}
