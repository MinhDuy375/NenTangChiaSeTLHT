<?php
// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
$is_logged_in = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';

// src/Views/chiTietNguon.php
include __DIR__ . '/../../config/ketNoiDB.php';

$id = $_GET['id'] ?? 0;

$sql = "SELECT b.*, u.ten_dang_nhap, d.ten_danh_muc
        FROM bai_chia_se b
        LEFT JOIN nguoi_dung u ON b.id_nguoi_dung = u.id
        LEFT JOIN danh_muc d ON b.id_danh_muc = d.id
        WHERE b.id = :id AND b.loai = 'du_an'";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

// Lấy comments (giả sử có bảng comments)
$comments_sql = "SELECT c.*, u.ten_dang_nhap as commenter_name 
                 FROM binh_luan c 
                 LEFT JOIN nguoi_dung u ON c.id_nguoi_dung = u.id 
                 WHERE c.id_bai_chia_se = :id 
                 ORDER BY c.ngay_tao DESC";
$comments_stmt = $pdo->prepare($comments_sql);
$comments_stmt->execute(['id' => $id]);
$comments = $comments_stmt->fetchAll();

// Thiết lập title cho trang
$title = htmlspecialchars($item['tieu_de'] ?? 'Chi tiết mã nguồn') . " - Sharedy";

