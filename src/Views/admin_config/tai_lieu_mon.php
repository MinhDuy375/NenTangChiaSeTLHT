<?php
include __DIR__ . '/../../../config/ketNoiDB.php';

// Kiểm tra có id môn học không
if (!isset($_GET['id_mon']) || !is_numeric($_GET['id_mon'])) {
    header("Location: mon_hoc.php");
    exit;
}

$id_mon = $_GET['id_mon'];

// Lấy thông tin môn học
$sql_mon = "SELECT * FROM mon_hoc WHERE id = ?";
$stmt_mon = $pdo->prepare($sql_mon);
$stmt_mon->execute([$id_mon]);
$mon_hoc = $stmt_mon->fetch();

if (!$mon_hoc) {
    header("Location: mon_hoc.php");
    exit;
}

// Xử lý thêm tài liệu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['them_tai_lieu'])) {
    $tieu_de = lam_sach_chuoi($_POST['tieu_de']);
    $mo_ta = lam_sach_chuoi($_POST['mo_ta']);
    $id_nguoi_dung = 12; // Mặc định user id

    // Xử lý upload file
    $file_upload = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $ten_file_goc = $_FILES['file']['name'];

        if (kiem_tra_loai_file($ten_file_goc)) {
            $thu_muc = 'uploads/tai_lieu/';
            if (!is_dir($thu_muc)) {
                mkdir($thu_muc, 0777, true);
            }

            $ten_file_moi = tao_ten_file_duy_nhat($ten_file_goc);
            $duong_dan = $thu_muc . $ten_file_moi;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $duong_dan)) {
                $file_upload = $duong_dan;
            } else {
                $_SESSION['loi'] = "Lỗi khi upload file!";
            }
        } else {
            $_SESSION['loi'] = "Chỉ chấp nhận file PDF, DOC, DOCX!";
        }
    }

    if (!isset($_SESSION['loi'])) {
        $sql = "INSERT INTO bai_chia_se (loai, tieu_de, mo_ta, file_upload, id_mon_hoc, id_nguoi_dung) 
                VALUES ('tai_lieu', ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tieu_de, $mo_ta, $file_upload, $id_mon, $id_nguoi_dung]);

        $_SESSION['thong_bao'] = "Thêm tài liệu thành công!";
    }

    header("Location: tai_lieu_mon.php?id_mon=$id_mon");
    exit;
}

// Xử lý sửa tài liệu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sua_tai_lieu'])) {
    $id = $_POST['id'];
    $tieu_de = lam_sach_chuoi($_POST['tieu_de']);
    $mo_ta = lam_sach_chuoi($_POST['mo_ta']);

    // Lấy file cũ
    $sql_old = "SELECT file_upload FROM bai_chia_se WHERE id = ?";
    $stmt_old = $pdo->prepare($sql_old);
    $stmt_old->execute([$id]);
    $old_file = $stmt_old->fetchColumn();

    $file_upload = $old_file;

    // Xử lý upload file mới
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $ten_file_goc = $_FILES['file']['name'];

        if (kiem_tra_loai_file($ten_file_goc)) {
            $thu_muc = 'uploads/tai_lieu/';
            if (!is_dir($thu_muc)) {
                mkdir($thu_muc, 0777, true);
            }

            $ten_file_moi = tao_ten_file_duy_nhat($ten_file_goc);
            $duong_dan = $thu_muc . $ten_file_moi;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $duong_dan)) {
                // Xóa file cũ
                if ($old_file && file_exists($old_file)) {
                    unlink($old_file);
                }
                $file_upload = $duong_dan;
            }
        } else {
            $_SESSION['loi'] = "Chỉ chấp nhận file PDF, DOC, DOCX!";
        }
    }

    if (!isset($_SESSION['loi'])) {
        $sql = "UPDATE bai_chia_se SET tieu_de = ?, mo_ta = ?, file_upload = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tieu_de, $mo_ta, $file_upload, $id]);

        $_SESSION['thong_bao'] = "Cập nhật tài liệu thành công!";
    }

    header("Location: tai_lieu_mon.php?id_mon=$id_mon");
    exit;
}

