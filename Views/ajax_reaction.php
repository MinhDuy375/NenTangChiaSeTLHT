<?php
include "ketNoiDB.php";


session_start();




if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Bạn cần đăng nhập";
    exit;
}

$id_bai = intval($_POST['id_bai']);
$type = $_POST['type'];
$id_user = $_SESSION['user_id'];

// Thêm hoặc cập nhật reaction
$sql = "INSERT INTO reaction (id_bai_chia_se, id_nguoi_dung, loai_cam_xuc)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE loai_cam_xuc = VALUES(loai_cam_xuc)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_bai, $id_user, $type]);

// Trả về tổng số reaction để update lại trên giao diện
$sql = "SELECT COUNT(*) as tong FROM reaction WHERE id_bai_chia_se = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_bai]);
$row = $stmt->fetch();

echo $row['tong'];
