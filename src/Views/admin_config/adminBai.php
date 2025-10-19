<?php
include __DIR__ . '/../../../config/ketNoiDB.php';

// Xử lý AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    // Xóa bài viết
    if (isset($_POST['xoa_bai_viet'])) {
        $id = $_POST['id'];

        // Lấy thông tin file trước khi xóa
        $sql = "SELECT file_upload FROM bai_chia_se WHERE id = ? AND loai = 'bai_viet'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $bai = $stmt->fetch();

        if ($bai && $bai['file_upload']) {
            $file_path = __DIR__ . '/../../../' . $bai['file_upload'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Xóa trong database
        $sql = "DELETE FROM bai_chia_se WHERE id = ? AND loai = 'bai_viet'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Xóa bài viết thành công!']);
        exit;
    }

    // Lấy thông tin bài viết để sửa
    if (isset($_POST['lay_bai_viet'])) {
        $id = $_POST['id'];
        $sql = "SELECT * FROM bai_chia_se WHERE id = ? AND loai = 'bai_viet'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $bai = $stmt->fetch();

        echo json_encode(['success' => true, 'data' => $bai]);
        exit;
    }

    // Sửa bài viết
    if (isset($_POST['sua_bai_viet'])) {
        $id = $_POST['id'];
        $tieu_de = lam_sach_chuoi($_POST['tieu_de']);
        $mo_ta = lam_sach_chuoi($_POST['mo_ta']);
        $cong_nghe = lam_sach_chuoi($_POST['cong_nghe']);
        $link_host = lam_sach_chuoi($_POST['link_host']);
        $link_source = lam_sach_chuoi($_POST['link_source']);
        $id_danh_muc = !empty($_POST['id_danh_muc']) ? (int)$_POST['id_danh_muc'] : null;

        $sql = "UPDATE bai_chia_se SET tieu_de = ?, mo_ta = ?, cong_nghe = ?, link_host = ?, link_source = ?, id_danh_muc = ?, ngay_cap_nhat = NOW() WHERE id = ? AND loai = 'bai_viet'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tieu_de, $mo_ta, $cong_nghe, $link_host, $link_source, $id_danh_muc, $id]);

        echo json_encode(['success' => true, 'message' => 'Cập nhật bài viết thành công!']);
        exit;
    }
}

// Lấy danh sách bài viết
$search = isset($_GET['search']) ? lam_sach_chuoi($_GET['search']) : '';
$filter_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

if (!empty($search) || $filter_category > 0) {
    $sql = "SELECT b.*, n.ho_ten, d.ten_danh_muc
            FROM bai_chia_se b
            JOIN nguoi_dung n ON b.id_nguoi_dung = n.id
            LEFT JOIN danh_muc d ON b.id_danh_muc = d.id
            WHERE b.loai = 'bai_viet'";

    if (!empty($search)) {
        $sql .= " AND (b.tieu_de LIKE ? OR b.mo_ta LIKE ? OR b.cong_nghe LIKE ?)";
    }

    if ($filter_category > 0) {
        $sql .= " AND b.id_danh_muc = ?";
    }

    $sql .= " ORDER BY b.ngay_tao DESC";

    $stmt = $pdo->prepare($sql);

    $params = [];
    if (!empty($search)) {
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    if ($filter_category > 0) {
        $params[] = $filter_category;
    }

    $stmt->execute($params);
} else {
    $sql = "SELECT b.*, n.ho_ten, d.ten_danh_muc
            FROM bai_chia_se b
            JOIN nguoi_dung n ON b.id_nguoi_dung = n.id
            LEFT JOIN danh_muc d ON b.id_danh_muc = d.id
            WHERE b.loai = 'bai_viet'
            ORDER BY b.ngay_tao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

$danh_sach_bai_viet = $stmt->fetchAll();

// Lấy danh sách danh mục cho bộ lọc
$sql_categories = "SELECT * FROM danh_muc ORDER BY ten_danh_muc ASC";
$categories = $pdo->query($sql_categories)->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bài Viết</title>
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

        /* Search & Filter */
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr 200px;
            gap: 10px;
            flex-wrap: wrap;
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

        .filter-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .filter-group select {
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
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

        .category-badge {
            display: inline-block;
            background: #e7f3ff;
            color: #0056b3;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
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
        textarea,
        select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
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

            .search-section {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📝 Quản Lý Bài Viết</h1>
            <a href="index.php?page=admin" class="btn btn-back">← Quay lại</a>
        </div>

        <div id="alertMessage" class="alert"></div>

        <div class="search-section">
            <form class="search-form" method="GET" action="">
                <div>
                    <input type="hidden" name="page" value="adminBai">
                    <input type="text" name="search" class="search-input" placeholder="Tìm kiếm theo tiêu đề, mô tả, công nghệ..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </form>

            <div class="filter-group">
                <select name="category" id="filterCategory" onchange="filterByCategory()">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($search || $filter_category > 0): ?>
                    <a href="index.php?page=adminBai" class="btn btn-secondary">Xóa bộ lọc</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (count($danh_sach_bai_viet) > 0): ?>
            <h2>Danh Sách Bài Viết (<?php echo count($danh_sach_bai_viet); ?>)</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tiêu Đề</th>
                            <th>Danh Mục</th>
                            <th>Công Nghệ</th>
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
                                <td>
                                    <?php if ($bai['ten_danh_muc']): ?>
                                        <span class="category-badge"><?php echo htmlspecialchars($bai['ten_danh_muc']); ?></span>
                                    <?php else: ?>
                                        <span style="color: #6c757d;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($bai['cong_nghe'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($bai['ho_ten']); ?></td>
                                <td><?php echo dinh_dang_ngay($bai['ngay_tao']); ?></td>
                                <td>
                                    <div class="actions">
                                        <button onclick="moModalSua(<?php echo $bai['id']; ?>)" class="btn btn-warning">Sửa</button>
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
                <p><?php echo $search ? 'Không tìm thấy bài viết phù hợp với từ khóa "' . htmlspecialchars($search) . '"' : 'Hệ thống chưa có bài viết nào.'; ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Sửa Bài Viết -->
    <div id="modalSua" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Sửa Bài Viết</h2>
            </div>
            <form id="formSua">
                <input type="hidden" name="id" id="sua_id">

                <div class="form-group">
                    <label>Tiêu Đề: <span style="color: red;">*</span></label>
                    <input type="text" name="tieu_de" id="sua_tieu_de" required>
                </div>

                <div class="form-group">
                    <label>Danh Mục:</label>
                    <select name="id_danh_muc" id="sua_id_danh_muc">
                        <option value="">Không chọn danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mô Tả:</label>
                    <textarea name="mo_ta" id="sua_mo_ta"></textarea>
                </div>

                <div class="form-group">
                    <label>Công Nghệ:</label>
                    <input type="text" name="cong_nghe" id="sua_cong_nghe" placeholder="VD: PHP, Laravel, MySQL">
                </div>

                <div class="form-group">
                    <label>Link Hosting:</label>
                    <input type="text" name="link_host" id="sua_link_host" placeholder="VD: https://example.com">
                </div>

                <div class="form-group">
                    <label>Link Source:</label>
                    <input type="text" name="link_source" id="sua_link_source" placeholder="VD: https://github.com/...">
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
                        document.getElementById('sua_id_danh_muc').value = data.data.id_danh_muc || '';
                        document.getElementById('sua_mo_ta').value = data.data.mo_ta || '';
                        document.getElementById('sua_cong_nghe').value = data.data.cong_nghe || '';
                        document.getElementById('sua_link_host').value = data.data.link_host || '';
                        document.getElementById('sua_link_source').value = data.data.link_source || '';
                        document.getElementById('modalSua').classList.add('show');
                    }
                });
        }

        function dongModal() {
            document.getElementById('modalSua').classList.remove('show');
        }

        document.getElementById('formSua').addEventListener('submit', function(e) {
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
                        dongModal();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        hienThongBao('Có lỗi xảy ra!', 'danger');
                    }
                })
                .catch(error => hienThongBao('Có lỗi xảy ra!', 'danger'));
        });

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

        function filterByCategory() {
            const category = document.getElementById('filterCategory').value;
            if (category) {
                window.location.href = `index.php?page=adminBai&category=${category}`;
            }
        }

        document.getElementById('modalSua').addEventListener('click', function(e) {
            if (e.target === this) dongModal();
        });
    </script>
</body>

</html>