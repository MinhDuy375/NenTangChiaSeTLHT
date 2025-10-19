<?php
include __DIR__ . '/../../../config/ketNoiDB.php';

// Xử lý AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    // Thêm môn học
    if (isset($_POST['them_mon'])) {
        $ten_mon = lam_sach_chuoi($_POST['ten_mon']);
        $mo_ta = lam_sach_chuoi($_POST['mo_ta']);

        $sql = "INSERT INTO mon_hoc (ten_mon, mo_ta) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ten_mon, $mo_ta]);

        echo json_encode(['success' => true, 'message' => 'Thêm môn học thành công!']);
        exit;
    }

    // Sửa môn học
    if (isset($_POST['sua_mon'])) {
        $id = $_POST['id'];
        $ten_mon = lam_sach_chuoi($_POST['ten_mon']);
        $mo_ta = lam_sach_chuoi($_POST['mo_ta']);

        $sql = "UPDATE mon_hoc SET ten_mon = ?, mo_ta = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ten_mon, $mo_ta, $id]);

        echo json_encode(['success' => true, 'message' => 'Cập nhật môn học thành công!']);
        exit;
    }

    // Xóa môn học
    if (isset($_POST['xoa_mon'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM mon_hoc WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Xóa môn học thành công!']);
        exit;
    }

    // Lấy thông tin môn học để sửa
    if (isset($_POST['lay_mon'])) {
        $id = $_POST['id'];
        $sql = "SELECT * FROM mon_hoc WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $mon = $stmt->fetch();

        echo json_encode(['success' => true, 'data' => $mon]);
        exit;
    }
}

// Lấy danh sách môn học
$sql = "SELECT * FROM mon_hoc ORDER BY id DESC";
$danh_sach_mon = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Môn Học</title>
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

        /* Message Alert */
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
        <div class="header">
            <h1>📚 Quản Lý Môn Học</h1>
            <a href="index.php?page=admin" class="btn btn-back">← Quay lại</a>
        </div>

        <div id="alertMessage" class="alert"></div>

        <div class="form-section">
            <h2>Thêm Môn Học Mới</h2>
            <form id="formThemMon">
                <div class="form-group">
                    <label>Tên Môn Học: <span style="color: red;">*</span></label>
                    <input type="text" name="ten_mon" id="ten_mon" required>
                </div>

                <div class="form-group">
                    <label>Mô Tả:</label>
                    <textarea name="mo_ta" id="mo_ta"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Thêm Môn Học</button>
            </form>
        </div>

        <h2>Danh Sách Môn Học</h2>
        <div class="table-container">
            <table id="tableMon">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Môn Học</th>
                        <th>Mô Tả</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($danh_sach_mon as $mon): ?>
                        <tr data-id="<?php echo $mon['id']; ?>">
                            <td><?php echo $mon['id']; ?></td>
                            <td><strong><?php echo $mon['ten_mon']; ?></strong></td>
                            <td><?php echo $mon['mo_ta']; ?></td>
                            <td>
                                <div class="actions">
                                    <a href="index.php?page=adminDocs&id_mon_hoc=<?php echo $mon['id']; ?>"
                                        class="btn btn-info">Tài Liệu</a>
                                    <button onclick="moModalSua(<?php echo $mon['id']; ?>)"
                                        class="btn btn-warning">Sửa</button>
                                    <button onclick="xoaMon(<?php echo $mon['id']; ?>)"
                                        class="btn btn-danger">Xóa</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Sửa -->
    <div id="modalSua" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Sửa Môn Học</h2>
            </div>
            <form id="formSuaMon">
                <input type="hidden" name="id" id="sua_id">

                <div class="form-group">
                    <label>Tên Môn Học: <span style="color: red;">*</span></label>
                    <input type="text" name="ten_mon" id="sua_ten_mon" required>
                </div>

                <div class="form-group">
                    <label>Mô Tả:</label>
                    <textarea name="mo_ta" id="sua_mo_ta"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="dongModal()" class="btn btn-secondary">Hủy</button>
                    <button type="submit" class="btn btn-success">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Hiển thị thông báo
        function hienThongBao(message, type = 'success') {
            const alert = document.getElementById('alertMessage');
            alert.className = `alert alert-${type} show`;
            alert.textContent = message;

            setTimeout(() => {
                alert.classList.remove('show');
            }, 3000);
        }

        // Thêm môn học
        document.getElementById('formThemMon').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');
            formData.append('them_mon', '1');

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hienThongBao(data.message, 'success');
                        this.reset();
                        taiLaiDanhSach();
                    } else {
                        hienThongBao('Có lỗi xảy ra!', 'danger');
                    }
                })
                .catch(error => {
                    hienThongBao('Có lỗi xảy ra!', 'danger');
                });
        });

        // Mở modal sửa
        function moModalSua(id) {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('lay_mon', '1');
            formData.append('id', id);

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('sua_id').value = data.data.id;
                        document.getElementById('sua_ten_mon').value = data.data.ten_mon;
                        document.getElementById('sua_mo_ta').value = data.data.mo_ta || '';
                        document.getElementById('modalSua').classList.add('show');
                    }
                });
        }

        // Đóng modal
        function dongModal() {
            document.getElementById('modalSua').classList.remove('show');
        }

        // Click outside modal to close
        document.getElementById('modalSua').addEventListener('click', function(e) {
            if (e.target === this) {
                dongModal();
            }
        });

        // Sửa môn học
        document.getElementById('formSuaMon').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('ajax', '1');
            formData.append('sua_mon', '1');

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hienThongBao(data.message, 'success');
                        dongModal();
                        taiLaiDanhSach();
                    } else {
                        hienThongBao('Có lỗi xảy ra!', 'danger');
                    }
                })
                .catch(error => {
                    hienThongBao('Có lỗi xảy ra!', 'danger');
                });
        });

        // Xóa môn học
        function xoaMon(id) {
            if (!confirm('Bạn có chắc muốn xóa môn học này?')) {
                return;
            }

            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('xoa_mon', '1');
            formData.append('id', id);

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hienThongBao(data.message, 'success');
                        taiLaiDanhSach();
                    } else {
                        hienThongBao('Có lỗi xảy ra!', 'danger');
                    }
                })
                .catch(error => {
                    hienThongBao('Có lỗi xảy ra!', 'danger');
                });
        }

        // Tải lại danh sách
        function taiLaiDanhSach() {
            location.reload();
        }
    </script>
</body>

</html>