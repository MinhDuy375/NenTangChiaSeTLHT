<?php
include __DIR__ . '/../../../config/ketNoiDB.php';

// Xử lý AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    // Thêm người dùng
    if (isset($_POST['them_nguoi_dung'])) {
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
            echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc email đã tồn tại!']);
            exit;
        }

        $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, email, mat_khau, ho_ten, vai_tro, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ten_dang_nhap, $email, $mat_khau, $ho_ten, $vai_tro, $trang_thai]);

        echo json_encode(['success' => true, 'message' => 'Thêm người dùng thành công!']);
        exit;
    }

    // Sửa người dùng
    if (isset($_POST['sua_nguoi_dung'])) {
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
            echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc email đã tồn tại!']);
            exit;
        }

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

        echo json_encode(['success' => true, 'message' => 'Cập nhật người dùng thành công!']);
        exit;
    }

    // Xóa người dùng
    if (isset($_POST['xoa_nguoi_dung'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM nguoi_dung WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Xóa người dùng thành công!']);
        exit;
    }

    // Lấy thông tin người dùng để sửa
    if (isset($_POST['lay_nguoi_dung'])) {
        $id = $_POST['id'];
        $sql = "SELECT * FROM nguoi_dung WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $nguoi_dung = $stmt->fetch();

        echo json_encode(['success' => true, 'data' => $nguoi_dung]);
        exit;
    }
}

// Lấy danh sách người dùng
$search = isset($_GET['search']) ? lam_sach_chuoi($_GET['search']) : '';
$filter_vai_tro = isset($_GET['vai_tro']) ? $_GET['vai_tro'] : '';
$filter_trang_thai = isset($_GET['trang_thai']) ? $_GET['trang_thai'] : '';

$sql = "SELECT * FROM nguoi_dung WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (ten_dang_nhap LIKE ? OR email LIKE ? OR ho_ten LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($filter_vai_tro)) {
    $sql .= " AND vai_tro = ?";
    $params[] = $filter_vai_tro;
}

if (!empty($filter_trang_thai)) {
    $sql .= " AND trang_thai = ?";
    $params[] = $filter_trang_thai;
}

