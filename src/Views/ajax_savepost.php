<?php
// src/Views/handle_save_post.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/ketNoiDB.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'error' => 'Bạn cần đăng nhập để thực hiện chức năng này.']);
    exit;
}

// 2. Lấy dữ liệu từ client
$input = json_decode(file_get_contents('php://input'), true);
$postId = $input['postId'] ?? 0;
$userId = $_SESSION['user_id'];

if ($postId <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ.']);
    exit;
}

try {
    // 3. Kiểm tra xem bài viết đã được lưu chưa
    $stmt = $pdo->prepare("SELECT id FROM thu_vien_ca_nhan WHERE id_nguoi_dung = ? AND id_bai_chia_se = ?");
    $stmt->execute([$userId, $postId]);
    $existing = $stmt->fetch();

    $is_saved_now = false;

    if ($existing) {
        // Nếu đã tồn tại -> Xóa (bỏ lưu)
        $stmt = $pdo->prepare("DELETE FROM thu_vien_ca_nhan WHERE id = ?");
        $stmt->execute([$existing['id']]);
        $is_saved_now = false; // Trạng thái mới là chưa lưu
    } else {
        // Nếu chưa tồn tại -> Thêm (lưu lại)
        // Giả sử loại mặc định là 'do_an' hoặc 'tai_lieu', bạn có thể tùy chỉnh
        $stmt = $pdo->prepare("INSERT INTO thu_vien_ca_nhan (id_nguoi_dung, id_bai_chia_se, loai) VALUES (?, ?, 'do_an')");
        $stmt->execute([$userId, $postId]);
        $is_saved_now = true; // Trạng thái mới là đã lưu
    }

    // 4. Trả về kết quả thành công và trạng thái mới
    echo json_encode(['success' => true, 'is_saved' => $is_saved_now]);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'error' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}
