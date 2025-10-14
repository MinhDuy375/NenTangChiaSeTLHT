<?php
// thuVienCaNhan.php - Trang thư viện cá nhân
include __DIR__ . '/../../config/ketNoiDB.php';
include __DIR__ . '/checkLogin.php'; // Thêm dòng này

// Kiểm tra đăng nhập (bắt buộc)
kiem_tra_dang_nhap(true);
// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$id_nguoi_dung = $_SESSION['user_id'];

// Lấy danh sách tài liệu đã lưu
try {
    $sql = "SELECT bcs.*, mh.ten_mon, nd.ho_ten as ten_nguoi_dang,
            tv.ngay_tao as ngay_luu
            FROM thu_vien_ca_nhan tv
            JOIN bai_chia_se bcs ON tv.id_bai_chia_se = bcs.id
            LEFT JOIN mon_hoc mh ON bcs.id_mon_hoc = mh.id
            LEFT JOIN nguoi_dung nd ON bcs.id_nguoi_dung = nd.id
            WHERE tv.id_nguoi_dung = :id_nguoi_dung
            ORDER BY tv.ngay_tao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_nguoi_dung' => $id_nguoi_dung]);
    $tai_lieu_da_luu = $stmt->fetchAll();
} catch (PDOException $e) {
    $tai_lieu_da_luu = [];
    $loi = "Không thể tải danh sách: " . $e->getMessage();
}

// Hàm lấy icon theo loại file
function lay_icon_file($duong_dan_file)
{
    $duoi_file = strtolower(pathinfo($duong_dan_file, PATHINFO_EXTENSION));
    switch ($duoi_file) {
        case 'pdf':
            return '📄';
        case 'doc':
        case 'docx':
            return '📝';
        default:
            return '📎';
    }
}

