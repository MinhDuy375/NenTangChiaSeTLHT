<?php
// taiLieuMon.php - Trang hiển thị tài liệu theo môn học
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy ID môn học từ URL
$id_mon_hoc = isset($_GET['id_mon_hoc']) ? (int)$_GET['id_mon_hoc'] : 0;

if ($id_mon_hoc <= 0) {
    header('Location: index.php?page=danhSachMon');
    exit;
}

// Lấy thông tin môn học
try {
    $sql_mon_hoc = "SELECT * FROM mon_hoc WHERE id = :id_mon_hoc";
    $stmt_mon_hoc = $pdo->prepare($sql_mon_hoc);
    $stmt_mon_hoc->execute([':id_mon_hoc' => $id_mon_hoc]);
    $thong_tin_mon_hoc = $stmt_mon_hoc->fetch();
    
    if (!$thong_tin_mon_hoc) {
        header('Location: index.php?page=danhSachMon');
        exit;
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Lấy danh sách tài liệu của môn học
try {
    $sql_tai_lieu = "SELECT bcs.*, nd.ho_ten as ten_nguoi_dang 
                     FROM bai_chia_se bcs 
                     LEFT JOIN nguoi_dung nd ON bcs.id_nguoi_dung = nd.id
                     WHERE bcs.id_mon_hoc = :id_mon_hoc AND bcs.loai = 'tai_lieu'
                     ORDER BY bcs.ngay_tao DESC";
    
    $stmt_tai_lieu = $pdo->prepare($sql_tai_lieu);
    $stmt_tai_lieu->execute([':id_mon_hoc' => $id_mon_hoc]);
    $danh_sach_tai_lieu = $stmt_tai_lieu->fetchAll();
} catch (PDOException $e) {
    $danh_sach_tai_lieu = [];
    $loi = "Không thể tải danh sách tài liệu: " . $e->getMessage();
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
    <title>Tài Liệu - <?= lam_sach_chuoi($thong_tin_mon_hoc['ten_mon']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f6fa; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .header h1 { margin-bottom: 10px; color: #007bff; }
        .breadcrumb { margin-bottom: 20px; font-size: 14px; }
        .breadcrumb a { text-decoration: none; color: #007bff; }
        .breadcrumb a:hover { text-decoration: underline; }
        .tai-lieu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .tai-lieu-card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; transition: 0.3s; }
        .tai-lieu-card:hover { transform: translateY(-3px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .tai-lieu-header { background: #007bff; color: white; padding: 15px; }
        .tai-lieu-title { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .tai-lieu-meta { font-size: 12px; opacity: 0.9; }
        .tai-lieu-body { padding: 15px; font-size: 14px; color: #333; }
        .tai-lieu-actions { margin-top: 10px; display: flex; gap: 10px; }
        .btn { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 500; transition: 0.3s; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .empty-state { text-align: center; padding: 50px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php?page=danhSachMon">🏠 Danh sách môn học</a> / <?= lam_sach_chuoi($thong_tin_mon_hoc['ten_mon']) ?>
        </div>
        <div class="header">
            <h1>📚 <?= lam_sach_chuoi($thong_tin_mon_hoc['ten_mon']) ?></h1>
            <p><?= !empty($thong_tin_mon_hoc['mo_ta']) ? lam_sach_chuoi($thong_tin_mon_hoc['mo_ta']) : "Khám phá các tài liệu học tập chất lượng" ?></p>
        </div>

        <?php if (isset($loi)): ?>
            <p style="color: red;"><?= $loi ?></p>
        <?php endif; ?>

        <?php if (empty($danh_sach_tai_lieu)): ?>
            <div class="empty-state">
                <h3>📭 Chưa có tài liệu nào</h3>
                <p>Hãy là người đầu tiên upload tài liệu cho môn học này.</p>
                <a href="index.php?page=dangTaiTaiLieu" class="btn btn-primary">➕ Upload Tài Liệu</a>
            </div>
        <?php else: ?>
            <div class="tai-lieu-grid">
                <?php foreach ($danh_sach_tai_lieu as $tai_lieu): ?>
                    <div class="tai-lieu-card">
                        <div class="tai-lieu-header">
                            <div class="tai-lieu-title"><?= lam_sach_chuoi($tai_lieu['tieu_de']) ?></div>
                            <div class="tai-lieu-meta">
                                📅 <?= dinh_dang_ngay($tai_lieu['ngay_tao']) ?>
                                <?php if (!empty($tai_lieu['ten_nguoi_dang'])): ?>
                                    | 👤 <?= lam_sach_chuoi($tai_lieu['ten_nguoi_dang']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="tai-lieu-body">
                            <?php if (!empty($tai_lieu['tom_tat'])): ?>
                                <p><?= lam_sach_chuoi($tai_lieu['tom_tat']) ?></p>
                            <?php endif; ?>
                            <div class="tai-lieu-actions">
                                <a href="index.php?page=chiTietTaiLieu&id=<?= $tai_lieu['id'] ?>" class="btn btn-primary">👁️ Xem</a>
                                <a href="<?= $tai_lieu['file_upload'] ?>" class="btn btn-success" download target="_blank">📥 Tải</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
