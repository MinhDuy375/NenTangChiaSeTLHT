<?php
// baivietCaNhan.php - Trang bài viết cá nhân
include __DIR__ . '/../../config/ketNoiDB.php';
include __DIR__ . '/checkLogin.php';

// Kiểm tra đăng nhập (bắt buộc)
kiem_tra_dang_nhap(true);

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$id_nguoi_dung = $_SESSION['user_id'];
$tab_active = $_GET['tab'] ?? 'saved';

// Xử lý AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    // Xóa mọi output trước đó
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    // Bỏ yêu thích tài liệu
    if (isset($_POST['bo_yeu_thich'])) {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit;
        }

        try {
            $sql = "DELETE FROM thu_vien_ca_nhan WHERE id_bai_chia_se = ? AND id_nguoi_dung = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id, $id_nguoi_dung]);

            echo json_encode(['success' => true, 'message' => 'Đã bỏ yêu thích!']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi database']);
            exit;
        }
    }

    // Chỉnh sửa bài viết
    if (isset($_POST['sua_bai_viet'])) {
        $id = (int)($_POST['id'] ?? 0);
        $tieu_de = trim($_POST['tieu_de'] ?? '');
        $mo_ta = trim($_POST['mo_ta'] ?? '');

        if (!$id || !$tieu_de) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
            exit;
        }

        try {
            $sql = "UPDATE bai_chia_se SET tieu_de = ?, mo_ta = ?, ngay_cap_nhat = NOW() 
                    WHERE id = ? AND id_nguoi_dung = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tieu_de, $mo_ta, $id, $id_nguoi_dung]);

            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
            exit;
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật bài viết']);
            exit;
        }
    }

    // Lấy thông tin bài viết
    if (isset($_POST['lay_bai_viet'])) {
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit;
        }

        try {
            $sql = "SELECT id, tieu_de, mo_ta FROM bai_chia_se WHERE id = ? AND id_nguoi_dung = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$id, $id_nguoi_dung]);

            if (!$result) {
                echo json_encode(['success' => false, 'message' => 'Lỗi execute query']);
                exit;
            }

            $bai = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($bai) {
                echo json_encode(['success' => true, 'data' => $bai]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài viết']);
            }
            exit;
        } catch (PDOException $e) {
            error_log("Lay bai viet error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
            exit;
        }
    }

    // Xóa bài viết
    if (isset($_POST['xoa_bai_viet'])) {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit;
        }

        try {
            // Lấy thông tin file
            $sql = "SELECT file_upload FROM bai_chia_se WHERE id = ? AND id_nguoi_dung = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id, $id_nguoi_dung]);
            $bai = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$bai) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bài này']);
                exit;
            }

            // Xóa file nếu có
            if (!empty($bai['file_upload']) && file_exists($bai['file_upload'])) {
                unlink($bai['file_upload']);
            }

            // Xóa các dữ liệu liên quan
            $pdo->prepare("DELETE FROM binh_luan WHERE id_bai_chia_se = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM reaction WHERE id_bai_chia_se = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM thu_vien_ca_nhan WHERE id_bai_chia_se = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM tuong_tac WHERE id_bai_chia_se = ?")->execute([$id]);

            // Xóa bài viết
            $sql = "DELETE FROM bai_chia_se WHERE id = ? AND id_nguoi_dung = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id, $id_nguoi_dung]);

            echo json_encode(['success' => true, 'message' => 'Xóa thành công!']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi database']);
            exit;
        }
    }
}

// Lấy danh sách bài viết của người dùng
try {
    $sql = "SELECT bcs.*, mh.ten_mon, nd.ho_ten as ten_nguoi_dang
            FROM bai_chia_se bcs
            LEFT JOIN mon_hoc mh ON bcs.id_mon_hoc = mh.id
            LEFT JOIN nguoi_dung nd ON bcs.id_nguoi_dung = nd.id
            WHERE bcs.id_nguoi_dung = :id_nguoi_dung
            ORDER BY bcs.ngay_tao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_nguoi_dung' => $id_nguoi_dung]);
    $bai_viet = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $bai_viet = [];
    $loi = "Không thể tải danh sách: " . $e->getMessage();
}