// Bắt đầu buffer để capture nội dung
ob_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f8f9fa;
        }
        
        /* Header styles - tương thích với layout.php */
        .main-header { 
            position: fixed;   
            top: 0;            
            left: 0;
            right: 0;
            z-index: 1000; 
            background: linear-gradient(135deg, #007bff, #0056b3); 
            color: white; 
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            padding: 10px 10px;
        }
        
        .logo:hover {
            opacity: 0.9;
        }
        
        .header-search-container {
            flex: 1;
            max-width: 300px;
            margin: 0 30px;
        }
        
        .header-search-box {
            width: 100%;
            height: 40px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 20px;
            font-size: 14px;
            transition: all 0.3s;
            padding: 0 20px;
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .header-search-box::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .header-search-box:focus {
            outline: none;
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.8);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.1);
        }
        
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 25px;
        }
        
        .nav-menu a { 
            color: white; 
            text-decoration: none; 
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(255,255,255,0.2);
            transform: translateY(-1px);
        }
        
        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-left: 20px;
        }
        
        .user-name {
            font-weight: 500;
            color: rgba(255,255,255,0.9);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            color: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
            border-color: #fff;
        }
        
        .logout-btn {
            background: rgba(255,77,79,0.9);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background: #ff4d4f;
            transform: translateY(-1px);
        }
        
        main { 
            padding-top: 70px; 
            min-height: calc(100vh - 130px);
            background-color: #f8f9fa;
        }
        
        footer { 
            background: #007bff; 
            color: white;
            text-align: center; 
            padding: 20px;
            margin-top: auto;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Detail page specific styles */
        .detail-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .back-button {
            margin-bottom: 20px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            text-decoration: none;
            color: #495057;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .back-btn:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .post-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid #f0f0f0;
        }

        .post-header {
            padding: 25px 25px 0;
            background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
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
            font-size: 20px;
            flex-shrink: 0;
        }

        .user-details h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #666;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .category-tag {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1976d2;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .tech-tag {
            background: linear-gradient(135deg, #f3e5f5, #e1bee7);
            color: #7b1fa2;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .post-content {
            padding: 25px;
        }

        .post-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .post-description {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
            white-space: pre-line;
        }

        .post-links {
            display: grid;
            gap: 15px;
            margin-bottom: 25px;
        }

        .link-card {
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .link-card:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,123,255,0.15);
        }

        .link-content {
            padding: 15px;
            background: #f8f9fa;
        }

        .link-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .link-url {
            color: #666;
            font-size: 13px;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .link-button {
            display: block;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .link-button:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: scale(1.02);
        }

        .link-button.secondary {
            background: linear-gradient(135deg, #28a745, #1e7e34);
        }

        .link-button.secondary:hover {
            background: linear-gradient(135deg, #1e7e34, #155724);
        }

        .post-stats {
            padding: 15px 25px;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: #666;
            background: #fafafa;
        }

        .like-count {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reaction-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .reaction-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .like-icon {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }

        .dislike-icon {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .post-actions {
            padding: 15px 25px;
            display: flex;
            gap: 10px;
        }

        .action-button {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background: none;
            border: 2px solid #f0f0f0;
            color: #666;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .action-button:hover {
            background: #f8f9fa;
            border-color: #dee2e6;
        }

        .action-button.active {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-color: #007bff;
            color: #007bff;
        }

        .action-button.dislike.active {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            border-color: #dc3545;
            color: #dc3545;
        }

        .comments-section {
            padding: 25px;
            background: #fafafa;
        }

        .comments-header {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .comment-input-container {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
        }

        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #28a745, #1e7e34);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            flex-shrink: 0;
        }

        .comment-input {
            flex: 1;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            padding: 12px 20px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }

        .comment-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }

        .comment {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .comment-content {
            flex: 1;
        }

        .comment-bubble {
            background: white;
            padding: 15px;
            border-radius: 16px;
            margin-bottom: 8px;
            border: 1px solid #f0f0f0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .comment-author {
            font-weight: 600;
            font-size: 14px;
            color: #333;
            margin-bottom: 6px;
        }

        .comment-text {
            font-size: 14px;
            color: #555;
            line-height: 1.5;
        }

        .comment-actions {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #666;
            font-weight: 600;
        }

        .comment-action {
            cursor: pointer;
            transition: color 0.3s;
        }

        .comment-action:hover {
            color: #007bff;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
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
            line-height: 1.6;
        }

        .no-comments {
            text-align: center;
            color: #666;
            padding: 40px 20px;
            background: white;
            border-radius: 12px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                height: auto;
                padding: 10px;
            }
            
            .header-search-container {
                margin: 10px 0;
                max-width: 100%;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                gap: 10px;
                justify-content: center;
            }
            
            .user-section {
                margin-left: 0;
                margin-top: 10px;
            }
            
            main {
                padding-top: 120px;
            }
            
            .detail-container {
                padding: 15px;
            }
            
            .post-header, .post-content, .post-stats, .post-actions, .comments-section {
                padding-left: 20px;
                padding-right: 20px;
            }
            
            .post-title {
                font-size: 24px;
            }
            
            .post-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .user-name {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .nav-menu a {
                padding: 6px 12px;
                font-size: 14px;
            }
            
            .logo {
                font-size: 20px;
            }
            
            .post-header, .post-content, .post-stats, .post-actions, .comments-section {
                padding-left: 15px;
                padding-right: 15px;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <a href="index.php?page=home" class="logo">Sharedy</a>
        
        <div class="header-search-container">
            <input type="text" 
                   class="header-search-box" 
                   id="search-input" 
                   placeholder="Tìm kiếm nhanh..."
                   onkeyup="tim_kiem_mon_hoc()">
        </div>
        
        <nav class="nav-menu">
            <a href="index.php?page=home">Trang chủ</a>
            <a href="index.php?page=monhoc">Môn học</a>
            <a href="index.php?page=source" class="active">Thư viện nguồn</a>
        </nav>
        
        <?php if ($is_logged_in): ?>
        <div class="user-section">
            <span class="user-name"><?= htmlspecialchars($username) ?></span>
            <div class="user-avatar" title="<?= htmlspecialchars($username) ?>">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
            <form action="logout.php" method="post" style="margin:0;">
                <button type="submit" class="logout-btn">Đăng xuất</button>
            </form>
        </div>
        <?php else: ?>
        <div class="user-section">
            <a href="index.php?page=login" style="padding: 8px 16px; font-size: 14px; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 20px;">Đăng nhập</a>
        </div>
        <?php endif; ?>
    </header>
    
    <main>
        <div class="detail-container">
            <?php if (!$item): ?>
                <div class="empty-state">
                    <h3>❌ Không tìm thấy mã nguồn</h3>
                    <p>Bài viết có thể đã bị xóa hoặc không tồn tại.<br>
                       Vui lòng kiểm tra lại đường link hoặc quay về trang chủ.</p>
                    <div style="margin-top: 20px;">
                        <a href="index.php?page=source" style="background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;">
                            ← Quay về Thư viện nguồn
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="back-button">
                    <a href="index.php?page=source" class="back-btn">
                        ← Quay về Thư viện nguồn
                    </a>
                </div>

                <div class="post-container">
                    <!-- Post Header -->
                    <div class="post-header">
                        <div class="user-info">
                            <div class="avatar">
                                <?= strtoupper(substr($item['ten_dang_nhap'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div class="user-details">
                                <h3><?= htmlspecialchars($item['ten_dang_nhap'] ?? 'Ẩn danh') ?></h3>
                                <div class="post-meta">
                                    <div class="meta-item">
                                        📅 <span><?= date('d/m/Y lúc H:i', strtotime($item['ngay_tao'])) ?></span>
                                    </div>
                                    <span class="category-tag">📂 <?= htmlspecialchars($item['ten_danh_muc']) ?></span>
                                    <?php if (!empty($item['cong_nghe'])): ?>
                                        <span class="tech-tag">💻 <?= htmlspecialchars($item['cong_nghe']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Post Content -->
                    <div class="post-content">
                        <h1 class="post-title"><?= htmlspecialchars($item['tieu_de']) ?></h1>
                        <div class="post-description"><?= htmlspecialchars($item['mo_ta']) ?></div>

                        <?php if (!empty($item['link_host']) || !empty($item['link_source'])): ?>
                            <div class="post-links">
                                <?php if (!empty($item['link_host'])): ?>
                                    <div class="link-card">
                                        <div class="link-content">
                                            <div class="link-title">🌐 Demo / Live Preview</div>
                                            <div class="link-url"><?= htmlspecialchars($item['link_host']) ?></div>
                                        </div>
                                        <a href="<?= htmlspecialchars($item['link_host']) ?>" target="_blank" class="link-button">
                                            🚀 Xem Demo Trực tiếp
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($item['link_source'])): ?>
                                    <div class="link-card">
                                        <div class="link-content">
                                            <div class="link-title">💻 Mã nguồn (Source Code)</div>
                                            <div class="link-url"><?= htmlspecialchars($item['link_source']) ?></div>
                                        </div>
                                        <a href="<?= htmlspecialchars($item['link_source']) ?>" target="_blank" class="link-button secondary">
                                            📦 Tải về / Xem Source Code
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Post Stats -->
                    <div class="post-stats">
                        <div class="like-count">
                            <?php if (($item['so_luot_like'] ?? 0) > 0): ?>
                                <div class="reaction-group">
                                    <div class="reaction-icon like-icon">👍</div>
                                    <span><?= $item['so_luot_like'] ?> lượt thích</span>
                                </div>
                            <?php endif; ?>
                            <?php if (($item['so_luot_dislike'] ?? 0) > 0): ?>
                                <div class="reaction-group">
                                    <div class="reaction-icon dislike-icon">👎</div>
                                    <span><?= $item['so_luot_dislike'] ?> không thích</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            💬 <span><?= count($comments ?? []) ?> bình luận</span>
                        </div>
                    </div>

                    <!-- Post Actions -->
                    <div class="post-actions">
                        <button class="action-button" id="likeBtn">
                            <span>👍</span> Thích
                        </button>
                        <button class="action-button dislike" id="dislikeBtn">
                            <span>👎</span> Không thích
                        </button>
                        <button class="action-button" id="commentBtn">
                            <span>💬</span> Bình luận
                        </button>
                        <button class="action-button">
                            <span>📤</span> Chia sẻ
                        </button>
                    </div>

                    <!-- Comments Section -->
                    <div class="comments-section">
                        <div class="comments-header">
                            💬 Bình luận (<?= count($comments ?? []) ?>)
                        </div>
                        
                        <div class="comment-input-container">
                            <div class="comment-avatar">
                                <?= $is_logged_in ? strtoupper(substr($username, 0, 1)) : 'U' ?>
                            </div>
                            <input type="text" class="comment-input" placeholder="Viết bình luận của bạn...">
                        </div>

                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <div class="comment-avatar">
                                        <?= strtoupper(substr($comment['commenter_name'] ?? 'A', 0, 1)) ?>
                                    </div>
                                    <div class="comment-content">
                                        <div class="comment-bubble">
                                            <div class="comment-author"><?= htmlspecialchars($comment['commenter_name'] ?? 'Ẩn danh') ?></div>
                                            <div class="comment-text"><?= htmlspecialchars($comment['noi_dung']) ?></div>
                                        </div>
                                        <div class="comment-actions">
                                            <span class="comment-action">Thích</span>
                                            <span class="comment-action">Phản hồi</span>
                                            <span><?= date('d/m/Y H:i', strtotime($comment['ngay_tao'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-comments">
                                <p>💭 Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer>
        <div class="footer-content">
            <p>&copy; <?= date("Y") ?> Sharedy - Hệ thống chia sẻ tài liệu học tập</p>
            <p style="margin-top: 5px; font-size: 14px; opacity: 0.8;">
                Phát triển bởi nhóm sinh viên CNTT
            </p>
        </div>
    </footer>

    <script>
        // Functionality for like/dislike buttons
        document.getElementById('likeBtn').addEventListener('click', function() {
            const isActive = this.classList.contains('active');
            
            // Toggle like button
            if (isActive) {
                this.classList.remove('active');
            } else {
                this.classList.add('active');
                // Remove dislike if active
                document.getElementById('dislikeBtn').classList.remove('active');
            }
            
            // Here you can add AJAX call to update database
            console.log(isActive ? 'Unliked' : 'Liked');
        });

        document.getElementById('dislikeBtn').addEventListener('click', function() {
            const isActive = this.classList.contains('active');
            
            // Toggle dislike button
            if (isActive) {
                this.classList.remove('active');
            } else {
                this.classList.add('active');
                // Remove like if active
                document.getElementById('likeBtn').classList.remove('active');
            }
            
            // Here you can add AJAX call to update database
            console.log(isActive ? 'Undisliked' : 'Disliked');
        });

        document.getElementById('commentBtn').addEventListener('click', function() {
            document.querySelector('.comment-input').focus();
            document.querySelector('.comment-input').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        });

        // Handle comment submission
        document.querySelector('.comment-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                // Here you can add AJAX call to submit comment
                alert('Tính năng bình luận sẽ được phát triển sau!\nNội dung: ' + this.value);
                this.value = '';
            }
        });

        // Add hover effects for comment actions
        document.querySelectorAll('.comment-action').forEach(action => {
            action.addEventListener('click', function() {
                if (this.textContent === 'Thích') {
                    alert('Tính năng thích bình luận sẽ được phát triển sau!');
                } else if (this.textContent === 'Phản hồi') {
                    alert('Tính năng phản hồi bình luận sẽ được phát triển sau!');
                }
            });
        });

        // Share functionality
        document.querySelector('.post-actions .action-button:last-child').addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: document.querySelector('.post-title').textContent,
                    text: 'Xem dự án thú vị này trên Sharedy!',
                    url: window.location.href
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(function() {
                    alert('Đã copy link vào clipboard!');
                });
            }
        });

        // Add loading animation for buttons
        document.querySelectorAll('.action-button').forEach(button => {
            button.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.style.opacity = '0.7';
                
                setTimeout(() => {
                    this.style.opacity = '1';
                }, 200);
            });
        });

        // Smooth scroll for back button
        document.querySelector('.back-btn').addEventListener('click', function(e) {
            e.preventDefault();
            window.history.back();
        });

        // Add copy functionality for code links
        document.querySelectorAll('.link-url').forEach(linkUrl => {
            linkUrl.addEventListener('click', function() {
                navigator.clipboard.writeText(this.textContent).then(function() {
                    // Show temporary feedback
                    const original = linkUrl.textContent;
                    linkUrl.textContent = 'Đã copy!';
                    linkUrl.style.color = '#28a745';
                    
                    setTimeout(() => {
                        linkUrl.textContent = original;
                        linkUrl.style.color = '#666';
                    }, 1500);
                });
            });
            
            linkUrl.style.cursor = 'pointer';
            linkUrl.title = 'Click để copy link';
        });

        // Header search functionality (basic)
        function tim_kiem_mon_hoc() {
            // Basic search functionality for header
            console.log('Search functionality will be implemented');
        }

        // Add scroll to top functionality
        let scrollToTopBtn = document.createElement('button');
        scrollToTopBtn.innerHTML = '↑';
        scrollToTopBtn.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        `;
        
        document.body.appendChild(scrollToTopBtn);
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.style.opacity = '1';
            } else {
                scrollToTopBtn.style.opacity = '0';
            }
        });
        
        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Add entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const postContainer = document.querySelector('.post-container');
            if (postContainer) {
                postContainer.style.opacity = '0';
                postContainer.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    postContainer.style.transition = 'all 0.6s ease';
                    postContainer.style.opacity = '1';
                    postContainer.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
</body>
</html>

<?php
// Lưu nội dung vào biến
$content = ob_get_clean();

// Tìm đường dẫn đúng đến layout.php
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
    echo $content;
}
?>