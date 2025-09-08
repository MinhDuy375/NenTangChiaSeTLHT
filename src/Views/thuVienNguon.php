<?php
// src/Views/thuVienNguon.php
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy danh mục
$danh_muc = $pdo->query("SELECT * FROM danh_muc ORDER BY ten_danh_muc")->fetchAll();

// Lọc dữ liệu
$keyword = $_GET['keyword'] ?? '';
$id_danh_muc = $_GET['id_danh_muc'] ?? '';

$sql = "SELECT b.*, u.ten_dang_nhap, d.ten_danh_muc
        FROM bai_chia_se b
        LEFT JOIN nguoi_dung u ON b.id_nguoi_dung = u.id
        LEFT JOIN danh_muc d ON b.id_danh_muc = d.id
        WHERE b.loai = 'du_an'";

$params = [];
if (!empty($keyword)) {
    $sql .= " AND (b.tieu_de LIKE :kw OR b.mo_ta LIKE :kw OR b.cong_nghe LIKE :kw)";
    $params['kw'] = "%$keyword%";
}
if (!empty($id_danh_muc)) {
    $sql .= " AND b.id_danh_muc = :id_dm";
    $params['id_dm'] = $id_danh_muc;
}
$sql .= " ORDER BY b.ngay_tao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ds_ma_nguon = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện nguồn</title>
    <style>
        * { margin:0; padding:0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { margin: 0 auto; background:white; box-shadow:0 10px 30px rgba(0,0,0,0.2); overflow:hidden; }
        .header { background: linear-gradient(45deg,#2196F3,#21CBF3); color:white; padding:15px; text-align:center; }
        .header h2 { font-size:2em; margin-bottom:10px; }
        .header p { font-size:1.1em; opacity:0.9; }

        .content { padding:30px; }
        .search-container { margin-bottom:30px; }
        .search-box {
            padding:12px 20px;
            border:2px solid #e1e5e9;
            border-radius:25px;
            font-size:16px;
            transition: all 0.3s;
        }
        .search-box:focus { outline:none; border-color:#007bff; box-shadow:0 0 0 3px rgba(0,123,255,0.25); }

        .mon-hoc-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(300px,1fr)); gap:25px; margin-top:20px; }
        .mon-hoc-card { background:white; border:1px solid #e1e5e9; box-shadow:0 2px 10px rgba(0,0,0,0.08); cursor:pointer; }
        .mon-hoc-card:hover { border-color:#007bff; }
        .mon-hoc-header { background: linear-gradient(45deg,#007bff,#0056b3); color:white; padding:20px; }
        .mon-hoc-title { font-size:1em; font-weight:600; margin-bottom:5px; }
        .mon-hoc-count { font-size:0.9em; opacity:0.9; }

        .mon-hoc-body { padding:20px; }
        .mon-hoc-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:10px; }

        .btn { padding:8px 16px; border:none; border-radius:6px; text-decoration:none; font-size:14px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all .3s; }
        .btn-primary { background:#007bff; color:white; }
        .btn-primary:hover { background:#0056b3; }
        .btn-outline { border:1px solid #007bff; color:#007bff; background:transparent; }
        .btn-outline:hover { background:#007bff; color:white; }
        .btn-success { background:#28a745; color:white; }
        .btn-success:hover { background:#1e7e34; }

        .empty-state { text-align:center; padding:60px 20px; color:#6c757d; }
        .empty-state h3 { font-size:1.5em; margin-bottom:15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Thư Viện Nguồn</h2>
            <p>Nơi chia sẻ các dự án, mã nguồn hữu ích cho sinh viên & người mới học lập trình</p>
        </div>

        <div class="content">
            <!-- Form tìm kiếm -->
            <div class="search-container">
                <form method="get" action="index.php" style="display:flex; gap:10px; flex-wrap:wrap;">
                    <input type="hidden" name="page" value="thuVienNguon">
                    <input type="text" name="keyword" placeholder="🔍 Tìm mã nguồn..." value="<?= htmlspecialchars($keyword) ?>" class="search-box">
                    <select name="id_danh_muc" class="search-box">
                        <option value="">--Danh mục--</option>
                        <?php foreach ($danh_muc as $dm): ?>
                            <option value="<?= $dm['id'] ?>" <?= $id_danh_muc == $dm['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dm['ten_danh_muc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">🔍 Tìm</button>
                    <a href="index.php?page=dangTaiNguon" class="btn btn-success">➕ Đăng mã nguồn</a>
                </form>
            </div>

            <!-- Danh sách mã nguồn -->
            <?php if (empty($ds_ma_nguon)): ?>
                <div class="empty-state">
                    <h3>📭 Chưa có mã nguồn nào</h3>
                    <p>Hãy là người đầu tiên chia sẻ mã nguồn hữu ích!</p>
                    <a href="index.php?page=dangTaiNguon" class="btn btn-primary">➕ Đăng mã nguồn</a>
                </div>
            <?php else: ?>
                <div class="mon-hoc-grid">
                    <?php foreach ($ds_ma_nguon as $item): ?>
                        <div class="mon-hoc-card">
                            <div class="mon-hoc-header">
                                <div class="mon-hoc-title"><?= htmlspecialchars($item['tieu_de']) ?></div>
                                <div class="mon-hoc-count">
                                    <?= htmlspecialchars($item['ten_danh_muc']) ?> | <?= htmlspecialchars($item['cong_nghe']) ?>
                                </div>
                            </div>
                            <div class="mon-hoc-body">
                                <p><?= htmlspecialchars($item['mo_ta']) ?></p>
                                <p><b>👤</b> <?= htmlspecialchars($item['ten_dang_nhap']) ?> |
                                   <b>📅</b> <?= $item['ngay_tao'] ?></p>
                                <div class="mon-hoc-actions">
                                    <?php if (!empty($item['link_host'])): ?>
                                        <a href="<?= $item['link_host'] ?>" target="_blank" class="btn btn-outline">🌐 Host</a>
                                    <?php endif; ?>
                                    <?php if (!empty($item['link_source'])): ?>
                                        <a href="<?= $item['link_source'] ?>" target="_blank" class="btn btn-primary">💻 Source</a>
                                    <?php endif; ?>
                                    <a href="index.php?page=chiTietNguon&id=<?= $item['id'] ?>" class="btn btn-outline">🔍 Xem chi tiết</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
