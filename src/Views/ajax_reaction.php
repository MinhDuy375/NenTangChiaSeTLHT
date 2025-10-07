<?php
include __DIR__ . '/../../config/ketNoiDB.php';

session_start();

// Trả về JSON
header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bạn cần đăng nhập']);
    exit;
}

// Kiểm tra dữ liệu
if (!isset($_POST['id_bai']) || !isset($_POST['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Thiếu dữ liệu']);
    exit;
}

$id_bai = intval($_POST['id_bai']);
$type = trim($_POST['type']);
$id_user = $_SESSION['user_id'];

// Validate loại cảm xúc
$valid_types = ['like', 'love', 'care', 'haha', 'wow', 'sad', 'angry'];
if (!in_array($type, $valid_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Loại cảm xúc không hợp lệ']);
    exit;
}

try {
    // Thêm hoặc cập nhật reaction
    $sql = "INSERT INTO reaction (id_bai_chia_se, id_nguoi_dung, loai_cam_xuc)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE loai_cam_xuc = VALUES(loai_cam_xuc)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_bai, $id_user, $type]);

    // Lấy tổng số reaction
    $sql = "SELECT COUNT(*) as tong FROM reaction WHERE id_bai_chia_se = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_bai]);
    $row = $stmt->fetch();

    // Lấy chi tiết từng loại
    $sql = "SELECT loai_cam_xuc, COUNT(*) as so_luong 
            FROM reaction 
            WHERE id_bai_chia_se = ? 
            GROUP BY loai_cam_xuc";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_bai]);
    $chi_tiet = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Trả về kết quả
    echo json_encode([
        'success' => true,
        'tong' => $row['tong'],
        'chi_tiet' => $chi_tiet,
        'user_reaction' => $type
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi database: ' . $e->getMessage()]);
}
