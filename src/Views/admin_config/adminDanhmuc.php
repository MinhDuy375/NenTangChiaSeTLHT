<?php
include __DIR__ . '/../../../config/ketNoiDB.php';

// Xử lý AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    // Thêm danh mục
    if (isset($_POST['them_danh_muc'])) {
        $ten_danh_muc = lam_sach_chuoi($_POST['ten_danh_muc']);
        $mo_ta = lam_sach_chuoi($_POST['mo_ta']);

        $sql = "INSERT INTO danh_muc (ten_danh_muc, mo_ta) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ten_danh_muc, $mo_ta]);

        echo json_encode(['success' => true, 'message' => 'Thêm danh mục thành công!']);
        exit;
    }

    // Sửa danh mục
    if (isset($_POST['sua_danh_muc'])) {
        $id = $_POST['id'];
        $ten_danh_muc = lam_sach_chuoi($_POST['ten_danh_muc']);
        $mo_ta = lam_sach_chuoi($_POST['mo_ta']);

        $sql = "UPDATE danh_muc SET ten_danh_muc = ?, mo_ta = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ten_danh_muc, $mo_ta, $id]);

        echo json_encode(['success' => true, 'message' => 'Cập nhật danh mục thành công!']);
        exit;
    }

    // Xóa danh mục
    if (isset($_POST['xoa_danh_muc'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM danh_muc WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Xóa danh mục thành công!']);
        exit;
    }

    // Lấy thông tin danh mục để sửa
    if (isset($_POST['lay_danh_muc'])) {
        $id = $_POST['id'];
        $sql = "SELECT * FROM danh_muc WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $danh_muc = $stmt->fetch();

        echo json_encode(['success' => true, 'data' => $danh_muc]);
        exit;
    }

    // Xóa bài viết
    if (isset($_POST['xoa_bai_viet'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM bai_chia_se WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Xóa bài viết thành công!']);
        exit;
    }

    // Lấy thông tin bài viết để sửa
    if (isset($_POST['lay_bai_viet'])) {
        $id = $_POST['id'];
        $sql = "SELECT * FROM bai_chia_se WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $bai_viet = $stmt->fetch();

        echo json_encode(['success' => true, 'data' => $bai_viet]);
        exit;
    }

    // Sửa bài viết
    if (isset($_POST['sua_bai_viet'])) {
        $id = $_POST['id'];
        $tieu_de = lam_sach_chuoi($_POST['tieu_de']);
        $mo_ta = lam_sach_chuoi($_POST['mo_ta']);

        $sql = "UPDATE bai_chia_se SET tieu_de = ?, mo_ta = ?, ngay_cap_nhat = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tieu_de, $mo_ta, $id]);

        echo json_encode(['success' => true, 'message' => 'Cập nhật bài viết thành công!']);
        exit;
    }
}

// Lấy danh mục để hiển thị
$search = isset($_GET['search']) ? lam_sach_chuoi($_GET['search']) : '';
$view_category_id = isset($_GET['view']) ? intval($_GET['view']) : null;

$danh_sach_danh_muc = [];
$danh_sach_bai_viet = [];
$category_info = null;

if ($view_category_id) {
    // Lấy thông tin danh mục
    $sql = "SELECT * FROM danh_muc WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$view_category_id]);
    $category_info = $stmt->fetch();

    // Lấy bài viết của danh mục
    $sql = "SELECT b.*, n.ho_ten 
            FROM bai_chia_se b 
            JOIN nguoi_dung n ON b.id_nguoi_dung = n.id 
            WHERE b.id_danh_muc = ? AND b.loai = 'bai_viet' 
            ORDER BY b.ngay_tao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$view_category_id]);
    $danh_sach_bai_viet = $stmt->fetchAll();
} else {
    // Lấy danh mục
    if (!empty($search)) {
        $sql = "SELECT * FROM danh_muc WHERE ten_danh_muc LIKE ? ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['%' . $search . '%']);
    } else {
        $sql = "SELECT * FROM danh_muc ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    $danh_sach_danh_muc = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh Mục</title>
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

        .header-left {
            flex: 1;
        }

        h1 {
            color: #2c3e50;
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        .category-info {
            color: #6c757d;
            font-size: 0.9em;
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

        .btn-info {
            background: #17a2b8;
            color: white;
            padding: 8px 15px;
            font-size: 13px;
        }

        .btn-info:hover {
            background: #138496;
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

        /* Search */
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 200px;
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

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
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
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 500px;
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
                flex-direction: column;
            }

            .search-input {
                width: 100%;
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

            .form-section {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if ($view_category_id): ?>
            <!-- View Bài Viết -->
            <div class="header">
                <div class="header-left">
                    <h1>📝 Bài Viết</h1>
                    <div class="category-info">
                        <strong><?php echo htmlspecialchars($category_info['ten_danh_muc']); ?></strong>
                        <?php if ($category_info['mo_ta']): ?>
                            - <?php echo htmlspecialchars($category_info['mo_ta']); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="index.php?page=adminDanhMuc" class="btn btn-back">← Quay lại</a>
            </div>

            <div id="alertMessage" class="alert"></div>

            <?php if (count($danh_sach_bai_viet) > 0): ?>
                <h2>Danh Sách Bài Viết (<?php echo count($danh_sach_bai_viet); ?>)</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tiêu Đề</th>
                                <th>Mô Tả</th>
                                <th>Tác Giả</th>
                                <th>Ngày Tạo</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($danh_sach_bai_viet as $bai): ?>
                                <tr data-id="<?php echo $bai['id']; ?>">
                                    <td><?php echo $bai['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($bai['tieu_de']); ?></strong></td>
                                    <td><?php echo htmlspecialchars(mb_substr($bai['mo_ta'] ?? '', 0, 50)); ?></td>
                                    <td><?php echo htmlspecialchars($bai['ho_ten']); ?></td>
                                    <td><?php echo dinh_dang_ngay($bai['ngay_tao']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button onclick="moModalSuaBaiViet(<?php echo $bai['id']; ?>)" class="btn btn-warning">Sửa</button>
                                            <button onclick="xoaBaiViet(<?php echo $bai['id']; ?>)" class="btn btn-danger">Xóa</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Chưa có bài viết nào</h3>
                    <p>Danh mục này hiện không có bài viết nào.</p>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- View Danh Mục -->
            <div class="header">
                <h1>📂 Quản Lý Danh Mục</h1>
            </div>

            <div id="alertMessage" class="alert"></div>

            <div class="form-section">
                <h2>Thêm Danh Mục Mới</h2>
                <form id="formThemDanhMuc">
                    <div class="form-group">
                        <label>Tên Danh Mục: <span style="color: red;">*</span></label>
                        <input type="text" name="ten_danh_muc" id="ten_danh_muc" required>
                    </div>

                    <div class="form-group">
                        <label>Mô Tả:</label>
                        <textarea name="mo_ta" id="mo_ta"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Thêm Danh Mục</button>
                </form>
            </div>

            <div class="search-section">
                <form class="search-form" method="GET" action="">
                    <input type="hidden" name="page" value="adminDanhMuc">
                    <input type="text" name="search" class="search-input" placeholder="Tìm kiếm danh mục..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                    <?php if ($search): ?>
                        <a href="index.php?page=adminDanhMuc" class="btn btn-secondary">Xóa bộ lọc</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (count($danh_sach_danh_muc) > 0): ?>
                <h2>Danh Sách Danh Mục (<?php echo count($danh_sach_danh_muc); ?>)</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên Danh Mục</th>
                                <th>Mô Tả</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($danh_sach_danh_muc as $dm): ?>
                                <tr data-id="<?php echo $dm['id']; ?>">
                                    <td><?php echo $dm['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($dm['ten_danh_muc']); ?></strong></td>
                                    <td><?php echo htmlspecialchars(mb_substr($dm['mo_ta'] ?? '', 0, 100)); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="index.php?page=adminBai&view=<?php echo $dm['id']; ?>" class="btn btn-info">Bài Viết</a>
                                            <button onclick="moModalSuaDanhMuc(<?php echo $dm['id']; ?>)" class="btn btn-warning">Sửa</button>
                                            <button onclick="xoaDanhMuc(<?php echo $dm['id']; ?>)" class="btn btn-danger">Xóa</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Chưa có danh mục nào</h3>
                    <p><?php echo $search ? 'Không tìm thấy danh mục phù hợp với từ khóa "' . htmlspecialchars($search) . '"' : 'Bắt đầu bằng cách thêm danh mục mới.'; ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Modal Thêm Danh Mục -->
    <div id="modalThemDanhMuc" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Thêm Danh Mục Mới</h2>
            </div>
            <form id="formThemDanhMucModal">
                <div class="form-group">
                    <label>Tên Danh Mục: <span style="color: red;">*</span></label>
                    <input type="text" name="ten_danh_muc" required>
                </div>

                <div class="form-group">
                    <label>Mô Tả:</label>
                    <textarea name="mo_ta"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="dongModalThemDanhMuc()" class="btn btn-secondary">Hủy</button>
                    <button type="submit" class="btn btn-success">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sửa Danh Mục -->
    <div id="modalSuaDanhMuc" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Sửa Danh Mục</h2>
            </div>
            <form id="formSuaDanhMuc">
                <input type="hidden" name="id" id="sua_id">

                <div class="form-group">
                    <label>Tên Danh Mục: <span style="color: red;">*</span></label>
                    <input type="text" name="ten_danh_muc" id="sua_ten_danh_muc" required>
                </div>

                <div class="form-group">
                    <label>Mô Tả:</label>
                    <textarea name="mo_ta" id="sua_mo_ta"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="dongModalSuaDanhMuc()" class="btn btn-secondary">Hủy</button>
                    <button type="submit" class="btn btn-success">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sửa Bài Viết -->
    <div id="modalSuaBaiViet" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Sửa Bài Viết</h2>
            </div>
            <form id="formSuaBaiViet">
                <input type="hidden" name="id" id="sua_bai_viet_id">

                <div class="form-group">
                    <label>Tiêu Đề: <span style="color: red;">*</span></label>
                    <input type="text" name="tieu_de" id="sua_tieu_de" required>
                </div>

                <div class="form-group">
                    <label>Mô Tả:</label>
                    <textarea name="mo_ta" id="sua_mo_ta_bai"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="dongModalSuaBaiViet()" class="btn btn-secondary">Hủy</button>
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

        // Thêm danh mục
        document.getElementById('formThemDanhMuc').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');
            formData.append('them_danh_muc', '1');

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
                        hienThongBao('Có lỗi xảy ra!', 'danger');
                    }
                })
                .catch(error => hienThongBao('Có lỗi xảy ra!', 'danger'));
        });

        // Mở modal sửa danh mục
        function moModalSuaDanhMuc(id) {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('lay_danh_muc', '1');
            formData.append('id', id);

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('sua_id').value = data.data.id;
                        document.getElementById('sua_ten_danh_muc').value = data.data.ten_danh_muc;
                        document.getElementById('sua_mo_ta').value = data.data.mo_ta || '';
                        document.getElementById('modalSuaDanhMuc').classList.add('show');
                    }
                });
        }

        function dongModalSuaDanhMuc() {
            document.getElementById('modalSuaDanhMuc').classList.remove('show');
        }

        // Sửa danh mục
        document.getElementById('formSuaDanhMuc').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');
            formData.append('sua_danh_muc', '1');

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hienThongBao(data.message, 'success');
                        dongModalSuaDanhMuc();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        hienThongBao('Có lỗi xảy ra!', 'danger');
                    }
                })
                .catch(error => hienThongBao('Có lỗi xảy ra!', 'danger'));
        });

        // Xóa danh mục
        function xoaDanhMuc(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa danh mục này?')) return;

            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('xoa_danh_muc', '1');
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

        // Mở modal sửa bài viết
        function moModalSuaBaiViet(id) {
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
                        document.getElementById('sua_bai_viet_id').value = data.data.id;
                        document.getElementById('sua_tieu_de').value = data.data.tieu_de;
                        document.getElementById('sua_mo_ta_bai').value = data.data.mo_ta || '';
                        document.getElementById('modalSuaBaiViet').classList.add('show');
                    }
                });
        }

        function dongModalSuaBaiViet() {
            document.getElementById('modalSuaBaiViet').classList.remove('show');
        }

        // Sửa bài viết
        document.getElementById('formSuaBaiViet').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');
            formData.append('sua_bai_viet', '1');

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hienThongBao(data.message, 'success');
                        dongModalSuaBaiViet();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        hienThongBao('Có lỗi xảy ra!', 'danger');
                    }
                })
                .catch(error => hienThongBao('Có lỗi xảy ra!', 'danger'));
        });

        // Xóa bài viết
        function xoaBaiViet(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa bài viết này?')) return;

            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('xoa_bai_viet', '1');
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
        document.getElementById('modalSuaDanhMuc').addEventListener('click', function(e) {
            if (e.target === this) dongModalSuaDanhMuc();
        });

        document.getElementById('modalSuaBaiViet').addEventListener('click', function(e) {
            if (e.target === this) dongModalSuaBaiViet();
        });
    </script>
</body>

</html>