$sql .= " ORDER BY ngay_tao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$danh_sach_nguoi_dung = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người Dùng</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            flex-wrap: wrap;
            gap: 15px;
        }

        h1 {
            color: #2c3e50;
            font-size: 1.8em;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
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
            color: #212529;
            padding: 8px 15px;
            font-size: 13px;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            font-size: 13px;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        /* Alert */
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
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

        /* Form Section */
        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .form-section h2 {
            color: #495057;
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .form-note {
            font-size: 12px;
            color: #6c757d;
            font-style: italic;
            margin-top: 5px;
        }

        /* Search & Filter */
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .search-input {
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
        }

        .search-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 500;
            white-space: nowrap;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            overflow-y: auto;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h2 {
            color: #2c3e50;
            font-size: 1.5em;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 1.5em;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .actions .btn {
                width: 100%;
            }

            table {
                font-size: 13px;
            }

            th,
            td {
                padding: 8px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>👥 Quản Lý Người Dùng</h1>
            <a href="index.php?page=admin" class="btn btn-back">← Quay lại</a>
        </div>

        <div id="alertMessage" class="alert"></div>

        <div class="form-section">
            <h2>Thêm Người Dùng Mới</h2>
            <form id="formThemNguoiDung">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tên Đăng Nhập: <span style="color: red;">*</span></label>
                        <input type="text" name="ten_dang_nhap" id="ten_dang_nhap" required>
                    </div>

                    <div class="form-group">
                        <label>Email: <span style="color: red;">*</span></label>
                        <input type="email" name="email" id="email" required>
                    </div>

                    <div class="form-group">
                        <label>Mật Khẩu: <span style="color: red;">*</span></label>
                        <input type="password" name="mat_khau" id="mat_khau" required>
                    </div>

                    <div class="form-group">
                        <label>Họ Tên:</label>
                        <input type="text" name="ho_ten" id="ho_ten">
                    </div>

                    <div class="form-group">
                        <label>Vai Trò: <span style="color: red;">*</span></label>
                        <select name="vai_tro" id="vai_tro" required>
                            <option value="nguoi_dung">Người dùng</option>
                            <option value="quan_tri_vien">Quản trị viên</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Trạng Thái: <span style="color: red;">*</span></label>
                        <select name="trang_thai" id="trang_thai" required>
                            <option value="hoat_dong">Hoạt động</option>
                            <option value="khoa">Khóa</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">+ Thêm Người Dùng</button>
            </form>
        </div>

        <div class="search-section">
            <form class="search-form" method="GET" action="">
                <input type="hidden" name="page" value="adminUser">
                <input type="text" name="search" class="search-input" placeholder="Tìm kiếm theo username, email, họ tên..." value="<?php echo htmlspecialchars($search); ?>">

                <select name="vai_tro" class="search-input">
                    <option value="">Tất cả vai trò</option>
                    <option value="nguoi_dung" <?php echo $filter_vai_tro === 'nguoi_dung' ? 'selected' : ''; ?>>Người dùng</option>
                    <option value="quan_tri_vien" <?php echo $filter_vai_tro === 'quan_tri_vien' ? 'selected' : ''; ?>>Quản trị viên</option>
                </select>

                <select name="trang_thai" class="search-input">
                    <option value="">Tất cả trạng thái</option>
                    <option value="hoat_dong" <?php echo $filter_trang_thai === 'hoat_dong' ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="khoa" <?php echo $filter_trang_thai === 'khoa' ? 'selected' : ''; ?>>Khóa</option>
                </select>
            </form>

            <div class="filter-buttons">
                <button type="submit" form="searchForm" class="btn btn-primary">Tìm kiếm</button>
                <?php if ($search || $filter_vai_tro || $filter_trang_thai): ?>
                    <a href="index.php?page=adminUser" class="btn btn-secondary">Xóa bộ lọc</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (count($danh_sach_nguoi_dung) > 0): ?>
            <h2>Danh Sách Người Dùng (<?php echo count($danh_sach_nguoi_dung); ?>)</h2>
            <div class="table-container">
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
                        <?php foreach ($danh_sach_nguoi_dung as $nd): ?>
                            <tr data-id="<?php echo $nd['id']; ?>">
                                <td><?php echo $nd['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($nd['ten_dang_nhap']); ?></strong></td>
                                <td><?php echo htmlspecialchars($nd['email']); ?></td>
                                <td><?php echo htmlspecialchars($nd['ho_ten'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge <?php echo $nd['vai_tro'] === 'quan_tri_vien' ? 'admin' : 'user'; ?>">
                                        <?php echo $nd['vai_tro'] === 'quan_tri_vien' ? 'Quản trị viên' : 'Người dùng'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $nd['trang_thai'] === 'hoat_dong' ? 'active' : 'locked'; ?>">
                                        <?php echo $nd['trang_thai'] === 'hoat_dong' ? 'Hoạt động' : 'Khóa'; ?>
                                    </span>
                                </td>
                                <td><?php echo dinh_dang_ngay($nd['ngay_tao']); ?></td>
                                <td>
                                    <div class="actions">
                                        <button onclick="moModalSua(<?php echo $nd['id']; ?>)" class="btn btn-warning">Sửa</button>
                                        <button onclick="xoaNguoiDung(<?php echo $nd['id']; ?>)" class="btn btn-danger">Xóa</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Chưa có người dùng nào</h3>
                <p><?php echo $search ? 'Không tìm thấy người dùng phù hợp với từ khóa "' . htmlspecialchars($search) . '"' : 'Hệ thống chưa có người dùng nào.'; ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Sửa Người Dùng -->
    <div id="modalSua" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Sửa Thông Tin Người Dùng</h2>
            </div>
            <form id="formSua">
                <input type="hidden" name="id" id="sua_id">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Tên Đăng Nhập: <span style="color: red;">*</span></label>
                        <input type="text" name="ten_dang_nhap" id="sua_ten_dang_nhap" required>
                    </div>

                    <div class="form-group">
                        <label>Email: <span style="color: red;">*</span></label>
                        <input type="email" name="email" id="sua_email" required>
                    </div>

                    <div class="form-group">
                        <label>Mật Khẩu:</label>
                        <input type="password" name="mat_khau" id="sua_mat_khau">
                        <div class="form-note">Để trống nếu không muốn thay đổi mật khẩu</div>
                    </div>

                    <div class="form-group">
                        <label>Họ Tên:</label>
                        <input type="text" name="ho_ten" id="sua_ho_ten">
                    </div>

                    <div class="form-group">
                        <label>Vai Trò: <span style="color: red;">*</span></label>
                        <select name="vai_tro" id="sua_vai_tro" required>
                            <option value="nguoi_dung">Người dùng</option>
                            <option value="quan_tri_vien">Quản trị viên</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Trạng Thái: <span style="color: red;">*</span></label>
                        <select name="trang_thai" id="sua_trang_thai" required>
                            <option value="hoat_dong">Hoạt động</option>
                            <option value="khoa">Khóa</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="dongModal()" class="btn btn-secondary">Hủy</button>
                    <button type="submit" class="btn btn-success">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function hienThongBao(message, type = 'success') {
            const alert = document.getElementById('alertMessage');
            alert.className = `alert alert-${type} show`;
            alert.textContent = message;

            setTimeout(() => {
                alert.classList.remove('show');
            }, 3000);
        }

        // Thêm người dùng
        document.getElementById('formThemNguoiDung').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');
            formData.append('them_nguoi_dung', '1');

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hienThongBao(data.message, 'success');
                        this.reset();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        hienThongBao(data.message, 'danger');
                    }
                })
                .catch(error => hienThongBao('Có lỗi xảy ra!', 'danger'));
        });

        // Mở modal sửa
        function moModalSua(id) {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('lay_nguoi_dung', '1');
            formData.append('id', id);

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('sua_id').value = data.data.id;
                        document.getElementById('sua_ten_dang_nhap').value = data.data.ten_dang_nhap;
                        document.getElementById('sua_email').value = data.data.email;
                        document.getElementById('sua_ho_ten').value = data.data.ho_ten || '';
                        document.getElementById('sua_vai_tro').value = data.data.vai_tro;
                        document.getElementById('sua_trang_thai').value = data.data.trang_thai;
                        document.getElementById('modalSua').classList.add('show');
                    }
                });
        }

        function dongModal() {
            document.getElementById('modalSua').classList.remove('show');
        }

        // Sửa người dùng
        document.getElementById('formSua').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');
            formData.append('sua_nguoi_dung', '1');

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hienThongBao(data.message, 'success');
                        dongModal();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        hienThongBao(data.message, 'danger');
                    }
                })
                .catch(error => hienThongBao('Có lỗi xảy ra!', 'danger'));
        });

        // Xóa người dùng
        function xoaNguoiDung(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa người dùng này?')) return;

            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('xoa_nguoi_dung', '1');
            formData.append('id', id);

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hienThongBao(data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        hienThongBao('Có lỗi xảy ra!', 'danger');
                    }
                })
                .catch(error => hienThongBao('Có lỗi xảy ra!', 'danger'));
        }

        // Click outside modal to close
        document.getElementById('modalSua').addEventListener('click', function(e) {
            if (e.target === this) dongModal();
        });

        // Fix form submission for search
        document.querySelectorAll('.search-form').forEach(form => {
            const submitBtn = form.closest('.search-section').querySelector('.filter-buttons .btn-primary');
            if (submitBtn) {
                submitBtn.addEventListener('click', () => form.submit());
            }
        });
    </script>
</body>

</html>