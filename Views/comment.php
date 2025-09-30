<?php
session_start();
include 'ketnoiDB.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noi_dung = lam_sach_chuoi($_POST['noi_dung'] ?? '');
    $id_bai_chia_se = (int)($_POST['id_bai_chia_se'] ?? 0);
    $id_nguoi_dung = $_SESSION['user_id'] ?? 0; // khi login bạn set session này

    if ($noi_dung && $id_bai_chia_se > 0 && $id_nguoi_dung > 0) {
        $sql = "INSERT INTO binh_luan (id_bai_chia_se, id_nguoi_dung, noi_dung) 
                VALUES (:id_bai_chia_se, :id_nguoi_dung, :noi_dung)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_bai_chia_se' => $id_bai_chia_se,
            ':id_nguoi_dung'  => $id_nguoi_dung,
            ':noi_dung'       => $noi_dung
        ]);
    }

    // Quay lại trang bài viết
    header("Location: index.php?page=chitiettailieu&id=" . $id_bai_chia_se);
    exit;
}

// ===== Xoá comment =====
if (isset($_GET['delete'], $_GET['id_bai'])) {
    $id_comment = (int) $_GET['delete'];
    $id_bai_chia_se = (int) $_GET['id_bai'];

    $sql = "DELETE FROM binh_luan WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_comment]);

    header("Location: index.php?page=chitiettailieu&id=" . $id_bai_chia_se);
    exit;
}
?>
