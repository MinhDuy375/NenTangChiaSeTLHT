<?php
session_start();
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy ID tài liệu
$id_tai_lieu = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_tai_lieu <= 0) {
    header('Location: index.php?page=monhoc');
    exit;
}

// ========== Lấy thông tin tài liệu ==========
try {
    $sql = "SELECT bcs.*, mh.ten_mon, nd.ho_ten AS ten_nguoi_dang, nd.email AS email_nguoi_dang
            FROM bai_chia_se bcs
            LEFT JOIN mon_hoc mh ON bcs.id_mon_hoc = mh.id
            LEFT JOIN nguoi_dung nd ON bcs.id_nguoi_dung = nd.id
            WHERE bcs.id = :id_tai_lieu AND bcs.loai = 'tai_lieu'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_tai_lieu' => $id_tai_lieu]);
    $tai_lieu = $stmt->fetch();
    if (!$tai_lieu) {
        header('Location: index.php?page=monhoc');
        exit;
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// ========== Lấy tài liệu liên quan ==========
try {
    $sql_lq = "SELECT id, tieu_de, tom_tat, file_upload, ngay_tao
               FROM bai_chia_se
               WHERE id_mon_hoc = :idmh AND id != :idtl AND loai = 'tai_lieu'
               ORDER BY ngay_tao DESC LIMIT 4";
    $stmt = $pdo->prepare($sql_lq);
    $stmt->execute([':idmh' => $tai_lieu['id_mon_hoc'], ':idtl' => $id_tai_lieu]);
    $tai_lieu_lien_quan = $stmt->fetchAll();
} catch (Exception $e) {
    $tai_lieu_lien_quan = [];
}

// ===== Các hàm phụ =====
function tao_url_preview($duong_dan_file) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $base = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
    return 'https://docs.google.com/viewer?url=' . urlencode($base . $duong_dan_file) . '&embedded=true';
}
function lay_icon_file($file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return match($ext) {
        'pdf' => '📄', 'doc', 'docx' => '📝', default => '📎'
    };
}
function tinh_kich_thuoc_file($file) {
    return (file_exists($file)) ?
        (filesize($file) >= 1048576 ?
            round(filesize($file) / 1048576, 2) . ' MB' :
            round(filesize($file) / 1024, 2) . ' KB')
        : 'Không xác định';
}
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo lam_sach_chuoi($tai_lieu['tieu_de']); ?> - Chi Tiết Tài Liệu</title>
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
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
        }


        .header {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            color: white;
            padding: 20px 30px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .breadcrumb {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .share-btn {
            background-color: #1877f2;
            /* xanh Facebook */
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .share-btn:hover {
            background-color: #145db2;
            /* màu đậm hơn khi hover */
        }

        .share-btn:active {
            transform: scale(0.96);
        }

        .breadcrumb a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .breadcrumb a:hover {
            opacity: 0.8;
        }

        .header-title {
            font-size: 1.5em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s;
            margin-right: 15px;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-2px);
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            min-height: calc(100vh - 80px);
        }

        .preview-section {
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
        }

        .preview-header {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .preview-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .preview-meta {
            color: #6c757d;
            font-size: 0.9em;
        }

        .preview-container {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .share-btn {
            background-color: #1877f2;
            /* xanh Facebook */
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .share-btn:hover {
            background-color: #145db2;
            /* màu đậm hơn khi hover */
        }

        .share-btn:active {
            transform: scale(0.96);
        }

        .preview-iframe {
            flex: 1;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: white;
            min-height: 600px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .preview-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .details-section {
            background: white;
            padding: 0;
            overflow-y: auto;
        }

        .details-content {
            padding: 30px;
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }

        .info-card h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #495057;
        }

        .info-value {
            color: #6c757d;
            text-align: right;
        }

        .description-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }

        .description-section h3 {
            color: #495057;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .description-text {
            color: #6c757d;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .related-section {
            margin-top: 30px;
        }

        .related-section h3 {
            color: #495057;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .related-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .related-item:hover {
            border-color: #007bff;
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
        }

        .related-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .related-meta {
            font-size: 0.85em;
            color: #6c757d;
        }

        .reaction-box {
            display: inline-flex;
            align-items: center;
            position: relative;
            margin-top: 10px;
            cursor: pointer;
        }

        .like-button {
            padding: 5px 10px;
            background: #eee;
            border-radius: 20px;
            transition: background 0.2s;
        }

        .like-button:hover {
            background: #ddd;
        }

        .reaction-count {
            margin-left: 8px;
            font-weight: bold;
            color: #444;
        }

        /* Popup cảm xúc */
        .reaction-popup {
            display: none;
            position: absolute;
            bottom: 40px;
            left: 0;
            background: #fff;
            border-radius: 30px;
            padding: 5px 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .reaction-popup .reaction {
            font-size: 22px;
            margin: 0 5px;
            cursor: pointer;
            transition: transform 0.2s;
        }


        .reaction-popup .reaction:hover {
            transform: scale(1.3);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(0, 123, 255, 0.3);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .loading-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            color: #6c757d;
        }

        .comment-box {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .comment-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .comment-input {
            width: 100%;
            min-height: 60px;
            resize: vertical;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .comment-input:focus {
            border-color: #007bff;
        }

        .comment-send {
            align-self: flex-end;
            margin-top: 8px;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .comment-send:hover {
            background-color: #0056b3;
        }



        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .details-section {
                border-top: 1px solid #dee2e6;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
            }

            .header-title {
                font-size: 1.2em;
            }

            .preview-container {
                padding: 15px;
            }

            .details-content {
                padding: 20px;
            }

            .preview-iframe {
                min-height: 400px;
            }
        }

        .comment-list {
            margin-top: 20px;
        }

        .comment-item {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .comment-item:hover {
            background-color: #f8f9fa;
        }

        .reaction-detail {
            margin-top: 10px;
            padding: 8px 0;
            font-size: 14px;
        }

        .reaction-item {
            display: inline-block;
            margin-right: 15px;
            padding: 4px 10px;
            background: #f0f2f5;
            border-radius: 12px;
            font-size: 13px;
        }

        .reaction-count {
            transition: transform 0.2s;
        }
    </style>
</head>

<body>
<div class="container">

    <div class="header" style="background:#2196F3;color:#fff;padding:20px;">
        <div>
            <a href="index.php?page=monhoc" style="color:white;">← Danh sách môn học</a> /
            <?= htmlspecialchars($tai_lieu['ten_mon']) ?> /
            <b>Chi tiết tài liệu</b>
        </div>
        <h2><?= lay_icon_file($tai_lieu['file_upload']) ?> <?= htmlspecialchars($tai_lieu['tieu_de']) ?></h2>
    </div>

    <div class="main-content" style="display:grid;grid-template-columns:1fr 350px;">
        <div class="preview" style="padding:20px;">
            <iframe src="<?= tao_url_preview($tai_lieu['file_upload']) ?>" style="width:100%;height:600px;border:1px solid #ccc;"></iframe>

            <div style="margin-top:10px;">
                <a href="<?= $tai_lieu['file_upload'] ?>" download class="btn btn-success">📥 Tải xuống</a>
                <button onclick="copyLink()" class="btn btn-primary">📋 Copy link</button>
            </div>
        </div>

        <div class="details" style="padding:20px;">
            <h3>📄 Thông tin</h3>
            <p><b>Môn học:</b> <?= htmlspecialchars($tai_lieu['ten_mon']) ?></p>
            <p><b>Người đăng:</b> <?= htmlspecialchars($tai_lieu['ten_nguoi_dang']) ?></p>
            <p><b>Kích thước:</b> <?= tinh_kich_thuoc_file($tai_lieu['file_upload']) ?></p>
            <p><b>Mô tả:</b> <?= nl2br(htmlspecialchars($tai_lieu['mo_ta'])) ?></p>

            <h3 style="margin-top:30px;">💬 Bình luận</h3>

            <!-- Form gửi bình luận -->
            <div class="comment-box">
                <img src="https://i.pravatar.cc/40?u=<?= $_SESSION['user_id'] ?? 'guest' ?>" class="comment-avatar">
                <form id="commentForm" method="POST" action="src/Views/comment.php">
                    <textarea name="noi_dung" class="comment-input" placeholder="Viết bình luận..." required></textarea>
                    <input type="hidden" name="id_bai_chia_se" value="<?= $id_tai_lieu ?>">
                    <button type="submit" class="comment-send">Gửi</button>
                </form>
            </div>

            <!-- Danh sách bình luận -->
            <div class="comment-list" id="commentList" style="margin-top:15px;">
                <p style="text-align:center;color:#777;">Đang tải bình luận...</p>
            </div>
        </div>
    </div>
</div>

<script>
// ======== Copy link ========
function copyLink() {
    const link = "http://" + window.location.host + "/index.php?page=chitiettailieu&id=<?= $id_tai_lieu ?>";
    navigator.clipboard.writeText(link).then(() => alert("✅ Link đã copy: " + link));
}

// ======== Load comment ========
const idBai = <?= $id_tai_lieu ?>;
const commentList = document.getElementById('commentList');

function loadComments() {
    fetch('src/Views/comment.php?get_comments=1&id_bai=' + idBai)
        .then(res => res.json())
        .then(data => {
            commentList.innerHTML = '';
            if (data.success && data.comments.length) {
                data.comments.forEach(c => {
                    const div = document.createElement('div');
                    div.className = 'comment-item';
                    div.dataset.id = c.id;
                    div.innerHTML = `<b>${c.ho_ten}</b>: ${c.noi_dung}<br><small>${c.ngay_tao}</small>
                        ${c.can_delete ? `<a href="#" class="delete-comment" onclick="deleteComment(${c.id});return false;">🗑️ Xóa</a>` : ''}`;
                    commentList.appendChild(div);
                });
            } else {
                commentList.innerHTML = '<p style="text-align:center;color:#999;">Chưa có bình luận</p>';
            }
        });
}
loadComments();

// ======== Gửi bình luận AJAX ========
document.getElementById('commentForm').addEventListener('submit', e => {
    e.preventDefault();
    const noi_dung = e.target.noi_dung.value.trim();
    if (!noi_dung) return alert('Nhập nội dung!');
    fetch('src/Views/comment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'ajax=1&id_bai_chia_se=' + idBai + '&noi_dung=' + encodeURIComponent(noi_dung)
    }).then(res => res.json()).then(data => {
        if (data.success) {
            e.target.noi_dung.value = '';
            loadComments();
        } else alert(data.message);
    });
});

// ======== Xóa bình luận ========
function deleteComment(id) {
    if (!confirm('Xóa bình luận này?')) return;
    fetch('src/Views/comment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'delete_ajax=1&id_comment=' + id
    }).then(res => res.json()).then(data => {
        if (data.success) loadComments();
        else alert(data.message);
    });
}
</script>
</body>
</html>