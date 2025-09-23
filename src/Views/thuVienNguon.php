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

// Thiết lập title cho trang
$title = "Thư viện nguồn - Sharedy";

// Bắt đầu buffer để capture nội dung
ob_start();
?>

<style>
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
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
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
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    
    .search-form {
        display: flex;
        gap: 15px;
        align-items: end;
        flex-wrap: wrap;
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
    
    .search-input, .search-select {
        width: 100%;
        padding: 12px 18px;
        border: 2px solid #e1e5e9;
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s ease;
        background: #fafafa;
    }
    
    .search-input:focus, .search-select:focus {
        outline: none;
        border-color: #007bff;
        background: white;
        box-shadow: 0 0 0 4px rgba(0,123,255,0.1);
    }
    
    .search-buttons {
        display: flex;
        gap: 10px;
        align-items: end;
    }
    
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,123,255,0.3);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        color: white;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40,167,69,0.3);
    }

    .posts-grid {
        display: grid;
        gap: 25px;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }
    
    .post-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
        position: relative;
        overflow: hidden;
    }
    
    .post-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .post-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }

    .post-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f5f5f5;
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
        flex-shrink: 0;
    }

    .post-info h4 {
        margin: 0;
        color: #333;
        font-size: 16px;
        font-weight: 600;
    }

    .post-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 5px;
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
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .post-description {
        color: #666;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .tech-badge {
        display: inline-block;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        color: #495057;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .post-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 20px;
        border-top: 1px solid #f5f5f5;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
    }

    .action-btn {
        background: none;
        border: none;
        color: #666;
        font-size: 14px;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
    }

    .action-btn:hover {
        background: #f0f0f0;
        color: #333;
    }

    .action-btn.liked {
        color: #e74c3c;
        background: #fdf2f2;
    }

    .view-detail-btn {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .view-detail-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0,123,255,0.3);
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .empty-state h3 {
        font-size: 24px;
        color: #666;
        margin-bottom: 15px;
    }

    .empty-state p {
        color: #888;
        font-size: 16px;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .library-container {
            padding: 15px;
        }
        
        .library-header {
            padding: 25px 20px;
            margin-bottom: 20px;
        }
        
        .library-header h2 {
            font-size: 2em;
        }
        
        .search-form {
            flex-direction: column;
            gap: 20px;
        }
        
        .search-buttons {
            justify-content: center;
        }
        
        .posts-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .post-card {
            padding: 20px;
        }
        
        .post-actions {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }
        
        .view-detail-btn {
            text-align: center;
        }
    }
</style>

