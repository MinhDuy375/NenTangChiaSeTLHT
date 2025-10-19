<?php
// handle_interaction.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/../../config/ketNoiDB.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Bạn cần đăng nhập để thực hiện hành động này.']);
    exit;
}

// 2. Lấy và kiểm tra dữ liệu đầu vào
$input = json_decode(file_get_contents('php://input'), true);
$postId = $input['postId'] ?? 0;
$type = $input['type'] ?? '';
$userId = $_SESSION['user_id'];

if ($postId <= 0 || empty($type)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ.']);
    exit;
}

$valid_reactions = ['like', 'love', 'care', 'haha', 'wow', 'sad', 'angry'];

try {
    // Bắt đầu một transaction để đảm bảo toàn vẹn dữ liệu
    $pdo->beginTransaction();

    if (in_array($type, $valid_reactions)) {
        // --- XỬ LÝ REACTION (like, love,...) ---

        // 1. Xóa dislike cũ (nếu có) của user này cho bài viết này
        $stmt = $pdo->prepare("DELETE FROM tuong_tac WHERE id_bai_chia_se = ? AND id_nguoi_dung = ? AND loai = 'dislike'");
        $stmt->execute([$postId, $userId]);

        // 2. Thêm hoặc cập nhật reaction mới
        $stmt = $pdo->prepare(
            "INSERT INTO reaction (id_bai_chia_se, id_nguoi_dung, loai_cam_xuc)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE loai_cam_xuc = VALUES(loai_cam_xuc)"
        );
        $stmt->execute([$postId, $userId, $type]);
    } elseif ($type === 'dislike') {
        // --- XỬ LÝ DISLIKE ---

        // 1. Xóa reaction cũ (nếu có)
        $stmt = $pdo->prepare("DELETE FROM reaction WHERE id_bai_chia_se = ? AND id_nguoi_dung = ?");
        $stmt->execute([$postId, $userId]);

        // 2. Thêm dislike vào bảng tuong_tac
        $stmt = $pdo->prepare(
            "INSERT INTO tuong_tac (id_bai_chia_se, id_nguoi_dung, loai)
             VALUES (?, ?, 'dislike')
             ON DUPLICATE KEY UPDATE loai = 'dislike'" // Gần như không cần nhưng để cho chắc
        );
        $stmt->execute([$postId, $userId]);
    } elseif ($type === 'remove_reaction') {
        // --- XỬ LÝ HỦY REACTION ---
        $stmt = $pdo->prepare("DELETE FROM reaction WHERE id_bai_chia_se = ? AND id_nguoi_dung = ?");
        $stmt->execute([$postId, $userId]);
    } elseif ($type === 'remove_dislike') {
        // --- XỬ LÝ HỦY DISLIKE ---
        $stmt = $pdo->prepare("DELETE FROM tuong_tac WHERE id_bai_chia_se = ? AND id_nguoi_dung = ? AND loai = 'dislike'");
        $stmt->execute([$postId, $userId]);
    } else {
        throw new Exception("Loại tương tác không hợp lệ.");
    }

    // Kết thúc transaction
    $pdo->commit();

    // Lấy lại dữ liệu tổng hợp mới nhất để trả về cho client
    // Tổng số reaction
    $stmt = $pdo->prepare("SELECT loai_cam_xuc, COUNT(*) as so_luong FROM reaction WHERE id_bai_chia_se = ? GROUP BY loai_cam_xuc");
    $stmt->execute([$postId]);
    $reaction_details = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $total_reactions = array_sum($reaction_details);

    // Tổng số dislike
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tuong_tac WHERE id_bai_chia_se = ? AND loai = 'dislike'");
    $stmt->execute([$postId]);
    $total_dislikes = $stmt->fetchColumn();

    // Trạng thái của user hiện tại
    $stmt = $pdo->prepare("SELECT loai_cam_xuc FROM reaction WHERE id_bai_chia_se = ? AND id_nguoi_dung = ?");
    $stmt->execute([$postId, $userId]);
    $user_reaction = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT 1 FROM tuong_tac WHERE id_bai_chia_se = ? AND id_nguoi_dung = ? AND loai = 'dislike'");
    $stmt->execute([$postId, $userId]);
    $user_disliked = (bool)$stmt->fetchColumn();


    echo json_encode([
        'success' => true,
        'reactions' => [
            'total' => $total_reactions,
            'details' => $reaction_details
        ],
        'dislikes' => [
            'total' => $total_dislikes
        ],
        'user_status' => [
            'reaction' => $user_reaction,
            'disliked' => $user_disliked
        ]
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Lỗi server: ' . $e->getMessage()]);
}
