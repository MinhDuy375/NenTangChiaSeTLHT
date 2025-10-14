<?php
include __DIR__ . '/../../../config/ketNoiDB.php';

// Xử lý thêm bài viết
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['them_bai'])) {
    $tieu_de = lam_sach_chuoi($_POST['tieu_de']);
    $mo_ta = lam_sach_chuoi($_POST['mo_ta']);
    $link_host = lam_sach_chuoi($_POST['link_host']);
    $link_source = lam_sach_chuoi($_POST['link_source']);
    $cong_nghe = lam_sach_chuoi($_POST['cong_nghe']);
    $id_danh_muc = !empty($_POST['id_danh_muc']) ? $_POST['id_danh_muc'] : null;
    $id_nguoi_dung = 12; // Mặc định user id

    $sql = "INSERT INTO bai_chia_se (loai, tieu_de, mo_ta, link_host, link_source, cong_nghe, id_danh_muc, id_nguoi_dung) 
            VALUES ('bai_viet', ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tieu_de, $mo_ta, $link_host, $link_source, $cong_nghe, $id_danh_muc, $id_nguoi_dung]);

    $_SESSION['thong_bao'] = "Thêm bài viết thành công!";
    header("Location: bai_viet.php");
    exit;
}

// Xử lý sửa bài viết
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sua_bai'])) {
    $id = $_POST['id'];
    $tieu_de = lam_sach_chuoi($_POST['tieu_de']);
    $mo_ta = lam_sach_chuoi($_POST['mo_ta']);
    $link_host = lam_sach_chuoi($_POST['link_host']);
    $link_source = lam_sach_chuoi($_POST['link_source']);
    $cong_nghe = lam_sach_chuoi($_POST['cong_nghe']);
    $id_danh_muc = !empty($_POST['id_danh_muc']) ? $_POST['id_danh_muc'] : null;

    $sql = "UPDATE bai_chia_se SET tieu_de = ?, mo_ta = ?, link_host = ?, link_source = ?, 
            cong_nghe = ?, id_danh_muc = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tieu_de, $mo_ta, $link_host, $link_source, $cong_nghe, $id_danh_muc, $id]);

    $_SESSION['thong_bao'] = "Cập nhật bài viết thành công!";
    header("Location: bai_viet.php");
    exit;
}

// Xử lý xóa bài viết
if (isset($_GET['xoa']) && is_numeric($_GET['xoa'])) {
    $id = $_GET['xoa'];
    $sql = "DELETE FROM bai_chia_se WHERE id = ? AND loai = 'bai_viet'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $_SESSION['thong_bao'] = "Xóa bài viết thành công!";
    header("Location: bai_viet.php");
    exit;
}

// Lấy danh sách danh mục
$sql_dm = "SELECT * FROM danh_muc ORDER BY ten_danh_muc";
$danh_muc_list = $pdo->query($sql_dm)->fetchAll();

// Xử lý lọc và tìm kiếm
$where = "WHERE loai = 'bai_viet'";
$params = [];

if (isset($_GET['danh_muc']) && is_numeric($_GET['danh_muc'])) {
    $where .= " AND id_danh_muc = ?";
    $params[] = $_GET['danh_muc'];
}