// Xử lý xóa tài liệu
if (isset($_GET['xoa']) && is_numeric($_GET['xoa'])) {
    $id = $_GET['xoa'];

    // Lấy thông tin file để xóa
    $sql_file = "SELECT file_upload FROM bai_chia_se WHERE id = ?";
    $stmt_file = $pdo->prepare($sql_file);
    $stmt_file->execute([$id]);
    $file_path = $stmt_file->fetchColumn();

    // Xóa bản ghi
    $sql = "DELETE FROM bai_chia_se WHERE id = ? AND loai = 'tai_lieu'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // Xóa file
    if ($file_path && file_exists($file_path)) {
        unlink($file_path);
    }

    $_SESSION['thong_bao'] = "Xóa tài liệu thành công!";
    header("Location: tai_lieu_mon.php?id_mon=$id_mon");
    exit;
}

// Xử lý tìm kiếm
$where = "WHERE loai = 'tai_lieu' AND id_mon_hoc = ?";
$params = [$id_mon];

if (isset($_GET['tim_kiem']) && !empty($_GET['tim_kiem'])) {
    $where .= " AND (tieu_de LIKE ? OR mo_ta LIKE ?)";
    $search = '%' . $_GET['tim_kiem'] . '%';
    $params[] = $search;
    $params[] = $search;
}

// Lấy danh sách tài liệu
$sql = "SELECT b.*, n.ho_ten 
        FROM bai_chia_se b 
        LEFT JOIN nguoi_dung n ON b.id_nguoi_dung = n.id 
        $where ORDER BY b.ngay_tao DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$danh_sach_tai_lieu = $stmt->fetchAll();

