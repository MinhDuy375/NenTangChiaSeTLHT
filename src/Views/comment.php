<?php
session_start();
require __DIR__ . '/../../config/ketNoiDB.php';

// ====== KIỂM TRA ĐĂNG NHẬP ======
if (!isset($_SESSION['user_id'])) {
    if (isset($_POST['ajax']) || isset($_POST['delete_ajax']) || isset($_GET['get_comments'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
        exit;
    } else {
        header('Location: ../../index.php?page=dangnhap');
        exit;
    }
}
$id_nguoi_dung = $_SESSION['user_id'];

// -----------------------------------------------------------
// ====== 1. XÓA COMMENT BẰNG AJAX ======
if (isset($_POST['delete_ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id_comment = (int)$_POST['id_comment'];

    try {
        $stmt = $pdo->prepare("SELECT id_nguoi_dung FROM binh_luan WHERE id = ?");
        $stmt->execute([$id_comment]);
        $comment = $stmt->fetch();

        if (!$comment) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy bình luận']);
            exit;
        }
        if ($comment['id_nguoi_dung'] != $id_nguoi_dung) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bình luận này']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM binh_luan WHERE id = ?");
        $stmt->execute([$id_comment]);

        echo json_encode(['success' => true, 'message' => 'Đã xóa bình luận']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -----------------------------------------------------------
// ====== 2. XÓA COMMENT BẰNG LINK (GET) ======
if (isset($_GET['delete'])) {
    $id_comment = (int)$_GET['delete'];
    $id_bai = (int)($_GET['id_bai'] ?? 0);

    $stmt = $pdo->prepare("DELETE FROM binh_luan WHERE id = ? AND id_nguoi_dung = ?");
    $stmt->execute([$id_comment, $id_nguoi_dung]);

    header("Location: ../../index.php?page=chitiettailieu&id=" . $id_bai);
    exit;
}

// -----------------------------------------------------------
// ====== 3. THÊM COMMENT MỚI (AJAX) ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    $noi_dung = trim($_POST['noi_dung'] ?? '');
    $id_bai_chia_se = (int)($_POST['id_bai_chia_se'] ?? 0);

    if ($noi_dung === '' || $id_bai_chia_se <= 0) {
        echo json_encode(['success' => false, 'message' => 'Nội dung hoặc ID không hợp lệ']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO binh_luan (id_bai_chia_se, id_nguoi_dung, noi_dung) VALUES (?, ?, ?)");
        $stmt->execute([$id_bai_chia_se, $id_nguoi_dung, $noi_dung]);

        $id_new = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT bl.*, nd.ho_ten FROM binh_luan bl JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id WHERE bl.id = ?");
        $stmt->execute([$id_new]);
        $c = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'comment' => [
                'id' => $c['id'],
                'noi_dung' => htmlspecialchars($c['noi_dung']),
                'ho_ten' => htmlspecialchars($c['ho_ten']),
                'ngay_tao' => dinh_dang_ngay($c['ngay_tao']),
                'id_nguoi_dung' => $c['id_nguoi_dung'],
                'can_delete' => true
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// -----------------------------------------------------------
// ====== 4. LẤY DANH SÁCH COMMENT (AJAX) ======
if (isset($_GET['get_comments'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id_bai = (int)$_GET['id_bai'];

    $stmt = $pdo->prepare("SELECT bl.*, nd.ho_ten FROM binh_luan bl JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id WHERE bl.id_bai_chia_se = ? ORDER BY bl.ngay_tao DESC");
    $stmt->execute([$id_bai]);
    $rows = $stmt->fetchAll();

    $comments = [];
    foreach ($rows as $c) {
        $comments[] = [
            'id' => $c['id'],
            'noi_dung' => htmlspecialchars($c['noi_dung']),
            'ho_ten' => htmlspecialchars($c['ho_ten']),
            'ngay_tao' => dinh_dang_ngay($c['ngay_tao']),
            'id_nguoi_dung' => $c['id_nguoi_dung'],
            'can_delete' => ($c['id_nguoi_dung'] == $id_nguoi_dung)
        ];
    }

    echo json_encode(['success' => true, 'comments' => $comments]);
    exit;
}

// -----------------------------------------------------------
// ====== 5. SUBMIT COMMENT BẰNG FORM THƯỜNG ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noi_dung = trim($_POST['noi_dung'] ?? '');
    $id_bai = (int)($_POST['id_bai_chia_se'] ?? 0);

    if ($noi_dung !== '' && $id_bai > 0) {
        $stmt = $pdo->prepare("INSERT INTO binh_luan (id_bai_chia_se, id_nguoi_dung, noi_dung) VALUES (?, ?, ?)");
        $stmt->execute([$id_bai, $id_nguoi_dung, $noi_dung]);
    }

    header("Location: ../../index.php?page=chitiettailieu&id=$id_bai");
    exit;
}
?>
