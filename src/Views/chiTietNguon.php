<?php
// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';

include __DIR__ . '/../../config/ketNoiDB.php';

$id = $_GET['id'] ?? 0;
$userId = $is_logged_in ? (int)$_SESSION['user_id'] : 0;

// ===================================
// 1. SQL QUERY ĐƯỢC CẬP NHẬT
// ===================================
$sql = "SELECT 
            b.*, 
            u.ten_dang_nhap, 
            d.ten_danh_muc,
            -- Đếm tổng reaction từ bảng `reaction`
            (SELECT COUNT(*) FROM reaction r WHERE r.id_bai_chia_se = b.id) AS tong_so_reaction,
            -- Đếm tổng dislike từ bảng `tuong_tac`
            (SELECT COUNT(*) FROM tuong_tac t WHERE t.id_bai_chia_se = b.id AND t.loai = 'dislike') AS so_luot_dislike,
            -- Lấy chi tiết các loại reaction để hiển thị icon
            (SELECT GROUP_CONCAT(CONCAT(r.loai_cam_xuc, ':', r.count) SEPARATOR ';')
             FROM (SELECT loai_cam_xuc, COUNT(*) as count FROM reaction WHERE id_bai_chia_se = b.id GROUP BY loai_cam_xuc) r
            ) AS chi_tiet_reaction,
            -- Lấy reaction của user hiện tại
            (SELECT r.loai_cam_xuc FROM reaction r WHERE r.id_bai_chia_se = b.id AND r.id_nguoi_dung = :uid) AS user_reaction,
            -- Kiểm tra user hiện tại đã dislike chưa
            EXISTS(SELECT 1 FROM tuong_tac t WHERE t.id_bai_chia_se = b.id AND t.id_nguoi_dung = :uid AND t.loai = 'dislike') AS da_dislike
        FROM bai_chia_se b
        LEFT JOIN nguoi_dung u ON b.id_nguoi_dung = u.id
        LEFT JOIN danh_muc d ON b.id_danh_muc = d.id
        WHERE b.id = :id AND b.loai = 'bai_viet'";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id, ':uid' => $userId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy comments
$comments_sql = "SELECT c.*, u.ten_dang_nhap as commenter_name 
                 FROM binh_luan c 
                 LEFT JOIN nguoi_dung u ON c.id_nguoi_dung = u.id 
                 WHERE c.id_bai_chia_se = :id 
                 ORDER BY c.ngay_tao DESC";
$comments_stmt = $pdo->prepare($comments_sql);
$comments_stmt->execute(['id' => $id]);
$comments = $comments_stmt->fetchAll();

$title = htmlspecialchars($item['tieu_de'] ?? 'Chi tiết mã nguồn') . " - Sharedy";

