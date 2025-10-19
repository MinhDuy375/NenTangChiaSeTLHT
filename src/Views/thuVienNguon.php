<?php
// src/Views/thuVienNguon.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isLoggedIn = $userId > 0;

$isAdmin = $isLoggedIn
    && isset($_SESSION['nguoi_dung']['vai_tro'])
    && $_SESSION['nguoi_dung']['vai_tro'] === 'quan_tri_vien';

include __DIR__ . '/../../config/ketNoiDB.php';

$danh_muc = $pdo->query("SELECT * FROM danh_muc ORDER BY ten_danh_muc")->fetchAll();

$keyword = $_GET['keyword'] ?? '';
$id_danh_muc = $_GET['id_danh_muc'] ?? '';


$sql = "SELECT 
            b.*, 
            u.ten_dang_nhap, 
            d.ten_danh_muc,
            (SELECT COUNT(*) FROM reaction r WHERE r.id_bai_chia_se = b.id) as tong_so_reaction,
            EXISTS(SELECT 1 FROM thu_vien_ca_nhan tv WHERE tv.id_bai_chia_se = b.id AND tv.id_nguoi_dung = :uid) as da_luu
        FROM bai_chia_se b
        LEFT JOIN nguoi_dung u ON b.id_nguoi_dung = u.id
        LEFT JOIN danh_muc d ON b.id_danh_muc = d.id
        WHERE b.loai = 'bai_viet'";

$params = [':uid' => $userId];
if (!empty($keyword)) {
    $sql .= " AND (b.tieu_de LIKE :kw OR b.mo_ta LIKE :kw OR b.cong_nghe LIKE :kw)";
    $params[':kw'] = "%$keyword%";
}
if (!empty($id_danh_muc)) {
    $sql .= " AND b.id_danh_muc = :id_dm";
    $params[':id_dm'] = (int)$id_danh_muc;
}
$sql .= " ORDER BY b.ngay_tao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ds_ma_nguon = $stmt->fetchAll();
$title = "Thư viện nguồn - Sharedy";
ob_start();
?>