// Lấy thông tin tài liệu để sửa
$tai_lieu_sua = null;
if (isset($_GET['sua']) && is_numeric($_GET['sua'])) {
    $id = $_GET['sua'];
    $sql = "SELECT * FROM bai_chia_se WHERE id = ? AND loai = 'tai_lieu' AND id_mon_hoc = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $id_mon]);
    $tai_lieu_sua = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài Liệu - <?php echo $mon_hoc['ten_mon']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;

        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f093fb;
        }

        .header-left h1 {
            color: #333;
            font-size: 2em;
            margin-bottom: 5px;
        }

        .header-left .subtitle {
            color: #666;
            font-size: 1.1em;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #545b62;
            transform: translateY(-2px);
        }

        .thong-bao {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .loi {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            align-items: end;
        }

        .search-group {
            flex: 1;
        }

        .search-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .search-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .form-section h2 {
            color: #f093fb;
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #f093fb;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        input[type="file"] {
            padding: 8px;
        }

        .btn {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
        }

        .btn-primary {
            background: #f093fb;
            color: white;
        }

        .btn-primary:hover {
            background: #e082ea;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .actions a,
        .actions button {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 13px;
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }

        .cancel-btn:hover {
            background: #545b62;
        }

        .file-icon {
            font-size: 1.5em;
            margin-right: 5px;
        }

        .file-link {
            color: #007bff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .file-link:hover {
            text-decoration: underline;
        }

        .note {
            font-size: 13px;
            color: #666;
            font-style: italic;
            margin-top: 5px;
        }

        .stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stats h3 {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <h1>📚 Tài Liệu Môn Học</h1>
                <div class="subtitle">Môn: <strong><?php echo $mon_hoc['ten_mon']; ?></strong></div>
            </div>
            <a href="index.php?page=adminMon" class="btn-back">← Quay lại Môn học</a>
        </div>

        <div class="stats">
            <h3>Tổng số tài liệu: <?php echo count($danh_sach_tai_lieu); ?></h3>
            <div><?php echo $mon_hoc['mo_ta']; ?></div>
        </div>

        <?php if (isset($_SESSION['thong_bao'])): ?>
            <div class="thong-bao">
                <?php
                echo $_SESSION['thong_bao'];
                unset($_SESSION['thong_bao']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['loi'])): ?>
            <div class="loi">
                <?php
                echo $_SESSION['loi'];
                unset($_SESSION['loi']);
                ?>
            </div>
        <?php endif; ?>

        <form class="search-section" method="GET">
            <input type="hidden" name="id_mon" value="<?php echo $id_mon; ?>">
            <div class="search-group">
                <label>Tìm Kiếm Tài Liệu:</label>
                <input type="text" name="tim_kiem" placeholder="Tìm theo tiêu đề, mô tả..."
                    value="<?php echo isset($_GET['tim_kiem']) ? $_GET['tim_kiem'] : ''; ?>">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">🔍 Tìm kiếm</button>
                <a href="index.php?page=adminDocs?id_mon=<?php echo $id_mon; ?>" class="btn btn-success">↻ Làm mới</a>
            </div>
        </form>

        <div class="form-section">
            <h2><?php echo $tai_lieu_sua ? 'Sửa Tài Liệu' : 'Thêm Tài Liệu Mới'; ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($tai_lieu_sua): ?>
                    <input type="hidden" name="id" value="<?php echo $tai_lieu_sua['id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Tiêu Đề Tài Liệu: *</label>
                        <input type="text" name="tieu_de" required
                            value="<?php echo $tai_lieu_sua ? $tai_lieu_sua['tieu_de'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>File Tài Liệu: <?php echo $tai_lieu_sua ? '' : '*'; ?></label>
                        <input type="file" name="file" accept=".pdf,.doc,.docx"
                            <?php echo $tai_lieu_sua ? '' : 'required'; ?>>
                        <div class="note">Chấp nhận: PDF, DOC, DOCX (Max: 10MB)</div>
                        <?php if ($tai_lieu_sua && $tai_lieu_sua['file_upload']): ?>
                            <div class="note">File hiện tại:
                                <a href="<?php echo $tai_lieu_sua['file_upload']; ?>" target="_blank">
                                    <?php echo basename($tai_lieu_sua['file_upload']); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group full-width">
                        <label>Mô Tả:</label>
                        <textarea name="mo_ta" rows="4"><?php echo $tai_lieu_sua ? $tai_lieu_sua['mo_ta'] : ''; ?></textarea>
                    </div>
                </div>

                <button type="submit" name="<?php echo $tai_lieu_sua ? 'sua_tai_lieu' : 'them_tai_lieu'; ?>"
                    class="btn <?php echo $tai_lieu_sua ? 'btn-success' : 'btn-primary'; ?>">
                    <?php echo $tai_lieu_sua ? '✓ Cập Nhật' : '+ Thêm Tài Liệu'; ?>
                </button>

                <?php if ($tai_lieu_sua): ?>
                    <a href="index.php?page=adminDocs?id_mon=<?php echo $id_mon; ?>" class="cancel-btn">Hủy</a>
                <?php endif; ?>
            </form>
        </div>

        <h2>Danh Sách Tài Liệu</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu Đề</th>
                    <th>Mô Tả</th>
                    <th>File</th>
                    <th>Người Đăng</th>
                    <th>Ngày Tạo</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($danh_sach_tai_lieu) > 0): ?>
                    <?php foreach ($danh_sach_tai_lieu as $tl): ?>
                        <tr>
                            <td><?php echo $tl['id']; ?></td>
                            <td><strong><?php echo $tl['tieu_de']; ?></strong></td>
                            <td><?php echo substr($tl['mo_ta'], 0, 80); ?><?php echo strlen($tl['mo_ta']) > 80 ? '...' : ''; ?></td>
                            <td>
                                <?php if ($tl['file_upload']): ?>
                                    <a href="<?php echo $tl['file_upload']; ?>" target="_blank" class="file-link">
                                        <span class="file-icon">📄</span>
                                        <?php
                                        $ext = strtoupper(pathinfo($tl['file_upload'], PATHINFO_EXTENSION));
                                        echo $ext;
                                        ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $tl['ho_ten']; ?></td>
                            <td><?php echo dinh_dang_ngay($tl['ngay_tao']); ?></td>
                            <td>
                                <div class="actions">
                                    <?php if ($tl['file_upload']): ?>
                                        <a href="<?php echo $tl['file_upload']; ?>" download
                                            class="btn btn-info" title="Tải xuống">⬇</a>
                                    <?php endif; ?>
                                    <a href="index.php?page=adminDocs?id_mon=<?php echo $id_mon; ?>&sua=<?php echo $tl['id']; ?>"
                                        class="btn btn-warning">Sửa</a>
                                    <a href="index.php?page=adminDocs?id_mon=<?php echo $id_mon; ?>&xoa=<?php echo $tl['id']; ?>"
                                        class="btn btn-danger"
                                        onclick="return confirm('Bạn có chắc muốn xóa tài liệu này?')">Xóa</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                            Chưa có tài liệu nào cho môn học này
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>