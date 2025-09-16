<?php
// src/Views/dangTaiNguon.php
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy danh mục
$danh_muc = $pdo->query("SELECT * FROM danh_muc ORDER BY ten_danh_muc")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tieu_de = $_POST['tieu_de'] ?? '';
    $mo_ta = $_POST['mo_ta'] ?? '';
    $id_danh_muc = $_POST['id_danh_muc'] ?? null;
    $link_source = $_POST['link_source'] ?? null;
    $link_host = $_POST['link_host'] ?? null;
    $cong_nghe = $_POST['cong_nghe'] ?? '';
    $id_nguoi_dung = 1; // ✅ user test (sau này thay bằng session)

    $sql = "INSERT INTO bai_chia_se 
        (loai, tieu_de, mo_ta, link_source, link_host, cong_nghe, id_danh_muc, id_nguoi_dung, ngay_tao)
        VALUES ('du_an', :tieu_de, :mo_ta, :link_source, :link_host, :cong_nghe, :id_danh_muc, :id_nguoi_dung, NOW())";

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

    echo "<p style='color:green'>✅ Đăng mã nguồn thành công!</p>";
}
?>

<div class="container">
    <a href="index.php?page=source" class="back-btn">
                ← Quay lại danh sách môn học
            </a>
    <h2>📝 Đăng tải mã nguồn</h2>
    <form method="post">
        <label>Tiêu đề:</label><br>
        <input type="text" name="tieu_de" required class="form-control"><br>

        <label>Mô tả:</label><br>
        <textarea name="mo_ta" required class="form-control"></textarea><br>

        <label>Danh mục:</label><br>
        <select name="id_danh_muc" required class="form-control">
            <?php foreach ($danh_muc as $dm): ?>
                <option value="<?= $dm['id'] ?>"><?= htmlspecialchars($dm['ten_danh_muc']) ?></option>
            <?php endforeach; ?>
        </select><br>

        <label>Công nghệ:</label><br>
        <input type="text" name="cong_nghe" class="form-control"><br>

        <label>Link Source (GitHub):</label><br>
        <input type="url" name="link_source" class="form-control"><br>

        <label>Link Host (nếu có):</label><br>
        <input type="url" name="link_host" class="form-control"><br>

        <button type="submit" class="btn btn-primary">Đăng tải</button>
    </form>
</div>