<style>
    /* CSS cũ của bạn giữ nguyên... */
    .library-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .library-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px 30px;
        text-align: center;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .library-header h2 {
        font-size: 2.5em;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .library-header p {
        font-size: 1.2em;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .search-section {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .search-form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: end;
    }

    .search-group {
        flex: 1;
        min-width: 200px;
    }

    .search-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .search-input,
    .search-select {
        width: 100%;
        padding: 12px 18px;
        border: 2px solid #e1e5e9;
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    .search-input:focus,
    .search-select:focus {
        outline: none;
        border-color: #007bff;
        background: white;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
    }

    .action-bar {
        display: grid;
        gap: 10px;
        margin-top: 15px;
        --btn-cols: 3;
        grid-template-columns: repeat(var(--btn-cols), 1fr);
    }

    .action-bar.is-guest {
        --btn-cols: 2;
    }

    .btn {
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        color: white;
    }

    .btn:hover {
        transform: translateY(-2px);
        opacity: 0.95;
    }

    .posts-grid {
        display: grid;
        gap: 25px;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }

    .post-card {
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
    }

    .post-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .post-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 18px;
    }

    .post-info h4 {
        margin: 0;
        color: #333;
        font-weight: 600;
    }

    .post-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: #666;
    }

    .category-badge {
        background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        color: #1976d2;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .post-title {
        font-size: 20px;
        font-weight: 700;
        color: #333;
        margin-bottom: 15px;
        line-height: 1.4;
    }

    .post-description {
        color: #666;
        line-height: 1.6;
        margin-bottom: 20px;
        flex-grow: 1;
        /* Giúp đẩy footer xuống */
    }

    .post-footer {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        /* Cho phép xuống dòng nếu không đủ chỗ */
    }

    .tech-badge,
    .reaction-stat {
        display: inline-block;
        background: #f8f9fa;
        color: #495057;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    /* ==================================================================== */
    /* CSS MỚI: Cho các nút hành động và nút lưu */
    /* ==================================================================== */
    .post-actions {
        display: flex;
        gap: 10px;
        margin-top: auto;
    }

    .post-actions .btn-detail {
        flex-grow: 1;
    }

    .save-button {
        flex-shrink: 0;
        padding: 10px;
        width: 44px;
        height: 44px;
        background: #f0f2f5;
        color: #65676b;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    /* --- LOGIC ẨN/HIỆN ICON --- */

    .save-button .icon-solid {
        display: none;
    }

    .save-button .icon-regular {
        display: inline-block;
    }

    .save-button.active {
        background: #ffb3b3ff;
        color: #fa383e;
    }

    .save-button.active .icon-solid {
        display: inline-block;
    }

    .save-button.active .icon-regular {
        display: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .search-form {
            flex-direction: column;
            gap: 10px;
        }

        .action-bar {
            grid-template-columns: repeat(var(--btn-cols), 1fr);
        }

        .posts-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện nguồn - Sharedy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<div class="library-container">
    <div class="library-header">
        <h2>🔧 Thư Viện Nguồn</h2>
        <p>Nơi chia sẻ các dự án, mã nguồn hữu ích cho sinh viên & người mới học lập trình.</p>
    </div>

    <div class="search-section">
        <form method="get" action="index.php" class="search-form">
            <input type="hidden" name="page" value="source">
            <div class="search-group">
                <label for="keyword">🔍 Từ khóa tìm kiếm</label>
                <input type="text" id="keyword" name="keyword" placeholder="Nhập tên dự án, công nghệ, mô tả..." value="<?= htmlspecialchars($keyword) ?>" class="search-input">
            </div>
            <div class="search-group">
                <label for="category">📂 Danh mục</label>
                <select name="id_danh_muc" id="category" class="search-select">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($danh_muc as $dm): ?>
                        <option value="<?= $dm['id'] ?>" <?= $id_danh_muc == $dm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dm['ten_danh_muc']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="action-bar <?= $isAdmin ? '' : 'is-guest' ?>">
                <button type="submit" class="btn btn-primary">🔍 Tìm kiếm</button>
                <?php if ($isAdmin): ?>
                    <a href="index.php?page=them_danh_muc" class="btn btn-primary">➕ Thêm danh mục</a>
                <?php endif; ?>
                <a href="index.php?page=source_upload" class="btn btn-success">📤 Đăng mã nguồn</a>
            </div>
        </form>
    </div>

    <?php if (empty($ds_ma_nguon)): ?>
        <div class="empty-state"> </div>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($ds_ma_nguon as $item): ?>
                <div class="post-card" data-post-id="<?= $item['id'] ?>">
                    <div class="post-header">
                        <div class="avatar"><?= strtoupper(substr($item['ten_dang_nhap'], 0, 1)) ?></div>
                        <div class="post-info">
                            <h4><?= htmlspecialchars($item['ten_dang_nhap']) ?></h4>
                            <div class="post-meta">
                                <span>📅 <?= date('d/m/Y', strtotime($item['ngay_tao'])) ?></span>
                                <span class="category-badge"><?= htmlspecialchars($item['ten_danh_muc']) ?></span>
                            </div>
                        </div>
                    </div>

                    <h3 class="post-title"><?= htmlspecialchars($item['tieu_de']) ?></h3>
                    <div class="post-description">
                        <?= htmlspecialchars(mb_strimwidth($item['mo_ta'], 0, 180, '...')) ?>
                    </div>

                    <div class="post-footer">
                        <?php if (!empty($item['cong_nghe'])): ?>
                            <div class="tech-badge">💻 <?= htmlspecialchars($item['cong_nghe']) ?></div>
                        <?php endif; ?>
                        <?php if ($item['tong_so_reaction'] > 0): ?>
                            <div class="reaction-stat">👍 <?= htmlspecialchars($item['tong_so_reaction']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="post-actions">
                        <a href="index.php?page=source_detail&id=<?= $item['id'] ?>" class="btn btn-primary btn-detail">📖 Chi tiết</a>
                        <?php if ($isLoggedIn):
                        ?>
                            <button class="save-button <?= $item['da_luu'] ? 'active' : '' ?>" title="Lưu bài viết">
                                <i class="fa-regular fa-heart icon-regular"></i>
                                <i class="fa-solid fa-heart icon-solid"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const saveButtons = document.querySelectorAll('.save-button');
        const isLoggedIn = <?= json_encode($isLoggedIn) ?>;

        saveButtons.forEach(button => {
            button.addEventListener('click', async function() {
                if (!isLoggedIn) {
                    alert('Vui lòng đăng nhập để sử dụng chức năng này.');
                    return;
                }

                const card = this.closest('.post-card');
                const postId = card.dataset.postId;

                // Vô hiệu hóa nút tạm thời để tránh click nhiều lần
                this.disabled = true;

                try {
                    const response = await fetch('src/Views/ajax_savepost.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            postId: postId
                        })
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.error || 'Có lỗi xảy ra, vui lòng thử lại.');
                    }

                    const data = await response.json();

                    if (data.success) {
                        this.classList.toggle('active', data.is_saved);
                    } else {
                        alert(data.error);
                    }

                } catch (error) {
                    console.error('Lỗi khi lưu bài viết:', error);
                    alert(error.message);
                } finally {
                    this.disabled = false;
                }
            });
        });
    });
</script>