// Lấy danh sách tài liệu đã lưu
try {
    $sql_saved = "SELECT bcs.*, mh.ten_mon, nd.ho_ten as ten_nguoi_dang, tv.ngay_tao as ngay_luu
            FROM thu_vien_ca_nhan tv
            JOIN bai_chia_se bcs ON tv.id_bai_chia_se = bcs.id
            LEFT JOIN mon_hoc mh ON bcs.id_mon_hoc = mh.id
            LEFT JOIN nguoi_dung nd ON bcs.id_nguoi_dung = nd.id
            WHERE tv.id_nguoi_dung = :id_nguoi_dung
            ORDER BY tv.ngay_tao DESC";

    $stmt_saved = $pdo->prepare($sql_saved);
    $stmt_saved->execute([':id_nguoi_dung' => $id_nguoi_dung]);
    $tai_lieu_da_luu = $stmt_saved->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tai_lieu_da_luu = [];
}

// Hàm lấy icon theo loại file
function lay_icon_file($duong_dan_file)
{
    if (empty($duong_dan_file)) return '📁';
    $duoi_file = strtolower(pathinfo($duong_dan_file, PATHINFO_EXTENSION));
    switch ($duoi_file) {
        case 'pdf':
            return '📄';
        case 'doc':
        case 'docx':
            return '📋';
        default:
            return '📎';
    }
}

