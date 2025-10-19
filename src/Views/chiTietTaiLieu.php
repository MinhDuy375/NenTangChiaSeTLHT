<?php
// chi_tiet_tai_lieu.php - Trang chi tiết tài liệu với preview
// Đặt file này trong thư mục src/Views/

// Khởi tạo session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';

include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy ID tài liệu từ URL
$id_tai_lieu = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_bai_chia_se = (int)($_GET['id'] ?? 0);
$userId = $is_logged_in ? (int)$_SESSION['user_id'] : 0;

if ($id_tai_lieu <= 0) {
    header('Location: danhSachMon.php');
    exit;
}

// ===================================
// SQL QUERY ĐÃ CẬP NHẬT
// ===================================
try {
    $sql = "SELECT 
                bcs.*, 
                mh.ten_mon, 
                nd.ho_ten as ten_nguoi_dang, 
                nd.email as email_nguoi_dang,
                -- Đếm tổng reaction từ bảng `reaction`
                (SELECT COUNT(*) FROM reaction r WHERE r.id_bai_chia_se = bcs.id) AS tong_so_reaction,
                -- Đếm tổng dislike từ bảng `tuong_tac`
                (SELECT COUNT(*) FROM tuong_tac t WHERE t.id_bai_chia_se = bcs.id AND t.loai = 'dislike') AS so_luot_dislike,
                -- Lấy chi tiết các loại reaction để hiển thị icon
                (SELECT GROUP_CONCAT(CONCAT(r.loai_cam_xuc, ':', r.count) SEPARATOR ';')
                 FROM (SELECT loai_cam_xuc, COUNT(*) as count FROM reaction WHERE id_bai_chia_se = bcs.id GROUP BY loai_cam_xuc) r
                ) AS chi_tiet_reaction,
                -- Lấy reaction của user hiện tại
                (SELECT r.loai_cam_xuc FROM reaction r WHERE r.id_bai_chia_se = bcs.id AND r.id_nguoi_dung = :uid) AS user_reaction,
                -- Kiểm tra user hiện tại đã dislike chưa
                EXISTS(SELECT 1 FROM tuong_tac t WHERE t.id_bai_chia_se = bcs.id AND t.id_nguoi_dung = :uid AND t.loai = 'dislike') AS da_dislike
            FROM bai_chia_se bcs 
            LEFT JOIN mon_hoc mh ON bcs.id_mon_hoc = mh.id
            LEFT JOIN nguoi_dung nd ON bcs.id_nguoi_dung = nd.id
            WHERE bcs.id = :id_tai_lieu AND bcs.loai = 'tai_lieu'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_tai_lieu' => $id_tai_lieu, ':uid' => $userId]);
    $tai_lieu = $stmt->fetch();

    if (!$tai_lieu) {
        header('Location: danhSachMon.php');
        exit;
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Lấy các tài liệu liên quan (cùng môn học)
try {
    $sql_lien_quan = "SELECT bcs.id, bcs.tieu_de, bcs.tom_tat, bcs.file_upload, bcs.ngay_tao
                      FROM bai_chia_se bcs 
                      WHERE bcs.id_mon_hoc = :id_mon_hoc 
                      AND bcs.id != :id_tai_lieu 
                      AND bcs.loai = 'tai_lieu'
                      ORDER BY bcs.ngay_tao DESC 
                      LIMIT 4";

    $stmt_lien_quan = $pdo->prepare($sql_lien_quan);
    $stmt_lien_quan->execute([
        ':id_mon_hoc' => $tai_lieu['id_mon_hoc'],
        ':id_tai_lieu' => $id_tai_lieu
    ]);
    $tai_lieu_lien_quan = $stmt_lien_quan->fetchAll();
} catch (PDOException $e) {
    $tai_lieu_lien_quan = array();
}

// Lấy comments
$comments_sql = "SELECT c.*, u.ho_ten as commenter_name, c.id_nguoi_dung
                 FROM binh_luan c 
                 LEFT JOIN nguoi_dung u ON c.id_nguoi_dung = u.id 
                 WHERE c.id_bai_chia_se = :id 
                 ORDER BY c.ngay_tao DESC";
$comments_stmt = $pdo->prepare($comments_sql);
$comments_stmt->execute(['id' => $id_tai_lieu]);
$comments = $comments_stmt->fetchAll();

// Hàm tạo URL preview cho Google Docs Viewer
function tao_url_preview($duong_dan_file)
{
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
    $file_url = $base_url . $duong_dan_file;
    return 'https://docs.google.com/viewer?url=' . urlencode($file_url) . '&embedded=true';
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
        } else {
            return $kich_thuoc . ' bytes';
        }
    }
    return 'Không xác định';
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
            max-width: 1600px;
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

        .preview-container {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
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

        /* ==================== REACTION STYLES ==================== */
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

        .like-button.active.like {
            color: #007bff;
            font-weight: bold;
            border-color: #007bff;
            background: #e3f2fd;
        }

        .like-button.active.love {
            color: #e0245e;
            font-weight: bold;
            border-color: #e0245e;
            background: #fce4ec;
        }

        .like-button.active.care {
            color: #f7b125;
            font-weight: bold;
            border-color: #f7b125;
            background: #fff8e1;
        }

        .like-button.active.haha {
            color: #f7b125;
            font-weight: bold;
            border-color: #f7b125;
            background: #fff8e1;
        }

        .like-button.active.wow {
            color: #f7b125;
            font-weight: bold;
            border-color: #f7b125;
            background: #fff8e1;
        }

        .like-button.active.sad {
            color: #f7b125;
            font-weight: bold;
            border-color: #f7b125;
            background: #fff8e1;
        }

        .like-button.active.angry {
            color: #e0245e;
            font-weight: bold;
            border-color: #e0245e;
            background: #fce4ec;
        }

        .reaction-box {
            position: relative;
            flex: 1;
        }

        .reaction-box .action-button {
            width: 100%;
        }

        .reaction-popup {
            position: absolute;
            bottom: 100%;
            left: 0;
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
            animation: popup-appear 0.2s ease-out forwards;
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
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .loading-preview {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            color: #6c757d;
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

        /* ==================== COMMENT STYLES ==================== */
        .comments-section {
            padding: 25px;
            background: #fafafa;
            border-top: 1px solid #e9ecef;
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
            resize: vertical;
            min-height: 45px;
        }

        .comment-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .comment-list {
            margin-top: 20px;
        }

        .comment-item {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .comment-item:hover {
            background-color: #f8f9fa;
        }

        .comment-content-wrapper {
            flex: 1;
        }

        .comment-bubble {
            background: white;
            padding: 10px 15px;
            border-radius: 18px;
            display: inline-block;
            max-width: 100%;
        }

        .comment-author {
            font-weight: 600;
            color: #050505;
            margin-bottom: 5px;
        }

        .comment-text {
            color: #050505;
            margin: 5px 0 0 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .comment-meta {
            padding: 5px 15px;
            font-size: 12px;
            color: #65676b;
        }

        .delete-comment {
            margin-left: 15px;
            color: #dc3545;
            text-decoration: none;
            cursor: pointer;
        }

        .delete-comment:hover {
            text-decoration: underline;
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
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="breadcrumb">
                <a href="index.php?page=monhoc">Danh sách môn học</a> /
                <a href="index.php?page=tailieumon&id_mon_hoc=<?php echo $tai_lieu['id_mon_hoc']; ?>">
                    <?php echo lam_sach_chuoi($tai_lieu['ten_mon']); ?>
                </a> /
                Chi tiết tài liệu
            </div>
            <div class="header-title">
                <a href="index.php?page=tailieumon&id_mon_hoc=<?php echo $tai_lieu['id_mon_hoc']; ?>"
                    class="back-btn">
                    ← Quay lại
                </a>
                <?php echo lay_icon_file($tai_lieu['file_upload']); ?>
                <?php echo lam_sach_chuoi($tai_lieu['tieu_de']); ?>
            </div>
        </div>

        <div class="main-content">
            <div class="preview-section">
                <div class="preview-container">
                    <div class="loading-preview" id="loading-preview">
                        <div class="loading-spinner"></div>
                        <p>Đang tải preview...</p>
                    </div>

                    <iframe id="preview-iframe"
                        class="preview-iframe"
                        src="<?php echo tao_url_preview($tai_lieu['file_upload']); ?>"
                        style="display: none;"
                        onload="hien_thi_preview()">
                    </iframe>

                    <div class="preview-actions">
                        <a href="<?php echo $tai_lieu['file_upload']; ?>"
                            class="btn btn-success"
                            download
                            target="_blank">
                            📥 Tải Xuống
                        </a>
                        <a href="<?php echo $tai_lieu['file_upload']; ?>"
                            class="btn btn-primary"
                            target="_blank">
                            🔗 Mở File Gốc
                        </a>
                    </div>
                </div>
            </div>

            <div class="details-section">
                <div class="details-content">
                    <div class="info-card">
                        <h3>📋 Thông tin cơ bản</h3>
                        <div class="info-item">
                            <span class="info-label">Tên file:</span>
                            <span class="info-value">
                                <?php echo basename($tai_lieu['file_upload']); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Loại file:</span>
                            <span class="info-value">
                                <?php echo strtoupper(pathinfo($tai_lieu['file_upload'], PATHINFO_EXTENSION)); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Kích thước:</span>
                            <span class="info-value">
                                <?php echo tinh_kich_thuoc_file($tai_lieu['file_upload']); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Ngày upload:</span>
                            <span class="info-value">
                                <?php echo dinh_dang_ngay($tai_lieu['ngay_tao']); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Môn học:</span>
                            <span class="info-value">
                                <?php echo lam_sach_chuoi($tai_lieu['ten_mon']); ?>
                            </span>
                        </div>
                        <?php if (!empty($tai_lieu['ten_nguoi_dang'])): ?>
                            <div class="info-item">
                                <span class="info-label">Người đăng:</span>
                                <span class="info-value">
                                    <?php echo lam_sach_chuoi($tai_lieu['ten_nguoi_dang']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($tai_lieu['tom_tat'])): ?>
                        <div class="description-section">
                            <h3>📝 Tóm tắt</h3>
                            <div class="description-text">
                                <?php echo lam_sach_chuoi($tai_lieu['tom_tat']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($tai_lieu['mo_ta'])): ?>
                        <div class="description-section">
                            <h3>Mô tả</h3>
                            <div class="description-text">
                                <?php echo lam_sach_chuoi($tai_lieu['mo_ta']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- ==================== REACTION & STATS ==================== -->
                    <div class="post-stats" data-post-id="<?= $tai_lieu['id'] ?>">
                        <div class="reaction-summary">
                            <div class="reaction-icons"></div>
                            <span class="reaction-count"></span>
                        </div>
                        <div class="right-stats" style="display: flex; gap: 15px;">
                            <span class="dislike-count"></span>
                            <span class="comment-count">💬 <?= count($comments ?? []) ?> bình luận</span>
                        </div>
                    </div>

                    <!-- ==================== ACTION BUTTONS ==================== -->
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
                        <button class="action-button share-button" onclick="copyLink()">
                            <span>📤</span> Chia sẻ
                        </button>
                    </div>

                    <!-- ==================== COMMENTS SECTION ==================== -->
                    <div class="comments-section">
                        <h3 class="comments-header">💬 Bình luận (<?= count($comments) ?>)</h3>

                        <?php if ($is_logged_in): ?>
                            <div class="comment-input-container">
                                <div class="comment-avatar">
                                    <?= strtoupper(substr($username, 0, 1)) ?>
                                </div>
                                <textarea
                                    class="comment-input"
                                    placeholder="Viết bình luận..."
                                    rows="1"></textarea>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 20px; background: white; border-radius: 12px;">
                                <p style="color: #666; margin-bottom: 10px;">Đăng nhập để bình luận</p>
                                <a href="index.php?page=login" class="btn btn-primary">Đăng nhập</a>
                            </div>
                        <?php endif; ?>

                        <div class="comment-list">
                            <?php if (empty($comments)): ?>
                                <p style="text-align:center;color:#999;padding:20px;">Chưa có bình luận nào</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($tai_lieu_lien_quan)): ?>
                        <div class="related-section">
                            <h3>🔗 Tài liệu liên quan</h3>
                            <?php foreach ($tai_lieu_lien_quan as $lien_quan): ?>
                                <div class="related-item"
                                    onclick="window.location.href='index.php?page=chitiettailieu&id=<?php echo $lien_quan['id']; ?>'">
                                    <div class="related-title">
                                        <?php echo lay_icon_file($lien_quan['file_upload']); ?>
                                        <?php echo lam_sach_chuoi($lien_quan['tieu_de']); ?>
                                    </div>
                                    <div class="related-meta">
                                        📅 <?php echo dinh_dang_ngay($lien_quan['ngay_tao']); ?>
                                        <?php if (!empty($lien_quan['tom_tat'])): ?>
                                            <br><?php echo substr(lam_sach_chuoi($lien_quan['tom_tat']), 0, 100); ?>...
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ========== PREVIEW IFRAME ==========
        function hien_thi_preview() {
            document.getElementById('loading-preview').style.display = 'none';
            document.getElementById('preview-iframe').style.display = 'block';
        }

        document.getElementById('preview-iframe')?.addEventListener('error', function() {
            document.getElementById('loading-preview').innerHTML = `
                <div style="text-align: center; color: #dc3545;">
                    <h4>❌ Không thể hiển thị preview</h4>
                    <p>File có thể không hỗ trợ xem trước hoặc có vấn đề với kết nối.</p>
                    <a href="<?php echo $tai_lieu['file_upload']; ?>" 
                       class="btn btn-primary" target="_blank">
                        📄 Mở file trực tiếp
                    </a>
                </div>
            `;
        });

        // ========== COPY LINK ==========
        function copyLink() {
            const link = "<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/BTL/index.php?page=chitiettailieu&id=' . $id_bai_chia_se; ?>";

            navigator.clipboard.writeText(link).then(() => {
                const btn = event.target.closest('.share-button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<span>✅</span> Đã copy!';
                btn.style.backgroundColor = '#28a745';
                btn.style.color = 'white';

                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.style.backgroundColor = '';
                    btn.style.color = '';
                }, 2000);
            }).catch(err => {
                console.error("Không copy được: ", err);
                alert("Trình duyệt không hỗ trợ copy.");
            });
        }

        // ========== REACTION SYSTEM ==========
        document.addEventListener('DOMContentLoaded', function() {
            const postStats = document.querySelector('.post-stats');
            if (!postStats) return;

            const postId = postStats.dataset.postId;
            const likeButton = document.querySelector('.like-button');
            const dislikeButton = document.querySelector('.dislike-button');
            const reactionBox = document.querySelector('.reaction-box');
            const reactionPopup = document.querySelector('.reaction-popup');
            const reactions = document.querySelectorAll('.reaction');
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
                reaction: <?= json_encode($tai_lieu['user_reaction'] ?? null) ?>,
                disliked: <?= json_encode((bool)$tai_lieu['da_dislike']) ?>
            };

            async function handleInteraction(type) {
                if (!isLoggedIn) {
                    alert('Bạn cần đăng nhập để thực hiện hành động này.');
                    return;
                }

                try {
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
                // Update like button
                likeButton.classList.remove('active', ...Object.values(reactionMap).map(r => r.colorClass));
                if (currentUserState.reaction && reactionMap[currentUserState.reaction]) {
                    const reactionInfo = reactionMap[currentUserState.reaction];
                    likeButton.classList.add('active', reactionInfo.colorClass);
                    likeButton.innerHTML = `<span>${reactionInfo.emoji}</span> ${reactionInfo.text}`;
                } else {
                    likeButton.innerHTML = `<span>👍</span> Thích`;
                }

                // Update dislike button
                dislikeButton.classList.toggle('active-dislike', currentUserState.disliked);

                // Update reaction display
                const {
                    reactions,
                    dislikes
                } = data;
                const reactionIconsContainer = document.querySelector('.reaction-icons');
                const reactionCountSpan = document.querySelector('.reaction-count');
                const dislikeCountSpan = document.querySelector('.dislike-count');

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

            // Reaction popup hover
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

            // Reaction click
            reactions.forEach(reaction => {
                reaction.addEventListener('click', () => {
                    const type = reaction.dataset.type;
                    handleInteraction(type);
                    reactionPopup.classList.remove('show');
                });
            });

            // Like button click
            likeButton.addEventListener('click', () => {
                if (currentUserState.reaction) {
                    handleInteraction('remove_reaction');
                } else {
                    handleInteraction('like');
                }
            });

            // Dislike button click
            dislikeButton.addEventListener('click', () => {
                if (currentUserState.disliked) {
                    handleInteraction('remove_dislike');
                } else {
                    handleInteraction('dislike');
                }
            });

            // Initialize UI
            const initialData = {
                reactions: {
                    total: <?= $tai_lieu['tong_so_reaction'] ?? 0 ?>,
                    details: {
                        <?php
                        if (!empty($tai_lieu['chi_tiet_reaction'])) {
                            $details = [];
                            $pairs = explode(';', $tai_lieu['chi_tiet_reaction']);
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
                    total: <?= $tai_lieu['so_luot_dislike'] ?? 0 ?>
                }
            };
            updateUI(initialData);
        });

        // ========== COMMENT SYSTEM ==========
        const commentInput = document.querySelector('.comment-input');
        const commentList = document.querySelector('.comment-list');
        const idBaiChiaSe = <?php echo $id_tai_lieu; ?>;
        const isLoggedIn = <?= json_encode($is_logged_in) ?>;
        const currentUserId = <?= json_encode($userId) ?>;

        // Load comments
        loadComments();

        // Comment input handler
        if (commentInput) {
            // Auto-resize textarea
            commentInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            // Submit on Enter (without Shift)
            commentInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    submitComment();
                }
            });
        }

        // Focus comment input when comment button clicked
        document.getElementById('commentBtn')?.addEventListener('click', function() {
            commentInput?.focus();
        });

        async function submitComment() {
            if (!isLoggedIn) {
                alert('Bạn cần đăng nhập để bình luận');
                return;
            }

            const noiDung = commentInput.value.trim();
            if (!noiDung) {
                alert('Vui lòng nhập nội dung bình luận');
                return;
            }

            const originalValue = commentInput.value;
            commentInput.disabled = true;
            commentInput.placeholder = '⏳ Đang gửi...';

            try {
                const response = await fetch('src/Views/comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'ajax=1&noi_dung=' + encodeURIComponent(noiDung) +
                        '&id_bai_chia_se=' + idBaiChiaSe
                });

                const data = await response.json();
                if (data.success) {
                    addCommentToList(data.comment, true);
                    commentInput.value = '';
                    commentInput.style.height = 'auto';

                    // Update comment count
                    const commentCountSpan = document.querySelector('.comment-count');
                    if (commentCountSpan) {
                        const match = commentCountSpan.textContent.match(/\d+/);
                        const currentCount = match ? parseInt(match[0]) : 0;
                        commentCountSpan.textContent = `💬 ${currentCount + 1} bình luận`;
                    }

                    commentInput.placeholder = '✅ Đã gửi!';
                    setTimeout(() => {
                        commentInput.placeholder = 'Viết bình luận...';
                    }, 1500);
                } else {
                    alert('❌ ' + data.message);
                    commentInput.value = originalValue;
                }
            } catch (error) {
                console.error(error);
                alert('⚠️ Không kết nối được server');
                commentInput.value = originalValue;
            } finally {
                commentInput.disabled = false;
                commentInput.focus();
            }
        }

        function loadComments() {
            if (!commentList) return;

            fetch('src/Views/comment.php?get_comments=1&id_bai=' + idBaiChiaSe)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        commentList.innerHTML = '';
                        if (data.comments.length === 0) {
                            commentList.innerHTML = '<p style="text-align:center;color:#999;padding:20px;">Chưa có bình luận nào</p>';
                        } else {
                            data.comments.forEach(comment => {
                                addCommentToList(comment, false);
                            });
                        }
                    }
                })
                .catch(err => console.error(err));
        }

        function addCommentToList(comment, isNew) {
            if (!commentList) return;

            // Remove empty message
            const emptyMsg = commentList.querySelector('p');
            if (emptyMsg) emptyMsg.remove();

            const div = document.createElement('div');
            div.className = 'comment-item';
            div.dataset.id = comment.id;

            if (isNew) {
                div.style.backgroundColor = '#e7f3ff';
                setTimeout(() => {
                    div.style.transition = 'background-color 1s';
                    div.style.backgroundColor = '';
                }, 100);
            }

            const firstLetter = comment.commenter_name ? comment.commenter_name.charAt(0).toUpperCase() : 'A';
            const canDelete = isLoggedIn && currentUserId == comment.id_nguoi_dung;

            div.innerHTML = `
                <div class="comment-avatar">${firstLetter}</div>
                <div class="comment-content-wrapper">
                    <div class="comment-bubble">
                        <div class="comment-author">${comment.commenter_name || 'Ẩn danh'}</div>
                        <p class="comment-text">${comment.noi_dung.replace(/\n/g, '<br>')}</p>
                    </div>
                    <div class="comment-meta">
                        <span>📅 ${comment.ngay_tao}</span>
                        ${canDelete ? `
                            <a href="#" 
                               onclick="deleteComment(${comment.id}); return false;" 
                               class="delete-comment">
                               🗑️ Xóa
                            </a>
                        ` : ''}
                    </div>
                </div>
            `;

            if (isNew) {
                commentList.insertBefore(div, commentList.firstChild);
            } else {
                commentList.appendChild(div);
            }
        }

        function deleteComment(commentId) {
            if (!confirm('Bạn có chắc muốn xóa bình luận này?')) return;

            const commentItem = document.querySelector(`.comment-item[data-id="${commentId}"]`);

            fetch('src/Views/comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'delete_ajax=1&id_comment=' + commentId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        commentItem.style.transition = 'all 0.3s';
                        commentItem.style.opacity = '0';
                        commentItem.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            commentItem.remove();

                            // Update comment count
                            const commentCountSpan = document.querySelector('.comment-count');
                            if (commentCountSpan) {
                                const match = commentCountSpan.textContent.match(/\d+/);
                                const currentCount = match ? parseInt(match[0]) : 0;
                                commentCountSpan.textContent = `💬 ${Math.max(0, currentCount - 1)} bình luận`;
                            }

                            if (commentList.children.length === 0) {
                                commentList.innerHTML = '<p style="text-align:center;color:#999;padding:20px;">Chưa có bình luận nào</p>';
                            }
                        }, 300);
                    } else {
                        alert('❌ ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('⚠️ Không kết nối được server');
                });
        }
    </script>
</body>

</html>