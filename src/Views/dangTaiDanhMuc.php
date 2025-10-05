<?php
include __DIR__ . '/../../config/ketNoiDB.php';

$thong_bao = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_dm = trim($_POST['ten_danh_muc'] ?? '');
    $mo_ta  = trim($_POST['mo_ta'] ?? '');

    if ($ten_dm === '') {
        $thong_bao = "⚠️ Vui lòng nhập tên danh mục!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO danh_muc (ten_danh_muc, mo_ta) VALUES (:ten_danh_muc, :mo_ta)");
        if ($stmt->execute([':ten_danh_muc' => $ten_dm, ':mo_ta' => $mo_ta])) {
            $thong_bao = "✅ Thêm danh mục thành công!";
        } else {
            $thong_bao = "❌ Lỗi khi thêm danh mục!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng tải danh mục</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="upload-container">
    <div class="hero-section">
        <div class="hero-content">
            <h1>➕ Đăng Tải Danh Mục</h1>
            <p>Tạo danh mục mới để sắp xếp tài liệu / nguồn</p>
        </div>
    </div>

    <div class="form-section">
        <div class="form-card">
            <a href="index.php?page=source" class="back-btn">← Quay lại thư viện nguồn</a>

            <?php if ($thong_bao): ?>
                <div class="thong-bao"><?php echo $thong_bao; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="ten_danh_muc">Tên danh mục <span class="required">*</span></label>
                    <input type="text" id="ten_danh_muc" name="ten_danh_muc" required>
                </div>

                <div class="form-group">
                    <label for="mo_ta">Mô tả</label>
                    <textarea id="mo_ta" name="mo_ta"></textarea>
                </div>

                <button type="submit" class="submit-btn">💾 Lưu danh mục</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
