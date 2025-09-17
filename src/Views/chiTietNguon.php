<?php
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['tieu_de'] ?? 'Chi Tiết Mã Nguồn') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f0f2f5;
            line-height: 1.34;
        }

        .container {
            max-width: 680px;
            margin: 20px auto;
            padding: 0 16px;
        }

        .back-button {
            margin-bottom: 16px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: white;
            border: 1px solid #dadde1;
            border-radius: 8px;
            text-decoration: none;
            color: #1c1e21;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }

        .back-btn:hover {
            background: #f0f2f5;
        }

        .post-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .post-header {
            padding: 16px 16px 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #1877f2, #42a5f5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .user-details h3 {
            font-size: 15px;
            font-weight: 600;
            color: #1c1e21;
            margin-bottom: 2px;
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #65676b;
        }

        .category-tag {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .tech-tag {
            background: #f3e5f5;
            color: #7b1fa2;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .post-content {
            padding: 12px 16px;
        }

        .post-title {
            font-size: 20px;
            font-weight: 600;
            color: #1c1e21;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .post-description {
            color: #1c1e21;
            font-size: 15px;
            line-height: 1.33;
            margin-bottom: 12px;
            white-space: pre-line;
        }

        .post-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 12px;
        }

        .link-card {
            border: 1px solid #dadde1;
            border-radius: 8px;
            overflow: hidden;
        }

        .link-content {
            padding: 12px;
            background: #f7f8fa;
        }

        .link-title {
            font-weight: 600;
            color: #1c1e21;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .link-url {
            color: #65676b;
            font-size: 12px;
            word-break: break-all;
        }

        .link-button {
            display: block;
            width: 100%;
            padding: 8px;
            background: #1877f2;
            color: white;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: background 0.2s;
        }

        .link-button:hover {
            background: #166fe5;
        }

        .link-button.secondary {
            background: #42b883;
        }

        .link-button.secondary:hover {
            background: #369870;
        }

        .post-stats {
            padding: 8px 16px;
            border-bottom: 1px solid #dadde1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #65676b;
        }

        .like-count {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .reaction-icon {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        .like-icon {
            background: #1877f2;
            color: white;
        }

        .dislike-icon {
            background: #f02849;
            color: white;
        }

        .post-actions {
            padding: 4px 16px;
            display: flex;
            border-bottom: 1px solid #dadde1;
        }

        .action-button {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px;
            background: none;
            border: none;
            color: #65676b;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .action-button:hover {
            background: #f0f2f5;
        }

        .action-button.active {
            color: #1877f2;
        }

        .action-button.dislike.active {
            color: #f02849;
        }

        .comments-section {
            padding: 12px 16px;
        }

        .comment-input-container {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .comment-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(45deg, #42b883, #35495e);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            flex-shrink: 0;
        }

        .comment-input {
            flex: 1;
            background: #f0f2f5;
            border: none;
            border-radius: 20px;
            padding: 8px 12px;
            font-size: 14px;
            outline: none;
        }

        .comment-input:focus {
            background: white;
            box-shadow: 0 0 0 2px #1877f2;
        }

        .comment {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .comment-content {
            flex: 1;
        }

        .comment-bubble {
            background: #f0f2f5;
            padding: 8px 12px;
            border-radius: 16px;
            margin-bottom: 4px;
        }

        .comment-author {
            font-weight: 600;
            font-size: 13px;
            color: #1c1e21;
            margin-bottom: 2px;
        }

        .comment-text {
            font-size: 14px;
            color: #1c1e21;
            line-height: 1.33;
        }

        .comment-actions {
            display: flex;
            gap: 12px;
            font-size: 12px;
            color: #65676b;
            font-weight: 600;
        }

        .comment-action {
            cursor: pointer;
        }

        .comment-action:hover {
            text-decoration: underline;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #65676b;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px auto;
                padding: 0 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$item): ?>
            <div class="empty-state">
                <h3>❌ Không tìm thấy mã nguồn.</h3>
                <p>Bài viết có thể đã bị xóa hoặc không tồn tại.</p>
            </div>
        <?php else: ?>
            <div class="back-button">
                <a href="javascript:history.back()" class="back-btn">
                    ← Quay lại
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
                                <span><?= date('d/m/Y \l\ú\c H:i', strtotime($item['ngay_tao'])) ?></span>
                                <span>•</span>
                                <span class="category-tag"><?= htmlspecialchars($item['ten_danh_muc']) ?></span>
                                <?php if (!empty($item['cong_nghe'])): ?>
                                    <span class="tech-tag"><?= htmlspecialchars($item['cong_nghe']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Post Content -->
                <div class="post-content">
                    <div class="post-title"><?= htmlspecialchars($item['tieu_de']) ?></div>
                    <div class="post-description"><?= htmlspecialchars($item['mo_ta']) ?></div>

                    <?php if (!empty($item['link_host']) || !empty($item['link_source'])): ?>
                        <div class="post-links">
                            <?php if (!empty($item['link_host'])): ?>
                                <div class="link-card">
                                    <div class="link-content">
                                        <div class="link-title">🌐 Demo / Host Link</div>
                                        <div class="link-url"><?= htmlspecialchars($item['link_host']) ?></div>
                                    </div>
                                    <a href="<?= htmlspecialchars($item['link_host']) ?>" target="_blank" class="link-button">
                                        Xem Demo
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($item['link_source'])): ?>
                                <div class="link-card">
                                    <div class="link-content">
                                        <div class="link-title">💻 Source Code</div>
                                        <div class="link-url"><?= htmlspecialchars($item['link_source']) ?></div>
                                    </div>
                                    <a href="<?= htmlspecialchars($item['link_source']) ?>" target="_blank" class="link-button secondary">
                                        Xem Source Code
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
                            <div class="reaction-icon like-icon">👍</div>
                            <span><?= $item['so_luot_like'] ?></span>
                        <?php endif; ?>
                        <?php if (($item['so_luot_dislike'] ?? 0) > 0): ?>
                            <div class="reaction-icon dislike-icon">👎</div>
                            <span><?= $item['so_luot_dislike'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span><?= count($comments ?? []) ?> bình luận</span>
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
                    <div class="comment-input-container">
                        <div class="comment-avatar">U</div>
                        <input type="text" class="comment-input" placeholder="Viết bình luận...">
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
                        <div style="text-align: center; color: #65676b; padding: 20px;">
                            Chưa có bình luận nào. Hãy là người đầu tiên bình luận!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Simple UI interactions for design demonstration
        document.getElementById('likeBtn').addEventListener('click', function() {
            this.classList.toggle('active');
        });

        document.getElementById('dislikeBtn').addEventListener('click', function() {
            this.classList.toggle('active');
            // Remove active from like button if dislike is clicked
            document.getElementById('likeBtn').classList.remove('active');
        });

        document.getElementById('commentBtn').addEventListener('click', function() {
            document.querySelector('.comment-input').focus();
        });

        // Handle comment input (just for demo)
        document.querySelector('.comment-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                alert('Tính năng bình luận sẽ được phát triển sau!');
                this.value = '';
            }
        });

        // Add hover effects for comment actions
        document.querySelectorAll('.comment-action').forEach(action => {
            action.addEventListener('click', function() {
                alert('Tính năng ' + this.textContent + ' sẽ được phát triển sau!');
            });
        });
    </script>
</body>
</html>