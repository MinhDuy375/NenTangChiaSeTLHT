<?php
include __DIR__ . '/../../../config/ketNoiDB.php';

// Xử lý thêm người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['them_nguoi_dung'])) {
    $ten_dang_nhap = lam_sach_chuoi($_POST['ten_dang_nhap']);
    $email = lam_sach_chuoi($_POST['email']);
    $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
    $ho_ten = lam_sach_chuoi($_POST['ho_ten']);
    $vai_tro = $_POST['vai_tro'];
    $trang_thai = $_POST['trang_thai'];

    // Kiểm tra trùng username hoặc email
    $sql_check = "SELECT * FROM nguoi_dung WHERE ten_dang_nhap = ? OR email = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$ten_dang_nhap, $email]);

    if ($stmt_check->rowCount() > 0) {
        $_SESSION['loi'] = "Tên đăng nhập hoặc email đã tồn tại!";
    } else {
        $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, email, mat_khau, ho_ten, vai_tro, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ten_dang_nhap, $email, $mat_khau, $ho_ten, $vai_tro, $trang_thai]);

        $_SESSION['thong_bao'] = "Thêm người dùng thành công!";
    }
    header("Location: nguoi_dung.php");
    exit;
}

// Xử lý sửa người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sua_nguoi_dung'])) {
    $id = $_POST['id'];
    $ten_dang_nhap = lam_sach_chuoi($_POST['ten_dang_nhap']);
    $email = lam_sach_chuoi($_POST['email']);
    $ho_ten = lam_sach_chuoi($_POST['ho_ten']);
    $vai_tro = $_POST['vai_tro'];
    $trang_thai = $_POST['trang_thai'];

    // Kiểm tra trùng username hoặc email (trừ bản ghi hiện tại)
    $sql_check = "SELECT * FROM nguoi_dung WHERE (ten_dang_nhap = ? OR email = ?) AND id != ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$ten_dang_nhap, $email, $id]);

    if ($stmt_check->rowCount() > 0) {
        $_SESSION['loi'] = "Tên đăng nhập hoặc email đã tồn tại!";
    } else {
        if (!empty($_POST['mat_khau'])) {
            $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
            $sql = "UPDATE nguoi_dung SET ten_dang_nhap = ?, email = ?, mat_khau = ?, ho_ten = ?, 
                    vai_tro = ?, trang_thai = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ten_dang_nhap, $email, $mat_khau, $ho_ten, $vai_tro, $trang_thai, $id]);
        } else {
            $sql = "UPDATE nguoi_dung SET ten_dang_nhap = ?, email = ?, ho_ten = ?, 
                    vai_tro = ?, trang_thai = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ten_dang_nhap, $email, $ho_ten, $vai_tro, $trang_thai, $id]);
        }

        $_SESSION['thong_bao'] = "Cập nhật người dùng thành công!";
    }
    header("Location: nguoi_dung.php");
    exit;
}

// Xử lý xóa người dùng
if (isset($_GET['xoa']) && is_numeric($_GET['xoa'])) {
    $id = $_GET['xoa'];
    $sql = "DELETE FROM nguoi_dung WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $_SESSION['thong_bao'] = "Xóa người dùng thành công!";
    header("Location: nguoi_dung.php");
    exit;
}

// Xử lý tìm kiếm và lọc
$where = "WHERE 1=1";
$params = [];

if (isset($_GET['vai_tro']) && !empty($_GET['vai_tro'])) {
    $where .= " AND vai_tro = ?";
    $params[] = $_GET['vai_tro'];
}

if (isset($_GET['trang_thai']) && !empty($_GET['trang_thai'])) {
    $where .= " AND trang_thai = ?";
    $params[] = $_GET['trang_thai'];
}