<div class="library-container">
    <div class="library-header">
        <h2>🔧 Thư Viện Nguồn</h2>
        <p>Nơi chia sẻ các dự án, mã nguồn hữu ích cho sinh viên & người mới học lập trình. Khám phá, học hỏi và đóng góp cho cộng đồng!</p>
    </div>

    <div class="search-section">
        <form method="get" action="index.php" class="search-form">
            <input type="hidden" name="page" value="source">
            
            <div class="search-group">
                <label for="keyword">🔍 Từ khóa tìm kiếm</label>
                <input type="text" 
                       id="keyword"
                       name="keyword" 
                       placeholder="Nhập tên dự án, công nghệ, mô tả..." 
                       value="<?= htmlspecialchars($keyword) ?>" 
                       class="search-input">
            </div>
            
            <div class="search-group">
                <label for="category">📂 Danh mục</label>
                <select name="id_danh_muc" id="category" class="search-select">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($danh_muc as $dm): ?>
                        <option value="<?= $dm['id'] ?>" <?= $id_danh_muc == $dm['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dm['ten_danh_muc']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="search-buttons">
                <button type="submit" class="btn btn-primary">
                    🔍 Tìm kiếm
                </button>
                <a href="index.php?page=source_upload" class="btn btn-success">
                    ➕ Đăng mã nguồn
                </a>
            </div>
        </form>
    </div>

    <?php if (empty($ds_ma_nguon)): ?>
        <div class="empty-state">
            <h3>🔍 Không tìm thấy mã nguồn</h3>
            <p>Hiện tại chưa có mã nguồn nào phù hợp với từ khóa tìm kiếm của bạn.<br>
               Hãy thử tìm kiếm với từ khóa khác hoặc trở thành người đầu tiên chia sẻ!</p>
            <a href="index.php?page=source_upload" class="btn btn-primary">
                ➕ Đăng mã nguồn đầu tiên
            </a>
        </div>
    <?php else: ?>
        <div class="posts-grid">
            <?php foreach ($ds_ma_nguon as $item): ?>
                <div class="post-card">
                    <div class="post-header">
                        <div class="avatar">
                            <?= strtoupper(substr($item['ten_dang_nhap'], 0, 1)) ?>
                        </div>
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
                        <?php 
                        $mo_ta = htmlspecialchars($item['mo_ta']);
                        if (strlen($mo_ta) > 200) {
                            $mo_ta = substr($mo_ta, 0, 200) . '...';
                        }
                        echo $mo_ta;
                        ?>
                    </div>
                    
                    <?php if (!empty($item['cong_nghe'])): ?>
                        <div class="tech-badge">
                            💻 <?= htmlspecialchars($item['cong_nghe']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="post-actions">
                        <div class="action-buttons">
                            <button class="action-btn" onclick="toggleLike(this, <?= $item['id'] ?>)">
                                👍 <span>0</span>
                            </button>
                            <button class="action-btn" onclick="toggleDislike(this, <?= $item['id'] ?>)">
                                👎 <span>0</span>
                            </button>
                        </div>
                        <a href="index.php?page=source_detail&id=<?= $item['id'] ?>" class="view-detail-btn">
                            📖 Chi tiết
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleLike(button, postId) {
    const isLiked = button.classList.contains('liked');
    
    if (isLiked) {
        button.classList.remove('liked');
        button.style.color = '#666';
        button.style.background = '';
    } else {
        button.classList.add('liked');
        button.style.color = '#28a745';
        button.style.background = '#f8fff8';
        
        // Remove dislike if exists
        const dislikeBtn = button.nextElementSibling;
        dislikeBtn.classList.remove('liked');
        dislikeBtn.style.color = '#666';
        dislikeBtn.style.background = '';
    }
    
    // Ở đây bạn có thể thêm AJAX call để lưu vào database
    console.log(`Post ${postId} ${isLiked ? 'unliked' : 'liked'}`);
}

function toggleDislike(button, postId) {
    const isDisliked = button.classList.contains('liked');
    
    if (isDisliked) {
        button.classList.remove('liked');
        button.style.color = '#666';
        button.style.background = '';
    } else {
        button.classList.add('liked');
        button.style.color = '#e74c3c';
        button.style.background = '#fff8f8';
        
        // Remove like if exists
        const likeBtn = button.previousElementSibling;
        likeBtn.classList.remove('liked');
        likeBtn.style.color = '#666';
        likeBtn.style.background = '';
    }
    
    // Ở đây bạn có thể thêm AJAX call để lưu vào database
    console.log(`Post ${postId} ${isDisliked ? 'undisliked' : 'disliked'}`);
}

// Animation khi scroll
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.post-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        observer.observe(card);
    });
});
</script>

<?php
// Lưu nội dung vào biến
$content = ob_get_clean();

// Tìm đường dẫn đúng đến layout.php
// Thử các đường dẫn có thể có
$possible_paths = [
    __DIR__ . '/layout.php',
    __DIR__ . '/../layout.php', 
    __DIR__ . '/../../layout.php',
    'layout.php'
];

$layout_found = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        include $path;
        $layout_found = true;
        break;
    }
}

// Nếu không tìm thấy layout.php, hiển thị trực tiếp
if (!$layout_found) {
    // Fallback: hiển thị với layout tối thiểu
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $title ?? 'Thư viện nguồn - Sharedy' ?></title>
        <style>
            body { margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
            .simple-header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 15px 20px; }
            .simple-header h1 { margin: 0; font-size: 24px; }
            .simple-nav { background: #343a40; padding: 10px 20px; }
            .simple-nav a { color: white; text-decoration: none; margin-right: 20px; }
            .simple-nav a:hover { text-decoration: underline; }
            main { min-height: calc(100vh - 120px); }
            .simple-footer { background: #007bff; color: white; text-align: center; padding: 20px; }
        </style>
    </head>
    <body>
        <div class="simple-header">
            <h1>Sharedy - Hệ thống chia sẻ tài liệu</h1>
        </div>
        <nav class="simple-nav">
            <a href="index.php?page=home">Trang chủ</a>
            <a href="index.php?page=monhoc">Môn học</a>
            <a href="index.php?page=source">Thư viện nguồn</a>
        </nav>
        <main>
            <?= $content ?>
        </main>
        <footer class="simple-footer">
            <p>&copy; <?= date("Y") ?> Sharedy - Hệ thống chia sẻ tài liệu học tập</p>
        </footer>
    </body>
    </html>
    <?php
}
?>