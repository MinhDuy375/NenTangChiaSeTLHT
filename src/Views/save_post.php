<?php
session_start();
include __DIR__ . '/../../config/ketNoiDB.php';

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Chưa đăng nhập"]);
    exit;
}

$id_nguoi_dung = $_SESSION['user_id'];
$id_bai_chia_se = intval($_POST['post_id'] ?? 0);

if ($id_bai_chia_se <= 0) {
    echo json_encode(["status" => "error", "message" => "ID không hợp lệ"]);
    exit;
}

// Kiểm tra đã lưu chưa
$sql = "SELECT id FROM thu_vien_ca_nhan WHERE id_nguoi_dung = :user AND id_bai_chia_se = :post";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':user' => $id_nguoi_dung,
    ':post' => $id_bai_chia_se
]);

if ($stmt->fetch()) {
    // Nếu đã lưu → xóa
    $del = $pdo->prepare("DELETE FROM thu_vien_ca_nhan WHERE id_nguoi_dung = :user AND id_bai_chia_se = :post");
    $del->execute([
        ':user' => $id_nguoi_dung,
        ':post' => $id_bai_chia_se
    ]);
    echo json_encode(["status" => "removed", "message" => "Đã xóa khỏi thư viện"]);
} else {
    // Nếu chưa lưu → thêm mới
    $ins = $pdo->prepare("INSERT INTO thu_vien_ca_nhan (id_nguoi_dung, id_bai_chia_se, loai)
                          VALUES (:user, :post, 'tai_lieu')");
    $ins->execute([
        ':user' => $id_nguoi_dung,
        ':post' => $id_bai_chia_se
    ]);
    echo json_encode(["status" => "saved", "message" => "Đã lưu bài viết"]);
}
