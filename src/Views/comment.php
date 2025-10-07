<?php
session_start();
include __DIR__ . '/../../config/ketNoiDB.php';

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
    exit;
}

$id_nguoi_dung = $_SESSION['user_id'];

// ===== Thêm comment mới (AJAX) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $noi_dung = trim($_POST['noi_dung'] ?? '');
    $id_bai_chia_se = (int)($_POST['id_bai_chia_se'] ?? 0);

    if (empty($noi_dung)) {
        echo json_encode(['success' => false, 'message' => 'Nội dung không được để trống']);
        exit;
    }

    if ($id_bai_chia_se <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID bài viết không hợp lệ']);
        exit;
    }

    try {
        // Thêm comment
        $sql = "INSERT INTO binh_luan (id_bai_chia_se, id_nguoi_dung, noi_dung) 
                VALUES (:id_bai, :id_user, :noi_dung)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_bai' => $id_bai_chia_se,
            ':id_user' => $id_nguoi_dung,
            ':noi_dung' => $noi_dung
        ]);

        // Lấy thông tin comment vừa thêm
        $comment_id = $pdo->lastInsertId();
        $sql = "SELECT bl.*, nd.ho_ten 
                FROM binh_luan bl
                JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
                WHERE bl.id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $comment_id]);
        $comment = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'comment' => [
                'id' => $comment['id'],
                'noi_dung' => htmlspecialchars($comment['noi_dung']),
                'ho_ten' => htmlspecialchars($comment['ho_ten']),
                'ngay_tao' => dinh_dang_ngay($comment['ngay_tao']),
                'id_nguoi_dung' => $comment['id_nguoi_dung'],
                'can_delete' => ($comment['id_nguoi_dung'] == $id_nguoi_dung)
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}

// ===== Xóa comment (AJAX) =====
if (isset($_POST['delete_ajax'])) {
    $id_comment = (int)$_POST['id_comment'];

    try {
        // Kiểm tra quyền xóa
        $sql = "SELECT id_nguoi_dung FROM binh_luan WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_comment]);
        $comment = $stmt->fetch();

        if (!$comment) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy comment']);
            exit;
        }

        if ($comment['id_nguoi_dung'] != $id_nguoi_dung) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa comment này']);
            exit;
        }

        // Xóa comment
        $sql = "DELETE FROM binh_luan WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_comment]);

        echo json_encode(['success' => true, 'message' => 'Đã xóa comment']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}

// ===== Lấy danh sách comment (AJAX) =====
if (isset($_GET['get_comments'])) {
    $id_bai = (int)$_GET['id_bai'];

    try {
        $sql = "SELECT bl.id, bl.noi_dung, bl.ngay_tao, bl.id_nguoi_dung, nd.ho_ten
                FROM binh_luan bl
                JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
                WHERE bl.id_bai_chia_se = :id_bai
                ORDER BY bl.ngay_tao DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_bai' => $id_bai]);
        $comments = $stmt->fetchAll();

        $result = [];
        foreach ($comments as $c) {
            $result[] = [
                'id' => $c['id'],
                'noi_dung' => htmlspecialchars($c['noi_dung']),
                'ho_ten' => htmlspecialchars($c['ho_ten']),
                'ngay_tao' => dinh_dang_ngay($c['ngay_tao']),
                'id_nguoi_dung' => $c['id_nguoi_dung'],
                'can_delete' => ($c['id_nguoi_dung'] == $id_nguoi_dung)
            ];
        }

        echo json_encode(['success' => true, 'comments' => $result]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
    exit;
}

// ===== Fallback: Form submit thông thường =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noi_dung = lam_sach_chuoi($_POST['noi_dung'] ?? '');
    $id_bai_chia_se = (int)($_POST['id_bai_chia_se'] ?? 0);

    if (!empty($noi_dung) && $id_bai_chia_se > 0) {
        $sql = "INSERT INTO binh_luan (id_bai_chia_se, id_nguoi_dung, noi_dung) 
                VALUES (:id_bai, :id_user, :noi_dung)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_bai' => $id_bai_chia_se,
            ':id_user' => $id_nguoi_dung,
            ':noi_dung' => $noi_dung
        ]);
    }
    header("Location: index.php?page=chitiettailieu&id=" . $id_bai_chia_se);
    exit;
}

// ===== Xóa comment thông thường =====
if (isset($_GET['delete'])) {
    $id_comment = (int)$_GET['delete'];
    $id_bai = (int)$_GET['id_bai'];

    $sql = "DELETE FROM binh_luan WHERE id = ? AND id_nguoi_dung = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_comment, $id_nguoi_dung]);

    header("Location: index.php?page=chitiettailieu&id=" . $id_bai);
    exit;
}