if (isset($_GET['tim_kiem']) && !empty($_GET['tim_kiem'])) {
    $where .= " AND (ten_dang_nhap LIKE ? OR email LIKE ? OR ho_ten LIKE ?)";
    $search = '%' . $_GET['tim_kiem'] . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

// Lấy danh sách người dùng
$sql = "SELECT * FROM nguoi_dung $where ORDER BY ngay_tao DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$danh_sach_nguoi_dung = $stmt->fetchAll();

// Lấy thông tin người dùng để sửa
$nguoi_dung_sua = null;
if (isset($_GET['sua']) && is_numeric($_GET['sua'])) {
    $id = $_GET['sua'];
    $sql = "SELECT * FROM nguoi_dung WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $nguoi_dung_sua = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
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
            border-bottom: 3px solid #43e97b;
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

        .loi {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
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
            color: #43e97b;
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
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #43e97b;
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
            background: #43e97b;
            color: white;
        }

        .btn-primary:hover {
            background: #38d96a;
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
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
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
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge.admin {
            background: #dc3545;
            color: white;
        }

        .badge.user {
            background: #007bff;
            color: white;
        }

        .badge.active {
            background: #28a745;
            color: white;
        }

        .badge.locked {
            background: #6c757d;
            color: white;
        }

        .note {
            font-size: 13px;
            color: #666;
            font-style: italic;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>👥 Quản Lý Người Dùng</h1>
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

        <?php if (isset($_SESSION['loi'])): ?>
            <div class="loi">
                <?php
                echo $_SESSION['loi'];
                unset($_SESSION['loi']);
                ?>
            </div>
        <?php endif; ?>

        <form class="filter-section" method="GET">
            <div class="filter-group">
                <label>Vai Trò:</label>
                <select name="vai_tro">
                    <option value="">-- Tất cả --</option>
                    <option value="quan_tri_vien" <?php echo (isset($_GET['vai_tro']) && $_GET['vai_tro'] == 'quan_tri_vien') ? 'selected' : ''; ?>>
                        Quản trị viên
                    </option>
                    <option value="nguoi_dung" <?php echo (isset($_GET['vai_tro']) && $_GET['vai_tro'] == 'nguoi_dung') ? 'selected' : ''; ?>>
                        Người dùng
                    </option>
                </select>
            </div>
            <div class="filter-group">
                <label>Trạng Thái:</label>
                <select name="trang_thai">
                    <option value="">-- Tất cả --</option>
                    <option value="hoạt_dong" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'hoạt_dong') ? 'selected' : ''; ?>>
                        Hoạt động
                    </option>
                    <option value="khoa" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'khoa') ? 'selected' : ''; ?>>
                        Khóa
                    </option>
                </select>
            </div>
            <div class="filter-group">
                <label>Tìm Kiếm:</label>
                <input type="text" name="tim_kiem" placeholder="Tìm theo username, email, họ tên..."
                    value="<?php echo isset($_GET['tim_kiem']) ? $_GET['tim_kiem'] : ''; ?>">
            </div>
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">🔍 Lọc</button>
                <a href="index.php?page=adminUser" class="btn btn-success">↻ Làm mới</a>
            </div>
        </form>

        <div class="form-section">
            <h2><?php echo $nguoi_dung_sua ? 'Sửa Người Dùng' : 'Thêm Người Dùng Mới'; ?></h2>
            <form method="POST">
                <?php if ($nguoi_dung_sua): ?>
                    <input type="hidden" name="id" value="<?php echo $nguoi_dung_sua['id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Tên Đăng Nhập: *</label>
                        <input type="text" name="ten_dang_nhap" required
                            value="<?php echo $nguoi_dung_sua ? $nguoi_dung_sua['ten_dang_nhap'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Email: *</label>
                        <input type="email" name="email" required
                            value="<?php echo $nguoi_dung_sua ? $nguoi_dung_sua['email'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Mật Khẩu: <?php echo $nguoi_dung_sua ? '' : '*'; ?></label>
                        <input type="password" name="mat_khau" <?php echo $nguoi_dung_sua ? '' : 'required'; ?>>
                        <?php if ($nguoi_dung_sua): ?>
                            <div class="note">Để trống nếu không muốn thay đổi mật khẩu</div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Họ Tên:</label>
                        <input type="text" name="ho_ten"
                            value="<?php echo $nguoi_dung_sua ? $nguoi_dung_sua['ho_ten'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Vai Trò: *</label>
                        <select name="vai_tro" required>
                            <option value="nguoi_dung" <?php echo ($nguoi_dung_sua && $nguoi_dung_sua['vai_tro'] == 'nguoi_dung') ? 'selected' : ''; ?>>
                                Người dùng
                            </option>
                            <option value="quan_tri_vien" <?php echo ($nguoi_dung_sua && $nguoi_dung_sua['vai_tro'] == 'quan_tri_vien') ? 'selected' : ''; ?>>
                                Quản trị viên
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Trạng Thái: *</label>
                        <select name="trang_thai" required>
                            <option value="hoạt_dong" <?php echo ($nguoi_dung_sua && $nguoi_dung_sua['trang_thai'] == 'hoạt_dong') ? 'selected' : ''; ?>>
                                Hoạt động
                            </option>
                            <option value="khoa" <?php echo ($nguoi_dung_sua && $nguoi_dung_sua['trang_thai'] == 'khoa') ? 'selected' : ''; ?>>
                                Khóa
                            </option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="<?php echo $nguoi_dung_sua ? 'sua_nguoi_dung' : 'them_nguoi_dung'; ?>"
                    class="btn <?php echo $nguoi_dung_sua ? 'btn-success' : 'btn-primary'; ?>">
                    <?php echo $nguoi_dung_sua ? '✓ Cập Nhật' : '+ Thêm Người Dùng'; ?>
                </button>

                <?php if ($nguoi_dung_sua): ?>
                    <a href="index.php?page=adminUser" class="cancel-btn">Hủy</a>
                <?php endif; ?>
            </form>
        </div>

        <h2>Danh Sách Người Dùng (<?php echo count($danh_sach_nguoi_dung); ?> người)</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Đăng Nhập</th>
                    <th>Email</th>
                    <th>Họ Tên</th>
                    <th>Vai Trò</th>
                    <th>Trạng Thái</th>
                    <th>Ngày Tạo</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($danh_sach_nguoi_dung) > 0): ?>
                    <?php foreach ($danh_sach_nguoi_dung as $nd): ?>
                        <tr>
                            <td><?php echo $nd['id']; ?></td>
                            <td><strong><?php echo $nd['ten_dang_nhap']; ?></strong></td>
                            <td><?php echo $nd['email']; ?></td>
                            <td><?php echo $nd['ho_ten']; ?></td>
                            <td>
                                <span class="badge <?php echo $nd['vai_tro'] == 'quan_tri_vien' ? 'admin' : 'user'; ?>">
                                    <?php echo $nd['vai_tro'] == 'quan_tri_vien' ? 'Quản trị viên' : 'Người dùng'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $nd['trang_thai'] == 'hoạt_dong' ? 'active' : 'locked'; ?>">
                                    <?php echo $nd['trang_thai'] == 'hoạt_dong' ? 'Hoạt động' : 'Khóa'; ?>
                                </span>
                            </td>
                            <td><?php echo dinh_dang_ngay($nd['ngay_tao']); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="index.php?page=adminUser?sua=<?php echo $nd['id']; ?>"
                                        class="btn btn-warning">Sửa</a>
                                    <a href="index.php?page=adminUser?xoa=<?php echo $nd['id']; ?>"
                                        class="btn btn-danger"
                                        onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')">Xóa</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: #999;">
                            Không tìm thấy người dùng nào
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>