ob_start();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        /* CSS Cũ của bạn giữ nguyên */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        main {
            padding-top: 30px;
            min-height: calc(100vh - 130px);
        }

        .detail-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .back-button {
            margin-bottom: 20px;
            margin-top: 35px;
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .back-btn:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .post-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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
            min-height: 50px;
        }

        .reaction-summary {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reaction-icons {
            display: flex;
            align-items: center;
        }

        .reaction-icon {
            font-size: 18px;
            margin-left: -5px;
            background: white;
            border-radius: 50%;
            padding: 2px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .reaction-count,
        .dislike-count,
        .comment-count {
            display: flex;
            align-items: center;
            gap: 5px;
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

        .action-button.active-dislike {
            background: linear-gradient(135deg, #ffebee, #ffcdd2);
            border-color: #dc3545;
            color: #dc3545;
        }

        /* CSS cho nút like khi active */
        .like-button.active.like {
            color: #007bff;
            font-weight: bold;
        }

        .like-button.active.love {
            color: #e0245e;
            font-weight: bold;
        }

        .like-button.active.care {
            color: #f7b125;
            font-weight: bold;
        }

        .like-button.active.haha {
            color: #f7b125;
            font-weight: bold;
        }

        .like-button.active.wow {
            color: #f7b125;
            font-weight: bold;
        }

        .like-button.active.sad {
            color: #f7b125;
            font-weight: bold;
        }

        .like-button.active.angry {
            color: #e0245e;
            font-weight: bold;
        }

        .like-button.active {
            border-color: #007bff;
            background: #e3f2fd;
        }


        /* ============================== */
        /* CSS ĐÃ SỬA CHO REACTION POPUP  */
        /* ============================== */
        .reaction-box {
            position: relative;
            flex: 1;
        }

        .reaction-box .action-button {
            width: 100%;
            /* Đảm bảo nút Thích chiếm toàn bộ chiều rộng */
        }

        .reaction-popup {
            position: absolute;
            bottom: 100%;
            left: 0;
            /* Canh lề trái với hộp chứa nó */
            margin-bottom: 10px;
            background-color: white;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            padding: 5px;
            display: none;
            align-items: center;
            gap: 5px;
            z-index: 10;
            transition: all 0.2s ease-out;
        }

        .reaction-popup.show {
            display: flex;
        }

        .reaction {
            font-size: 28px;
            cursor: pointer;
            transition: transform 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            padding: 5px;
        }

        .reaction:hover {
            transform: scale(1.3);
        }

        @keyframes popup-appear {
            from {
                opacity: 0;
                transform: translateY(10px);
                /* Sửa lại: Chỉ còn hiệu ứng trồi lên */
            }

            to {
                opacity: 1;
                transform: translateY(0);
                /* Sửa lại: Chỉ còn hiệu ứng trồi lên */
            }
        }

        .reaction-popup.show {
            animation: popup-appear 0.2s ease-out forwards;
        }

        /* CSS cũ cho comment */
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
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        @media (max-width: 768px) {

            .post-actions,
            .post-content,
            .post-stats,
            .comments-section,
            .post-header {
                padding-left: 15px;
                padding-right: 15px;
            }
        }
    </style>
</head>

<body>
    <main>
        <div class="detail-container">
            <?php if (!$item): ?>
                <div class="empty-state">
                    <h3>❌ Không tìm thấy mã nguồn</h3>
                    <p>Bài viết có thể đã bị xóa hoặc không tồn tại.</p>
                </div>
            <?php else: ?>
                <div class="back-button">
                    <a href="index.php?page=source" class="back-btn">← Quay về Thư viện nguồn</a>
                </div>

                <div class="post-container" id="post-<?= $item['id'] ?>" data-post-id="<?= $item['id'] ?>">
                    <div class="post-header">
                        <div class="user-info">
                            <div class="avatar"><?= strtoupper(substr($item['ten_dang_nhap'] ?? 'A', 0, 1)) ?></div>
                            <div class="user-details">
                                <h3><?= htmlspecialchars($item['ten_dang_nhap'] ?? 'Ẩn danh') ?></h3>
                                <div class="post-meta">
                                    <span>📅 <?= date('d/m/Y lúc H:i', strtotime($item['ngay_tao'])) ?></span>
                                    <span class="category-tag">📂 <?= htmlspecialchars($item['ten_danh_muc']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="post-content">
                        <h1 class="post-title"><?= htmlspecialchars($item['tieu_de']) ?></h1>
                        <div class="post-description"><?= htmlspecialchars($item['mo_ta']) ?></div>
                    </div>

                    <div class="post-stats">
                        <div class="reaction-summary">
                            <div class="reaction-icons"></div>
                            <span class="reaction-count"></span>
                        </div>
                        <div class="right-stats" style="display: flex; gap: 15px;">
                            <span class="dislike-count"></span>
                            <span class="comment-count">💬 <?= count($comments ?? []) ?> bình luận</span>
                        </div>
                    </div>


                    <div class="post-actions">
                        <div class="reaction-box">
                            <div class="reaction-popup">
                                <span class="reaction" data-type="like">👍</span>
                                <span class="reaction" data-type="love">❤️</span>
                                <span class="reaction" data-type="care">🤗</span>
                                <span class="reaction" data-type="haha">😆</span>
                                <span class="reaction" data-type="wow">😮</span>
                                <span class="reaction" data-type="sad">😢</span>
                                <span class="reaction" data-type="angry">😡</span>
                            </div>
                            <button class="action-button like-button">
                                <span>👍</span> Thích
                            </button>
                        </div>
                        <button class="action-button dislike-button">
                            <span>👎</span> Không thích
                        </button>
                        <button class="action-button" id="commentBtn">
                            <span>💬</span> Bình luận
                        </button>
                        <button class="action-button share-button">
                            <span>📤</span> Chia sẻ
                        </button>
                    </div>

                    <div class="comments-section">
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const postContainer = document.querySelector('.post-container');
            if (!postContainer) return;

            const postId = postContainer.dataset.postId;
            const likeButton = postContainer.querySelector('.like-button');
            const dislikeButton = postContainer.querySelector('.dislike-button');
            const reactionBox = postContainer.querySelector('.reaction-box');
            const reactionPopup = postContainer.querySelector('.reaction-popup');
            const reactions = postContainer.querySelectorAll('.reaction');
            const isLoggedIn = <?= json_encode($is_logged_in) ?>;

            const reactionMap = {
                like: {
                    emoji: '👍',
                    text: 'Thích',
                    colorClass: 'like'
                },
                love: {
                    emoji: '❤️',
                    text: 'Yêu thích',
                    colorClass: 'love'
                },
                care: {
                    emoji: '🤗',
                    text: 'Thương thương',
                    colorClass: 'care'
                },
                haha: {
                    emoji: '😆',
                    text: 'Haha',
                    colorClass: 'haha'
                },
                wow: {
                    emoji: '😮',
                    text: 'Wow',
                    colorClass: 'wow'
                },
                sad: {
                    emoji: '😢',
                    text: 'Buồn',
                    colorClass: 'sad'
                },
                angry: {
                    emoji: '😡',
                    text: 'Phẫn nộ',
                    colorClass: 'angry'
                }
            };

            let currentUserState = {
                reaction: '<?= $item['user_reaction'] ?? null ?>',
                disliked: <?= json_encode((bool)$item['da_dislike']) ?>
            };

            async function handleInteraction(type) {
                if (!isLoggedIn) {
                    alert('Bạn cần đăng nhập để thực hiện hành động này.');
                    return;
                }

                try {
                    // Đảm bảo bạn gọi đúng file AJAX đã đổi tên
                    const response = await fetch('src/Views/ajax_reaction.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            postId: postId,
                            type: type
                        })
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.error || 'Có lỗi xảy ra.');
                    }

                    const data = await response.json();
                    if (data.success) {
                        currentUserState = data.user_status;
                        updateUI(data);
                    } else {
                        throw new Error(data.error);
                    }
                } catch (error) {
                    console.error('Lỗi tương tác:', error);
                    alert('Lỗi: ' + error.message);
                }
            }

            function updateUI(data) {
                likeButton.classList.remove('active', ...Object.values(reactionMap).map(r => r.colorClass));
                if (currentUserState.reaction && reactionMap[currentUserState.reaction]) {
                    const reactionInfo = reactionMap[currentUserState.reaction];
                    likeButton.classList.add('active', reactionInfo.colorClass);
                    likeButton.innerHTML = `<span>${reactionInfo.emoji}</span> ${reactionInfo.text}`;
                } else {
                    likeButton.innerHTML = `<span>👍</span> Thích`;
                }

                dislikeButton.classList.toggle('active-dislike', currentUserState.disliked);

                const {
                    reactions,
                    dislikes
                } = data;
                const reactionIconsContainer = postContainer.querySelector('.reaction-icons');
                const reactionCountSpan = postContainer.querySelector('.reaction-count');
                const dislikeCountSpan = postContainer.querySelector('.dislike-count');

                reactionIconsContainer.innerHTML = '';
                if (reactions.details) {
                    const sortedReactions = Object.keys(reactions.details).sort((a, b) => {
                        const order = ['love', 'like', 'care', 'haha', 'wow', 'sad', 'angry'];
                        return order.indexOf(a) - order.indexOf(b);
                    });
                    sortedReactions.forEach(type => {
                        if (reactionMap[type]) {
                            const iconSpan = document.createElement('span');
                            iconSpan.className = 'reaction-icon';
                            iconSpan.textContent = reactionMap[type].emoji;
                            reactionIconsContainer.appendChild(iconSpan);
                        }
                    });
                }
                reactionCountSpan.textContent = reactions.total > 0 ? reactions.total : '';
                dislikeCountSpan.textContent = dislikes.total > 0 ? `👎 ${dislikes.total}` : '';
            }

            // ===========================================
            // JAVASCRIPT ĐÃ SỬA CHO HIỆU ỨNG POPUP
            // ===========================================
            let hideTimeout;

            reactionBox.addEventListener('mouseenter', () => {
                clearTimeout(hideTimeout);
                reactionPopup.classList.add('show');
            });

            reactionBox.addEventListener('mouseleave', () => {
                hideTimeout = setTimeout(() => {
                    reactionPopup.classList.remove('show');
                }, 300);
            });

            reactions.forEach(reaction => {
                reaction.addEventListener('click', () => {
                    const type = reaction.dataset.type;
                    handleInteraction(type);
                    reactionPopup.classList.remove('show');
                });
            });

            likeButton.addEventListener('click', () => {
                if (currentUserState.reaction) {
                    handleInteraction('remove_reaction');
                } else {
                    handleInteraction('like');
                }
            });

            dislikeButton.addEventListener('click', () => {
                if (currentUserState.disliked) {
                    handleInteraction('remove_dislike');
                } else {
                    handleInteraction('dislike');
                }
            });

            const initialData = {
                reactions: {
                    total: <?= $item['tong_so_reaction'] ?? 0 ?>,
                    details: {
                        <?php
                        if (!empty($item['chi_tiet_reaction'])) {
                            $details = [];
                            $pairs = explode(';', $item['chi_tiet_reaction']);
                            foreach ($pairs as $pair) {
                                list($key, $value) = explode(':', $pair);
                                $details[] = '"' . htmlspecialchars($key) . '": ' . (int)$value;
                            }
                            echo implode(', ', $details);
                        }
                        ?>
                    }
                },
                dislikes: {
                    total: <?= $item['so_luot_dislike'] ?? 0 ?>
                }
            };
            updateUI(initialData);

        });

        document.getElementById('commentBtn')?.addEventListener('click', function() {
            document.querySelector('.comment-input')?.focus();
        });
    </script>

</body>

</html>