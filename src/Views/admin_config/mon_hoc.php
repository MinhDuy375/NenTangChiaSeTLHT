<?php
include __DIR__ . '/../../../config/ketNoiDB.php';

// Xử lý thêm môn học
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['them_mon'])) {
    $ten_mon = lam_sach_chuoi($_POST['ten_mon']);
    $mo_ta = lam_sach_chuoi($_POST['mo_ta']);

    $sql = "INSERT INTO mon_hoc (ten_mon, mo_ta) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ten_mon, $mo_ta]);

    $_SESSION['thong_bao'] = "Thêm môn học thành công!";
    header("Location: mon_hoc.php");
    exit;
}

// Xử lý sửa môn học
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sua_mon'])) {
    $id = $_POST['id'];
    $ten_mon = lam_sach_chuoi($_POST['ten_mon']);
    $mo_ta = lam_sach_chuoi($_POST['mo_ta']);

    $sql = "UPDATE mon_hoc SET ten_mon = ?, mo_ta = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ten_mon, $mo_ta, $id]);

    $_SESSION['thong_bao'] = "Cập nhật môn học thành công!";
    header("Location: mon_hoc.php");
    exit;
}

// Xử lý xóa môn học
if (isset($_GET['xoa']) && is_numeric($_GET['xoa'])) {
    $id = $_GET['xoa'];
    $sql = "DELETE FROM mon_hoc WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $_SESSION['thong_bao'] = "Xóa môn học thành công!";
    header("Location: mon_hoc.php");
    exit;
}

// Lấy danh sách môn học
$sql = "SELECT * FROM mon_hoc ORDER BY id DESC";
$danh_sach_mon = $pdo->query($sql)->fetchAll();

// Lấy thông tin môn học để sửa
$mon_sua = null;
if (isset($_GET['sua']) && is_numeric($_GET['sua'])) {
    $id = $_GET['sua'];
    $sql = "SELECT * FROM mon_hoc WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $mon_sua = $stmt->fetch();
}
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;

        }

        .container {
            max-width: 1400px;
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
            border-bottom: 3px solid #667eea;
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

        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .form-section h2 {
            color: #667eea;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        textarea {
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
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
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
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>📚 Quản Lý Môn Học</h1>
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

        <div class="form-section">
            <h2><?php echo $mon_sua ? 'Sửa Môn Học' : 'Thêm Môn Học Mới'; ?></h2>
            <form method="POST">
                <?php if ($mon_sua): ?>
                    <input type="hidden" name="id" value="<?php echo $mon_sua['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Tên Môn Học:</label>
                    <input type="text" name="ten_mon" required
                        value="<?php echo $mon_sua ? $mon_sua['ten_mon'] : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Mô Tả:</label>
                    <textarea name="mo_ta"><?php echo $mon_sua ? $mon_sua['mo_ta'] : ''; ?></textarea>
                </div>

                <button type="submit" name="<?php echo $mon_sua ? 'sua_mon' : 'them_mon'; ?>"
                    class="btn <?php echo $mon_sua ? 'btn-success' : 'btn-primary'; ?>">
                    <?php echo $mon_sua ? 'Cập Nhật' : 'Thêm Môn Học'; ?>
                </button>

                <?php if ($mon_sua): ?>
                    <a href="index.php?page=adminMon" class="cancel-btn">Hủy</a>
                <?php endif; ?>
            </form>
        </div>

        <h2>Danh Sách Môn Học</h2>
        <table>
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
                    <tr>
                        <td><?php echo $mon['id']; ?></td>
                        <td><strong><?php echo $mon['ten_mon']; ?></strong></td>
                        <td><?php echo $mon['mo_ta']; ?></td>
                        <td>
                            <div class="actions">
                                <a href="page=tailieumon&id_mon_hoc=<?php echo $mon['id']; ?>"
                                    class="btn btn-info">Tài Liệu</a>
                                <a href="index.php?page=adminMon?sua=<?php echo $mon['id']; ?>"
                                    class="btn btn-warning">Sửa</a>
                                <a href="index.php?page=adminMon?xoa=<?php echo $mon['id']; ?>"
                                    class="btn btn-danger"
                                    onclick="return confirm('Bạn có chắc muốn xóa môn học này?')">Xóa</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>