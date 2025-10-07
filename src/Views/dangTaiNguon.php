<?php
// src/Views/dangTaiNguon.php
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy danh mục
$danh_muc = $pdo->query("SELECT * FROM danh_muc ORDER BY ten_danh_muc")->fetchAll();

$thong_bao = '';
$loai_thong_bao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dang_tai_nguon'])) {
    // Làm sạch dữ liệu
    $tieu_de = htmlspecialchars(trim($_POST['tieu_de'] ?? ''));
    $mo_ta = htmlspecialchars(trim($_POST['mo_ta'] ?? ''));
    $id_danh_muc = $_POST['id_danh_muc'] ?? null;
    $link_source = htmlspecialchars(trim($_POST['link_source'] ?? ''));
    $link_host = htmlspecialchars(trim($_POST['link_host'] ?? ''));
    $cong_nghe = htmlspecialchars(trim($_POST['cong_nghe'] ?? ''));
    $id_nguoi_dung = 1; // tạm thời (sau thay bằng session login)

    if (empty($tieu_de) || empty($mo_ta) || empty($id_danh_muc) || empty($link_source) || empty($cong_nghe)) {
        $thong_bao = "⚠️ Vui lòng nhập đầy đủ các trường bắt buộc!";
        $loai_thong_bao = "loi";
    } else {
        try {
            $sql = "INSERT INTO bai_chia_se 
                (loai, tieu_de, mo_ta, link_source, link_host, cong_nghe, id_danh_muc, id_nguoi_dung, ngay_tao)
                VALUES ('bai_viet', :tieu_de, :mo_ta, :link_source, :link_host, :cong_nghe, :id_danh_muc, :id_nguoi_dung, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'tieu_de' => $tieu_de,
                'mo_ta' => $mo_ta,
                'link_source' => $link_source,
                'link_host' => $link_host,
                'cong_nghe' => $cong_nghe,
                'id_danh_muc' => $id_danh_muc,
                'id_nguoi_dung' => $id_nguoi_dung
            ]);

            $thong_bao = "✅ Đăng mã nguồn thành công!";
            $loai_thong_bao = "thanh-cong";
        } catch (PDOException $e) {
            $thong_bao = "❌ Lỗi CSDL: " . $e->getMessage();
            $loai_thong_bao = "loi";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng Tải Nguồn</title>
    <link rel="stylesheet" href="style.css"> <!-- nhớ chỉnh lại path -->
</head>

<body>
    <div class="upload-container">
        <div class="hero-section">
            <div class="hero-content">
                <h1>🚀 Đăng Tải Nguồn</h1>
                <p>Chia sẻ mã nguồn, dự án để cộng đồng học tập và phát triển</p>
            </div>
        </div>

        <div class="form-section">
            <div class="form-card">
                <a href="index.php?page=source" class="back-btn">← Quay lại thư viện nguồn</a>

                <?php if (!empty($thong_bao)): ?>
                    <div class="thong-bao <?= $loai_thong_bao ?>">
                        <?= $thong_bao ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label for="tieu_de">📝 Tiêu đề <span class="required">*</span></label>
                        <input type="text" id="tieu_de" name="tieu_de" required>
                    </div>

                    <div class="form-group">
                        <label for="mo_ta">📄 Mô tả chi tiết <span class="required">*</span></label>
                        <textarea id="mo_ta" name="mo_ta" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="id_danh_muc">📂 Danh mục <span class="required">*</span></label>
                        <select id="id_danh_muc" name="id_danh_muc" required>
                            <?php foreach ($danh_muc as $dm): ?>
                                <option value="<?= $dm['id'] ?>"><?= htmlspecialchars($dm['ten_danh_muc']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cong_nghe">⚙️ Loại<span class="required">*</span></label>
                        <input type="text" id="cong_nghe" name="cong_nghe" placeholder="Ví dụ: PHP, MySQL, NodeJS..." required>
                    </div>

                    <div class="form-group">
                        <label for="link_source">💻 Link Source (GitHub) <span class="required">*</span></label>
                        <input type="url" id="link_source" name="link_source" required>
                    </div>

                    <div class="form-group">
                        <label for="link_host">🌐 Link Demo (nếu có)</label>
                        <input type="url" id="link_host" name="link_host">
                    </div>

                    <button type="submit" name="dang_tai_nguon" class="submit-btn">🚀 Đăng Tải Nguồn</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>