if (isset($_GET['tim_kiem']) && !empty($_GET['tim_kiem'])) {
    $where .= " AND (tieu_de LIKE ? OR mo_ta LIKE ? OR cong_nghe LIKE ?)";
    $search = '%' . $_GET['tim_kiem'] . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

// Lấy danh sách bài viết
$sql = "SELECT b.*, d.ten_danh_muc, n.ho_ten 
        FROM bai_chia_se b 
        LEFT JOIN danh_muc d ON b.id_danh_muc = d.id 
        LEFT JOIN nguoi_dung n ON b.id_nguoi_dung = n.id 
        $where ORDER BY b.ngay_tao DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$danh_sach_bai = $stmt->fetchAll();

// Lấy thông tin bài viết để sửa
$bai_sua = null;
if (isset($_GET['sua']) && is_numeric($_GET['sua'])) {
    $id = $_GET['sua'];
    $sql = "SELECT * FROM bai_chia_se WHERE id = ? AND loai = 'bai_viet'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $bai_sua = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bài Viết</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            border-bottom: 3px solid #4facfe;
        }

        h1 {
            color: #333;
            font-size: 2em;
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

        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .filter-group select,
        .filter-group input {
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
            color: #4facfe;
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
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #4facfe;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
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
            background: #4facfe;
            color: white;
        }

        .btn-primary:hover {
            background: #3a8fd9;
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            background: #e9ecef;
            color: #495057;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📝 Quản Lý Bài Viết</h1>
            <a href="index.php?page=admin" class="btn-back">← Quay lại</a>
        </div>

        <?php if (isset($_SESSION['thong_bao'])): ?>
            <div class="thong-bao">
                <?php
                echo $_SESSION['thong_bao'];
                unset($_SESSION['thong_bao']);
                ?>
            </div>
        <?php endif; ?>

        <form class="filter-section" method="GET">
            <div class="filter-group">
                <label>Danh Mục:</label>
                <select name="danh_muc">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($danh_muc_list as $dm): ?>
                        <option value="<?php echo $dm['id']; ?>"
                            <?php echo (isset($_GET['danh_muc']) && $_GET['danh_muc'] == $dm['id']) ? 'selected' : ''; ?>>
                            <?php echo $dm['ten_danh_muc']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Tìm Kiếm:</label>
                <input type="text" name="tim_kiem" placeholder="Tìm theo tiêu đề, mô tả, công nghệ..."
                    value="<?php echo isset($_GET['tim_kiem']) ? $_GET['tim_kiem'] : ''; ?>">
            </div>
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">🔍 Lọc</button>
                <a href="index.php?page=adminBai" class="btn btn-success">↻ Làm mới</a>
            </div>
        </form>

        <div class="form-section">
            <h2><?php echo $bai_sua ? 'Sửa Bài Viết' : 'Thêm Bài Viết Mới'; ?></h2>
            <form method="POST">
                <?php if ($bai_sua): ?>
                    <input type="hidden" name="id" value="<?php echo $bai_sua['id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Tiêu Đề: *</label>
                        <input type="text" name="tieu_de" required
                            value="<?php echo $bai_sua ? $bai_sua['tieu_de'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Danh Mục:</label>
                        <select name="id_danh_muc">
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($danh_muc_list as $dm): ?>
                                <option value="<?php echo $dm['id']; ?>"
                                    <?php echo ($bai_sua && $bai_sua['id_danh_muc'] == $dm['id']) ? 'selected' : ''; ?>>
                                    <?php echo $dm['ten_danh_muc']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label>Mô Tả:</label>
                        <textarea name="mo_ta" rows="3"><?php echo $bai_sua ? $bai_sua['mo_ta'] : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Link Host:</label>
                        <input type="text" name="link_host"
                            value="<?php echo $bai_sua ? $bai_sua['link_host'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Link Source:</label>
                        <input type="text" name="link_source"
                            value="<?php echo $bai_sua ? $bai_sua['link_source'] : ''; ?>">
                    </div>

                    <div class="form-group full-width">
                        <label>Công Nghệ:</label>
                        <input type="text" name="cong_nghe" placeholder="VD: PHP, Laravel, MySQL"
                            value="<?php echo $bai_sua ? $bai_sua['cong_nghe'] : ''; ?>">
                    </div>
                </div>

                <button type="submit" name="<?php echo $bai_sua ? 'sua_bai' : 'them_bai'; ?>"
                    class="btn <?php echo $bai_sua ? 'btn-success' : 'btn-primary'; ?>">
                    <?php echo $bai_sua ? '✓ Cập Nhật' : '+ Thêm Bài Viết'; ?>
                </button>

                <?php if ($bai_sua): ?>
                    <a href="index.php?page=adminBai" class="cancel-btn">Hủy</a>
                <?php endif; ?>
            </form>
        </div>

        <h2>Danh Sách Bài Viết (<?php echo count($danh_sach_bai); ?> bài)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu Đề</th>
                    <th>Mô Tả</th>
                    <th>Công Nghệ</th>
                    <th>Danh Mục</th>
                    <th>Người Đăng</th>
                    <th>Ngày Tạo</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($danh_sach_bai) > 0): ?>
                    <?php foreach ($danh_sach_bai as $bai): ?>
                        <tr>
                            <td><?php echo $bai['id']; ?></td>
                            <td><strong><?php echo $bai['tieu_de']; ?></strong></td>
                            <td><?php echo substr($bai['mo_ta'], 0, 100); ?><?php echo strlen($bai['mo_ta']) > 100 ? '...' : ''; ?></td>
                            <td>
                                <?php if ($bai['cong_nghe']): ?>
                                    <span class="badge"><?php echo $bai['cong_nghe']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $bai['ten_danh_muc'] ?? '<em>Chưa phân loại</em>'; ?></td>
                            <td><?php echo $bai['ho_ten']; ?></td>
                            <td><?php echo dinh_dang_ngay($bai['ngay_tao']); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="index.php?page=adminBai?sua=<?php echo $bai['id']; ?>"
                                        class="btn btn-warning">Sửa</a>
                                    <a href="index.php?page=adminBai?xoa=<?php echo $bai['id']; ?>"
                                        class="btn btn-danger"
                                        onclick="return confirm('Bạn có chắc muốn xóa bài viết này?')">Xóa</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: #999;">
                            Không tìm thấy bài viết nào
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>