// Hàm tính kích thước file
function tinh_kich_thuoc_file($duong_dan_file)
{
    if (file_exists($duong_dan_file)) {
        $kich_thuoc = filesize($duong_dan_file);
        if ($kich_thuoc >= 1048576) {
            return round($kich_thuoc / 1048576, 2) . ' MB';
        } elseif ($kich_thuoc >= 1024) {
            return round($kich_thuoc / 1024, 2) . ' KB';
        }
        return $kich_thuoc . ' bytes';
    }
    return 'Không xác định';
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện của tôi - Sharedy</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            color: white;
            padding: 30px;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }

        .search-box {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 25px;
            font-size: 16px;
            margin-bottom: 25px;
            transition: all 0.3s;
        }

        .search-box:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .tai-lieu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .tai-lieu-card {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .tai-lieu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #007bff;
        }

        .tai-lieu-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 20px;
        }

        .file-icon {
            font-size: 2em;
            margin-bottom: 10px;
            display: block;
        }

        .tai-lieu-title {
            font-size: 1.2em;
            font-weight: 600;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .tai-lieu-meta {
            font-size: 0.85em;
            opacity: 0.9;
        }

        .tai-lieu-body {
            padding: 20px;
        }

        .tai-lieu-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 0.9em;
            color: #6c757d;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tai-lieu-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #6c757d;
        }

        .empty-state h3 {
            font-size: 1.8em;
            margin-bottom: 15px;
        }

        .back-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background: #5a6268;
            transform: translateX(-3px);
        }

        .saved-date {
            color: #28a745;
            font-size: 0.85em;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .tai-lieu-grid {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>😻 Yêu thích</h1>
            <p>Quản lý tài liệu yêu thích của bạn</p>
        </div>

        <div class="content">
            <a href="index.php?page=monhoc" class="back-btn">
                ← Quay lại danh sách môn học
            </a>

            <?php if (isset($loi)): ?>
                <div class="alert alert-danger"><?php echo $loi; ?></div>
            <?php endif; ?>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($tai_lieu_da_luu); ?></div>
                    <div class="stat-label">Tài liệu đã lưu</div>
                </div>
            </div>

            <?php if (empty($tai_lieu_da_luu)): ?>
                <div class="empty-state">
                    <h3>📭 Chưa có tài liệu nào</h3>
                    <p>Bạn chưa lưu tài liệu nào. Hãy khám phá và lưu các tài liệu yêu thích!</p>
                    <a href="index.php?page=monhoc" class="btn btn-primary" style="margin-top: 20px;">
                        🔍 Khám phá tài liệu
                    </a>
                </div>
            <?php else: ?>
                <input type="text"
                    class="search-box"
                    id="search-input"
                    placeholder="🔍 Tìm kiếm trong thư viện...">

                <div class="tai-lieu-grid" id="tai-lieu-container">
                    <?php foreach ($tai_lieu_da_luu as $tai_lieu): ?>
                        <div class="tai-lieu-card" data-title="<?php echo strtolower(lam_sach_chuoi($tai_lieu['tieu_de'])); ?>">
                            <div class="tai-lieu-header">
                                <span class="file-icon"><?php echo lay_icon_file($tai_lieu['file_upload']); ?></span>
                                <div class="tai-lieu-title"><?php echo lam_sach_chuoi($tai_lieu['tieu_de']); ?></div>
                                <div class="tai-lieu-meta">
                                    📅 <?php echo dinh_dang_ngay($tai_lieu['ngay_tao']); ?>
                                    <?php if (!empty($tai_lieu['ten_nguoi_dang'])): ?>
                                        | 👤 <?php echo lam_sach_chuoi($tai_lieu['ten_nguoi_dang']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="tai-lieu-body">
                                <?php if (!empty($tai_lieu['tom_tat'])): ?>
                                    <div style="color: #6c757d; margin-bottom: 15px; line-height: 1.6;">
                                        <?php echo lam_sach_chuoi(substr($tai_lieu['tom_tat'], 0, 150)); ?>...
                                    </div>
                                <?php endif; ?>

                                <div class="tai-lieu-info">
                                    <div class="info-item">
                                        📖 <?php echo lam_sach_chuoi($tai_lieu['ten_mon']); ?>
                                    </div>
                                    <div class="info-item">
                                        💾 <?php echo tinh_kich_thuoc_file($tai_lieu['file_upload']); ?>
                                    </div>
                                </div>

                                <div class="saved-date">
                                    ✅ Đã lưu: <?php echo dinh_dang_ngay($tai_lieu['ngay_luu']); ?>
                                </div>

                                <div class="tai-lieu-actions" style="margin-top: 15px;">
                                    <a href="index.php?page=chitiettailieu&id=<?php echo $tai_lieu['id']; ?>"
                                        class="btn btn-primary">
                                        👁️ Xem Chi Tiết
                                    </a>
                                    <a href="<?php echo $tai_lieu['file_upload']; ?>"
                                        class="btn btn-success"
                                        download target="_blank">
                                        📥 Tải Xuống
                                    </a>
                                    <button class="btn btn-danger"
                                        onclick="xoaKhoiThuVien(<?php echo $tai_lieu['id']; ?>, this)">
                                        🗑️ Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Tìm kiếm
        document.getElementById('search-input')?.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.tai-lieu-card');

            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                card.style.display = title.includes(searchTerm) ? 'block' : 'none';
            });
        });

        // Xóa khỏi thư viện
        function xoaKhoiThuVien(postId, btn) {
            if (!confirm('Bạn có chắc muốn xóa tài liệu này khỏi thư viện?')) return;

            btn.disabled = true;
            btn.innerHTML = '⏳ Đang xóa...';

            fetch('src/Views/save_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'post_id=' + postId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'removed') {
                        btn.closest('.tai-lieu-card').remove();

                        // Cập nhật số lượng
                        const remaining = document.querySelectorAll('.tai-lieu-card').length;
                        document.querySelector('.stat-number').textContent = remaining;

                        if (remaining === 0) {
                            location.reload();
                        }
                    } else {
                        alert('❌ ' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = '🗑️ Xóa';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('⚠️ Không kết nối được server');
                    btn.disabled = false;
                    btn.innerHTML = '🗑️ Xóa';
                });
        }
    </script>
</body>

</html>