<?php
include __DIR__ . '/../../config/ketNoiDB.php';

$thong_bao = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_mon = trim($_POST['ten_mon'] ?? '');
    $mo_ta   = trim($_POST['mo_ta'] ?? '');

    if ($ten_mon === '') {
        $thong_bao = "⚠️ Vui lòng nhập tên môn học!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO mon_hoc (ten_mon, mo_ta) VALUES (:ten_mon, :mo_ta)");
        if ($stmt->execute([':ten_mon' => $ten_mon, ':mo_ta' => $mo_ta])) {
            $thong_bao = "✅ Thêm môn học thành công!";
        } else {
            $thong_bao = "❌ Lỗi khi thêm môn học!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng tải môn học</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="upload-container">
    <div class="hero-section">
        <div class="hero-content">
            <h1>➕ Đăng Tải Môn Học</h1>
            <p>Tạo môn học mới để quản lý tài liệu học tập</p>
        </div>
    </div>

    <div class="form-section">
        <div class="form-card">
            <a href="index.php?page=monhoc" class="back-btn">← Quay lại danh sách môn</a>

            <?php if ($thong_bao): ?>
                <div class="thong-bao"><?php echo $thong_bao; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="ten_mon">Tên môn học <span class="required">*</span></label>
                    <input type="text" id="ten_mon" name="ten_mon" required>
                </div>

                <div class="form-group">
                    <label for="mo_ta">Mô tả</label>
                    <textarea id="mo_ta" name="mo_ta"></textarea>
                </div>

                <button type="submit" class="submit-btn">💾 Lưu môn học</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