// Hàm tính kích thước file
function tinh_kich_thuoc_file($duong_dan_file)
{
    if (file_exists($duong_dan_file)) {
        $kich_thuoc = filesize($duong_dan_file);
        if ($kich_thuoc >= 1048576) {
            return round($kich_thuoc / 1048576, 2) . ' MB';
        } elseif ($kich_thuoc >= 1024) {
            return round($kich_thuoc / 1024, 2) . ' KB';
        }
        return $kich_thuoc . ' bytes';
    }
    return 'Không xác định';
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài viết cá nhân - Sharedy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            color: white;
            padding: 30px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e1e5e9;
        }

        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #6c757d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;

        }

        .tab-btn.active {
            color: #007bff;
            border-bottom-color: #007bff;
        }

        .tab-btn:hover {
            color: #007bff;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }

        .search-box {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 25px;
            font-size: 16px;
            margin-bottom: 25px;
            transition: all 0.3s;
        }

        .search-box:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .filter-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e1e5e9;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .filter-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .bai-viet-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .bai-viet-card {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .bai-viet-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #007bff;
        }

        .bai-viet-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 20px;
        }

        .file-icon {
            font-size: 2em;
            margin-bottom: 10px;
            display: block;
        }

        .bai-viet-title {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .bai-viet-meta {
            font-size: 0.85em;
            opacity: 0.9;
        }

        .bai-viet-body {
            padding: 20px;
        }

        .bai-viet-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 0.9em;
            color: #6c757d;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 5px;
            word-break: break-word;
        }

        .bai-viet-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-warning:hover {
            background: #ffb300;
            transform: translateY(-1px);
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            margin-bottom: 20px;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #6c757d;
        }

        .empty-state h3 {
            font-size: 1.8em;
            margin-bottom: 15px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-close {
            background: #6c757d;
            color: white;
        }

        .btn-close:hover {
            background: #5a6268;
        }

        .btn-save {
            background: #28a745;
            color: white;
        }

        .btn-save:hover {
            background: #218838;
        }

        @media (max-width: 768px) {
            .bai-viet-grid {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="content">
            <a href="index.php?page=monhoc" class="btn btn-back">← Quay lại danh sách môn học</a>

            <div id="alertMessage" class="alert"></div>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn <?php echo $tab_active === 'saved' ? 'active' : ''; ?>"
                    onclick="switchTab('saved')">
                    ❤️ Thư Viện Yêu Thích (<?php echo count($tai_lieu_da_luu); ?>)
                </button>
                <button class="tab-btn <?php echo $tab_active === 'mypost' ? 'active' : ''; ?>"
                    onclick="switchTab('mypost')">
                    ✏️ Bài Viết Của Tôi (<?php echo count($bai_viet); ?>)
                </button>
            </div>

            <!-- Tab Thư Viện Yêu Thích -->
            <div id="saved-tab" class="tab-content <?php echo $tab_active === 'saved' ? 'active' : ''; ?>">
                <!-- <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($tai_lieu_da_luu); ?></div>
                        <div class="stat-label">Tài liệu đã lưu</div>
                    </div>
                </div> -->

                <?php if (empty($tai_lieu_da_luu)): ?>
                    <div class="empty-state">
                        <h3>🔭 Chưa có tài liệu nào</h3>
                        <p>Bạn chưa lưu tài liệu nào. Hãy khám phá và lưu các tài liệu yêu thích!</p>
                        <a href="index.php?page=monhoc" class="btn btn-primary" style="margin-top: 20px;">
                            📖 Khám phá tài liệu
                        </a>
                    </div>
                <?php else: ?>
                    <input type="text" class="search-box" id="search-saved"
                        placeholder="🔍 Tìm kiếm trong thư viện...">

                    <div class="filter-group">
                        <button class="filter-btn active" onclick="filterLoaiSaved(this, 'all')">
                            Tất Cả
                        </button>
                        <button class="filter-btn" onclick="filterLoaiSaved(this, 'tai_lieu')">
                            📄 Tài Liệu
                        </button>
                        <button class="filter-btn" onclick="filterLoaiSaved(this, 'bai_viet')">
                            ✏️ Bài Viết
                        </button>
                    </div>

                    <div class="bai-viet-grid" id="saved-container">
                        <?php foreach ($tai_lieu_da_luu as $tai_lieu): ?>
                            <!-- Tìm đoạn này trong foreach $tai_lieu_da_luu -->
                            <div class="bai-viet-card"
                                data-title="<?php echo strtolower(htmlspecialchars($tai_lieu['tieu_de'] ?? '')); ?>"
                                data-loai="<?php echo $tai_lieu['loai']; ?>"
                                data-id="<?php echo $tai_lieu['id']; ?>">
                                <div class="bai-viet-header">
                                    <span class="file-icon">
                                        <?php echo ($tai_lieu['loai'] === 'tai_lieu') ? lay_icon_file($tai_lieu['file_upload']) : '✏️'; ?>
                                    </span>
                                    <div class="bai-viet-title"><?php echo htmlspecialchars($tai_lieu['tieu_de']); ?></div>
                                    <div class="bai-viet-meta">
                                        📅 <?php echo date('d/m/Y', strtotime($tai_lieu['ngay_tao'])); ?>
                                        | 🏷️ <?php echo ($tai_lieu['loai'] === 'tai_lieu') ? 'Tài Liệu' : 'Bài Viết'; ?>
                                    </div>
                                </div>

                                <div class="bai-viet-body">
                                    <?php if (!empty($tai_lieu['mo_ta'])): ?>
                                        <div style="color: #6c757d; margin-bottom: 15px; line-height: 1.6;">
                                            <?php echo htmlspecialchars(substr($tai_lieu['mo_ta'], 0, 150)); ?>...
                                        </div>
                                    <?php endif; ?>

                                    <div class="bai-viet-info">
                                        <div class="info-item">
                                            📖 <?php echo htmlspecialchars($tai_lieu['ten_mon'] ?? 'Chưa phân loại'); ?>
                                        </div>
                                        <div class="info-item">
                                            💾 <?php echo tinh_kich_thuoc_file($tai_lieu['file_upload']); ?>
                                        </div>
                                    </div>

                                    <div class="bai-viet-actions">
                                        <?php if ($tai_lieu['loai'] === 'bai_viet'): ?>
                                            <a href="index.php?page=source_detail&id=<?php echo $tai_lieu['id']; ?>"
                                                class="btn btn-primary">
                                                👁️ Xem
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($tai_lieu['loai'] === 'tai_lieu'): ?>
                                            <a href="index.php?page=chitiettailieu&id=<?php echo $tai_lieu['id']; ?>"
                                                class="btn btn-primary">
                                                👁️ Xem
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo $tai_lieu['file_upload']; ?>"
                                            class="btn btn-primary" download target="_blank">
                                            📥 Tải Xuống
                                        </a>
                                        <button class="btn btn-danger"
                                            onclick="boYeuThich(<?php echo $tai_lieu['id']; ?>, this)">
                                            💔 Bỏ Yêu Thích
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab Bài Viết Của Tôi -->
            <div id="mypost-tab" class="tab-content <?php echo $tab_active === 'mypost' ? 'active' : ''; ?>">
                <!-- <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($bai_viet); ?></div>
                        <div class="stat-label">Bài viết của bạn</div>
                    </div>
                </div> -->

                <?php if (empty($bai_viet)): ?>
                    <div class="empty-state">
                        <h3>✏️ Chưa có bài viết nào</h3>
                        <p>Bạn chưa đăng bài viết nào. Hãy chia sẻ kiến thức của bạn!</p>
                        <a href="index.php?page=upload" class="btn btn-primary" style="margin-top: 20px;">
                            ➕ Đăng Bài Viết
                        </a>
                    </div>
                <?php else: ?>
                    <input type="text" class="search-box" id="search-mypost"
                        placeholder="🔍 Tìm kiếm bài viết...">

                    <div class="filter-group">
                        <button class="filter-btn active" onclick="filterLoai(this, 'all')">
                            Tất Cả
                        </button>
                        <button class="filter-btn" onclick="filterLoai(this, 'tai_lieu')">
                            📄 Tài Liệu
                        </button>
                        <button class="filter-btn" onclick="filterLoai(this, 'bai_viet')">
                            ✏️ Bài Viết
                        </button>
                    </div>

                    <div class="bai-viet-grid" id="mypost-container">
                        <?php foreach ($bai_viet as $bv): ?>
                            <div class="bai-viet-card"
                                data-title="<?php echo strtolower(htmlspecialchars($bv['tieu_de'] ?? '')); ?>"
                                data-loai="<?php echo $bv['loai']; ?>"
                                data-id="<?php echo $bv['id']; ?>">
                                <div class="bai-viet-header">
                                    <span class="file-icon">
                                        <?php echo ($bv['loai'] === 'tai_lieu') ? lay_icon_file($bv['file_upload']) : '✏️'; ?>
                                    </span>
                                    <div class="bai-viet-title"><?php echo htmlspecialchars($bv['tieu_de']); ?></div>
                                    <div class="bai-viet-meta">
                                        📅 <?php echo date('d/m/Y', strtotime($bv['ngay_tao'])); ?>
                                        | 🏷️ <?php echo ($bv['loai'] === 'tai_lieu') ? 'Tài Liệu' : 'Bài Viết'; ?>
                                    </div>
                                </div>

                                <div class="bai-viet-body">
                                    <?php if (!empty($bv['mo_ta'])): ?>
                                        <div style="color: #6c757d; margin-bottom: 15px; line-height: 1.6;">
                                            <?php echo htmlspecialchars(substr($bv['mo_ta'], 0, 150)); ?>...
                                        </div>
                                    <?php endif; ?>

                                    <div class="bai-viet-info">
                                        <div class="info-item">
                                            📖 <?php echo htmlspecialchars($bv['ten_mon'] ?? 'Chưa phân loại'); ?>
                                        </div>
                                        <?php if ($bv['loai'] === 'tai_lieu' && !empty($bv['file_upload'])): ?>
                                            <div class="info-item">
                                                💾 <?php echo tinh_kich_thuoc_file($bv['file_upload']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="bai-viet-actions">
                                        <?php if ($bv['loai'] === 'bai_viet'): ?>
                                            <a href="index.php?page=source_detail&id=<?php echo $bv['id']; ?>"
                                                class="btn btn-primary">
                                                👁️ Xem
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($bv['loai'] === 'tai_lieu'): ?>
                                            <a href="index.php?page=chitiettailieu&id=<?php echo $bv['id']; ?>"
                                                class="btn btn-primary">
                                                👁️ Xem
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn btn-warning"
                                            onclick="moModalSua(<?php echo $bv['id']; ?>)">
                                            ✏️ Sửa
                                        </button>
                                        <button class="btn btn-danger"
                                            onclick="xoaBaiViet(<?php echo $bv['id']; ?>)">
                                            🗑️ Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Sửa -->
    <div id="modalSua" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                ✏️ Chỉnh Sửa Bài Viết
            </div>
            <form id="formSuaBaiViet">
                <input type="hidden" name="id" id="sua_id">

                <div class="modal-body">
                    <div class="form-group">
                        <label>Tiêu Đề: <span style="color: red;">*</span></label>
                        <input type="text" name="tieu_de" id="sua_tieu_de" required>
                    </div>

                    <div class="form-group">
                        <label>Mô Tả / Nội Dung:</label>
                        <textarea name="mo_ta" id="sua_mo_ta"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="dongModal()" class="btn btn-close">Hủy</button>
                    <button type="submit" class="btn btn-save">💾 Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Hiện thông báo
        function hienThongBao(message, type = 'success') {
            const alert = document.getElementById('alertMessage');
            alert.className = `alert alert-${type} show`;
            alert.textContent = message;

            setTimeout(() => {
                alert.classList.remove('show');
            }, 3000);
        }

        // Chuyển tab
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('active');
            });

            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');

            window.history.pushState(null, '', 'index.php?page=thu_vien_personal&tab=' + tabName);
        }

        // Tìm kiếm thư viện
        // Tìm và THAY THẾ đoạn này:
        const searchSaved = document.getElementById('search-saved');
        if (searchSaved) {
            searchSaved.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const activeFilter = document.querySelector('#saved-tab .filter-btn.active');
                const loaiFilter = activeFilter ? (activeFilter.dataset.loai || 'all') : 'all';
                const cards = document.querySelectorAll('#saved-container .bai-viet-card');

                cards.forEach(card => {
                    const title = card.getAttribute('data-title');
                    const loai = card.getAttribute('data-loai');
                    const matchSearch = title.includes(searchTerm);
                    const matchFilter = loaiFilter === 'all' || loai === loaiFilter;
                    card.style.display = (matchSearch && matchFilter) ? 'block' : 'none';
                });
            });
        }

        // Tìm kiếm bài viết
        const searchMypost = document.getElementById('search-mypost');
        if (searchMypost) {
            searchMypost.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const cards = document.querySelectorAll('#mypost-container .bai-viet-card');
                const activeFilter = document.querySelector('.filter-btn.active');
                const loaiFilter = activeFilter ? (activeFilter.dataset.loai || 'all') : 'all';

                cards.forEach(card => {
                    const title = card.getAttribute('data-title');
                    const loai = card.getAttribute('data-loai');
                    const matchSearch = title.includes(searchTerm);
                    const matchFilter = loaiFilter === 'all' || loai === loaiFilter;
                    card.style.display = (matchSearch && matchFilter) ? 'block' : 'none';
                });
            });
        }

        // Lọc theo loại
        function filterLoai(btn, loai) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            btn.dataset.loai = loai;

            const searchBox = document.getElementById('search-mypost');
            const searchTerm = searchBox ? searchBox.value.toLowerCase().trim() : '';
            const cards = document.querySelectorAll('#mypost-container .bai-viet-card');

            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                const cardLoai = card.getAttribute('data-loai');
                const matchSearch = title.includes(searchTerm);
                const matchFilter = loai === 'all' || cardLoai === loai;
                card.style.display = (matchSearch && matchFilter) ? 'block' : 'none';
            });
        }

        // Bỏ yêu thích
        function boYeuThich(id, btn) {
            if (!confirm('Bạn có chắc muốn bỏ yêu thích tài liệu này?')) {
                return;
            }

            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('bo_yeu_thich', '1');
            formData.append('id', id);

            btn.disabled = true;
            btn.innerHTML = '⏳ Đang xóa...';

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Tìm và xóa card dựa trên data-id
                        const card = document.querySelector(`#saved-container .bai-viet-card[data-id="${id}"]`);
                        if (card) {
                            card.remove();
                        }

                        hienThongBao('✅ ' + data.message, 'success');

                        // Cập nhật số lượng
                        const remaining = document.querySelectorAll('#saved-container .bai-viet-card').length;
                        const statNumber = document.querySelector('#saved-tab .stat-number');
                        if (statNumber) {
                            statNumber.textContent = remaining;
                        }

                        // Reload nếu hết tài liệu
                        if (remaining === 0) {
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    } else {
                        hienThongBao('❌ ' + (data.message || 'Có lỗi xảy ra!'), 'danger');
                        btn.disabled = false;
                        btn.innerHTML = '💔 Bỏ Yêu Thích';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    hienThongBao('⚠️ Có lỗi xảy ra!', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = '💔 Bỏ Yêu Thích';
                });
        }

        // Mở modal sửa
        function moModalSua(id) {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('lay_bai_viet', '1');
            formData.append('id', id);

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('sua_id').value = data.data.id;
                        document.getElementById('sua_tieu_de').value = data.data.tieu_de;
                        document.getElementById('sua_mo_ta').value = data.data.mo_ta || '';
                        document.getElementById('modalSua').classList.add('show');
                    } else {
                        hienThongBao('❌ ' + (data.message || 'Không thể tải bài viết!'), 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    hienThongBao('⚠️ Có lỗi xảy ra!', 'danger');
                });
        }

        // Đóng modal
        function dongModal() {
            document.getElementById('modalSua').classList.remove('show');
        }

        // Click ngoài modal để đóng
        document.getElementById('modalSua').addEventListener('click', function(e) {
            if (e.target === this) {
                dongModal();
            }
        });

        // Submit form sửa
        document.getElementById('formSuaBaiViet').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');
            formData.append('sua_bai_viet', '1');

            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '⏳ Đang lưu...';

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hienThongBao('✅ ' + data.message, 'success');
                        dongModal();
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        hienThongBao('❌ ' + (data.message || 'Có lỗi xảy ra!'), 'danger');
                        btn.disabled = false;
                        btn.innerHTML = '💾 Lưu Thay Đổi';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    hienThongBao('⚠️ Có lỗi xảy ra!', 'danger');
                    btn.disabled = false;
                    btn.innerHTML = '💾 Lưu Thay Đổi';
                });
        });

        // Xóa bài viết
        function xoaBaiViet(id) {
            if (!confirm('Bạn có chắc muốn xóa bài viết này? Hành động này không thể hoàn tác!')) {
                return;
            }

            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('xoa_bai_viet', '1');
            formData.append('id', id);

            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '⏳ Đang xóa...';

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Tìm và xóa card dựa trên data-id
                        const card = document.querySelector(`#mypost-container .bai-viet-card[data-id="${id}"]`);
                        if (card) {
                            card.remove();
                        }

                        hienThongBao('✅ ' + data.message, 'success');

                        // Cập nhật số lượng
                        const remaining = document.querySelectorAll('#mypost-container .bai-viet-card').length;
                        const statCard = document.querySelector('#mypost-tab .stat-number');
                        if (statCard) {
                            statCard.textContent = remaining;
                        }

                        // Reload nếu hết bài viết
                        if (remaining === 0) {
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    } else {
                        hienThongBao('❌ ' + (data.message || 'Có lỗi xảy ra!'), 'danger');
                        btn.disabled = false;
                        btn.innerHTML = '🗑️ Xóa';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    hienThongBao('⚠️ Có lỗi xảy ra: ' + error.message, 'danger');
                    btn.disabled = false;
                    btn.innerHTML = '🗑️ Xóa';
                });
        }
        // Thêm function này vào phần JavaScript, sau function filterLoai()

        // Lọc theo loại cho tab saved
        function filterLoaiSaved(btn, loai) {
            document.querySelectorAll('#saved-tab .filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            btn.dataset.loai = loai;

            const searchBox = document.getElementById('search-saved');
            const searchTerm = searchBox ? searchBox.value.toLowerCase().trim() : '';
            const cards = document.querySelectorAll('#saved-container .bai-viet-card');

            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                const cardLoai = card.getAttribute('data-loai');
                const matchSearch = title.includes(searchTerm);
                const matchFilter = loai === 'all' || cardLoai === loai;
                card.style.display = (matchSearch && matchFilter) ? 'block' : 'none';
            });
        }
    </script>
</body>

</html>