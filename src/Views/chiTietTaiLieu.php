<?php
// chiTietTaiLieu.php - Trang hiển thị chi tiết một tài liệu
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy ID tài liệu từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php?page=danhSachMon");
    exit;
}

// Lấy thông tin chi tiết tài liệu
try {
    $sql = "SELECT bcs.*, mh.ten_mon, nd.ho_ten as ten_nguoi_dang
            FROM bai_chia_se bcs
            LEFT JOIN mon_hoc mh ON bcs.id_mon_hoc = mh.id
            LEFT JOIN nguoi_dung nd ON bcs.id_nguoi_dung = nd.id
            WHERE bcs.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $tai_lieu = $stmt->fetch();

    if (!$tai_lieu) {
        header("Location: index.php?page=danhSachMon");
        exit;
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Hàm lấy icon theo loại file
function lay_icon_file($duong_dan_file) {
    $duoi_file = strtolower(pathinfo($duong_dan_file, PATHINFO_EXTENSION));
    switch ($duoi_file) {
        case 'pdf': return '📄';
        case 'doc':
        case 'docx': return '📝';
        default: return '📎';
    }
}

// Hàm tính kích thước file
function tinh_kich_thuoc_file($duong_dan_file) {
    if (file_exists($duong_dan_file)) {
        $kich_thuoc = filesize($duong_dan_file);
        if ($kich_thuoc >= 1048576) {
            return round($kich_thuoc / 1048576, 2) . ' MB';
        } elseif ($kich_thuoc >= 1024) {
            return round($kich_thuoc / 1024, 2) . ' KB';
        } else {
            return $kich_thuoc . ' bytes';
        }
    }
    return 'Không xác định';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Tài Liệu - <?= lam_sach_chuoi($tai_lieu['tieu_de']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f6fa; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .breadcrumb { font-size: 14px; margin-bottom: 15px; }
        .breadcrumb a { text-decoration: none; color: #007bff; }
        .breadcrumb a:hover { text-decoration: underline; }
        h1 { color: #007bff; margin-bottom: 10px; }
        .meta { font-size: 14px; color: #555; margin-bottom: 20px; }
        .tom-tat { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff; border-radius: 4px; }
        .actions { margin-top: 20px; display: flex; gap: 10px; }
        .btn { padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        iframe { width: 100%; height: 500px; border: 1px solid #ddd; border-radius: 6px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php?page=danhSachMon">🏠 Danh sách môn học</a> /
            <a href="index.php?page=taiLieuMon&id_mon_hoc=<?= $tai_lieu['id_mon_hoc'] ?>">
                <?= lam_sach_chuoi($tai_lieu['ten_mon']) ?>
            </a> /
            <?= lam_sach_chuoi($tai_lieu['tieu_de']) ?>
        </div>

        <h1><?= lay_icon_file($tai_lieu['file_upload']) ?> <?= lam_sach_chuoi($tai_lieu['tieu_de']) ?></h1>

        <div class="meta">
            📅 <?= dinh_dang_ngay($tai_lieu['ngay_tao']) ?> 
            | 👤 <?= !empty($tai_lieu['ten_nguoi_dang']) ? lam_sach_chuoi($tai_lieu['ten_nguoi_dang']) : "Ẩn danh" ?>
            | 💾 <?= tinh_kich_thuoc_file($tai_lieu['file_upload']) ?>
        </div>

        <?php if (!empty($tai_lieu['tom_tat'])): ?>
            <div class="tom-tat">
                <strong>Tóm tắt:</strong><br>
                <?= nl2br(lam_sach_chuoi($tai_lieu['tom_tat'])) ?>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="<?= $tai_lieu['file_upload'] ?>" class="btn btn-success" download target="_blank">📥 Tải xuống</a>
        </div>

        <?php
        $ext = strtolower(pathinfo($tai_lieu['file_upload'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf'])): ?>
            <iframe src="<?= $tai_lieu['file_upload'] ?>"></iframe>
        <?php elseif (in_array($ext, ['doc', 'docx'])): ?>
            <p><em>Không thể xem trực tiếp file Word. Vui lòng tải xuống để xem.</em></p>
        <?php endif; ?>
    </div>
</body>
</html>
