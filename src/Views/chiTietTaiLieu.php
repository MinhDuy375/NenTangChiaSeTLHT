<?php
// chi_tiet_tai_lieu.php - Trang chi tiết tài liệu với preview
// Đặt file này trong thư mục src/Views/
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy ID tài liệu từ URL
$id_tai_lieu = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_bai_chia_se = (int)($_GET['id'] ?? 0);

if ($id_tai_lieu <= 0) {
    header('Location: danhSachMon.php');
    exit;
}

// Lấy thông tin chi tiết tài liệu
try {
    $sql = "SELECT bcs.*, mh.ten_mon, nd.ho_ten as ten_nguoi_dang, nd.email as email_nguoi_dang
            FROM bai_chia_se bcs 
            LEFT JOIN mon_hoc mh ON bcs.id_mon_hoc = mh.id
            LEFT JOIN nguoi_dung nd ON bcs.id_nguoi_dung = nd.id
            WHERE bcs.id = :id_tai_lieu AND bcs.loai = 'tai_lieu'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_tai_lieu' => $id_tai_lieu]);
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
                            <h3>📖 Mô tả chi tiết</h3>
                            <div class="description-text">
                                <?php echo lam_sach_chuoi($tai_lieu['mo_ta']); ?>
                            </div>
                        </div>
                        <!-- Nút Like -->
                        <!-- Nút Like -->
                        <!-- Nút Like -->
                        <!-- Nút Like -->

                        <div class="reaction-box" data-id="<?php echo $tai_lieu['id']; ?>">
                            <!-- Nút Like -->
                            <div class="like-button">
                                👍 <span class="like-text">Thích</span>
                            </div>

                            <!-- Số lượt tương tác -->
                            <span class="reaction-count">
                                <?php echo lay_tong_reaction($pdo, $tai_lieu['id']); ?>
                            </span>

                            <!-- Popup cảm xúc -->
                            <div class="reaction-popup">
                                <span class="reaction" data-type="like">👍</span>
                                <span class="reaction" data-type="love">❤️</span>
                                <span class="reaction" data-type="haha">😆</span>
                                <span class="reaction" data-type="wow">😮</span>
                                <span class="reaction" data-type="sad">😢</span>
                                <span class="reaction" data-type="angry">😡</span>
                            </div>
                        </div> <!-- Kết thúc .reaction-box -->
                        <?php
                        // Lấy chi tiết reaction cho tài liệu hiện tại
                        $chiTiet = lay_chi_tiet_reaction($pdo, $tai_lieu['id']);
                        $tong = $chiTiet ? array_sum($chiTiet) : 0;
                        ?>
                        <!-- Tách chi tiết reaction ra dưới -->
                        <div class="reaction-detail">
                            <?php foreach ($chiTiet as $loai => $sl): ?>
                                <?php if ($sl > 0): ?>
                                    <span class="reaction-item">
                                        <?= htmlspecialchars($loai) ?>: <?= $sl ?>
                                    </span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <!-- Nút Like --> <!-- Nút Like --> <!-- Nút Like --> <!-- Nút Like --> <!-- Nút Like -->
                        <!-- Nút Like --> <!-- Nút Like --> <!-- Nút Like --> <!-- Nút Like --> <!-- Nút Like -->
                        <!-- Nút Like --> <!-- Nút Like -->


                        <!-- Nút copy link -->
                        <button class="share-btn" onclick="copyLink()">📋 Copy link</button>

                        <script>
                            function copyLink() {
                                var link = "<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/BTL/index.php?page=chitiettailieu&id=' . $id_bai_chia_se; ?>";

                                navigator.clipboard.writeText(link).then(function() {
                                    alert("✅ Link đã được copy: " + link);
                                }).catch(function(err) {
                                    console.error("❌ Không copy được: ", err);
                                    alert("Trình duyệt không hỗ trợ copy.");
                                });
                            }
                        </script>



                        <?php


                        $id_bai_chia_se = (int)($_GET['id'] ?? 0);

                        // Lấy danh sách comment (có thêm id để xoá, id_nguoi_dung để check quyền)
                        $sql = "SELECT bl.id, bl.noi_dung, bl.ngay_tao, bl.id_nguoi_dung, nd.ho_ten
        FROM binh_luan bl
        JOIN nguoi_dung nd ON bl.id_nguoi_dung = nd.id
        WHERE bl.id_bai_chia_se = :id_bai_chia_se
        ORDER BY bl.ngay_tao DESC";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':id_bai_chia_se' => $id_bai_chia_se]);
                        $comments = $stmt->fetchAll();
                        ?>

                        <!-- Form nhập comment -->
                        <div class="comment-box">
                            <img src="https://i.pravatar.cc/40" alt="Avatar" class="comment-avatar">
                            <div class="comment-content">
                                <form method="POST" action="comment.php">
                                    <textarea name="noi_dung" class="comment-input" placeholder="Viết bình luận..." required></textarea>
                                    <input type="hidden" name="id_bai_chia_se" value="<?php echo $id_bai_chia_se; ?>">
                                    <button type="submit" class="comment-send">Gửi</button>
                                </form>
                            </div>
                        </div>

                        <!-- Hiển thị danh sách bình luận -->
                        <div class="comment-list">
                            <?php foreach ($comments as $c): ?>
                                <div class="comment-item">
                                    <b><?php echo htmlspecialchars($c['ho_ten']); ?></b>:
                                    <?php echo nl2br(htmlspecialchars($c['noi_dung'])); ?>
                                    <br>
                                    <small><?php echo dinh_dang_ngay($c['ngay_tao']); ?></small>

                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $c['id_nguoi_dung']): ?>
                                        <!-- Nút xoá chỉ hiện với chủ comment -->
                                        <a href="comment.php?delete=<?php echo $c['id']; ?>&id_bai=<?php echo $id_bai_chia_se; ?>"
                                            onclick="return confirm('Bạn có chắc muốn xoá bình luận này?')"
                                            class="delete-comment">Xoá</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>




                </div>
            </div>
        <?php endif; ?>

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

        // ========== REACTION SYSTEM ==========
        document.querySelectorAll(".reaction-box").forEach(box => {
            let timeout;
            const likeBtn = box.querySelector(".like-button");
            const popup = box.querySelector(".reaction-popup");
            const countSpan = box.querySelector(".reaction-count");
            const detailDiv = box.closest('.tai-lieu-body')?.querySelector('.reaction-detail');

            // Hover để hiện popup
            likeBtn.addEventListener("mouseenter", () => {
                timeout = setTimeout(() => {
                    popup.style.display = "flex";
                }, 500);
            });

            likeBtn.addEventListener("mouseleave", () => {
                clearTimeout(timeout);
            });

            popup.addEventListener("mouseleave", () => {
                popup.style.display = "none";
            });

            // Click vào reaction
            popup.querySelectorAll(".reaction").forEach(r => {
                r.addEventListener("click", () => {
                    const type = r.dataset.type;
                    const postId = box.dataset.id;

                    // Hiển thị loading
                    const originalText = countSpan.textContent;
                    countSpan.textContent = "...";

                    fetch("src/Views/ajax_reaction.php", {
                            method: "POST",
                            credentials: "same-origin",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "id_bai=" + postId + "&type=" + type
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Cập nhật tổng số
                                countSpan.textContent = data.tong;

                                // Cập nhật chi tiết
                                if (detailDiv && data.chi_tiet) {
                                    let html = '';
                                    const emojiMap = {
                                        'like': '👍',
                                        'love': '❤️',
                                        'care': '🤗',
                                        'haha': '😆',
                                        'wow': '😮',
                                        'sad': '😢',
                                        'angry': '😡'
                                    };

                                    for (let [loai, sl] of Object.entries(data.chi_tiet)) {
                                        if (sl > 0) {
                                            html += `<span class="reaction-item">${emojiMap[loai] || loai} ${sl}</span> `;
                                        }
                                    }
                                    detailDiv.innerHTML = html;
                                }

                                // Hiệu ứng animation
                                countSpan.style.transform = 'scale(1.3)';
                                setTimeout(() => {
                                    countSpan.style.transform = 'scale(1)';
                                }, 200);

                                popup.style.display = "none";
                            } else {
                                alert('❌ ' + (data.error || 'Có lỗi xảy ra'));
                                countSpan.textContent = originalText;
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('⚠️ Không kết nối được server');
                            countSpan.textContent = originalText;
                        });
                });
            });
        });

        // ========== COMMENT SYSTEM ==========
        const commentForm = document.querySelector('.comment-box form');
        const commentList = document.querySelector('.comment-list');
        const commentInput = document.querySelector('.comment-input');
        const idBaiChiaSe = <?php echo $id_bai_chia_se; ?>;

        // Tải danh sách comment khi trang load
        loadComments();

        // Gửi comment
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const noiDung = commentInput.value.trim();
                if (!noiDung) {
                    alert('Vui lòng nhập nội dung bình luận');
                    return;
                }

                const submitBtn = this.querySelector('.comment-send');
                submitBtn.disabled = true;
                submitBtn.textContent = '⏳ Đang gửi...';

                fetch('src/Views/comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'ajax=1&noi_dung=' + encodeURIComponent(noiDung) +
                            '&id_bai_chia_se=' + idBaiChiaSe
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Thêm comment mới vào đầu danh sách
                            addCommentToList(data.comment, true);
                            commentInput.value = '';

                            // Hiệu ứng thành công
                            submitBtn.textContent = '✅ Đã gửi!';
                            setTimeout(() => {
                                submitBtn.textContent = 'Gửi';
                                submitBtn.disabled = false;
                            }, 1500);
                        } else {
                            alert('❌ ' + data.message);
                            submitBtn.textContent = 'Gửi';
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('⚠️ Không kết nối được server');
                        submitBtn.textContent = 'Gửi';
                        submitBtn.disabled = false;
                    });
            });
        }

        // Tải danh sách comment
        function loadComments() {
            if (!commentList) return;

            fetch('comment.php?get_comments=1&id_bai=' + idBaiChiaSe)
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

        // Thêm comment vào danh sách
        function addCommentToList(comment, isNew) {
            if (!commentList) return;

            // Xóa message "chưa có bình luận"
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

            div.innerHTML = `
        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
            <img src="https://i.pravatar.cc/40?u=${comment.id_nguoi_dung}" 
                 alt="Avatar" 
                 style="width: 40px; height: 40px; border-radius: 50%;">
            <div style="flex: 1;">
                <div style="background: #f0f2f5; padding: 10px 15px; border-radius: 18px;">
                    <b style="color: #050505;">${comment.ho_ten}</b>
                    <p style="margin: 5px 0 0 0; color: #050505;">${comment.noi_dung.replace(/\n/g, '<br>')}</p>
                </div>
                <div style="padding: 5px 15px; font-size: 12px; color: #65676b;">
                    <span>📅 ${comment.ngay_tao}</span>
                    ${comment.can_delete ? `
                        <a href="#" 
                           onclick="deleteComment(${comment.id}); return false;" 
                           style="margin-left: 15px; color: #dc3545; text-decoration: none;">
                           🗑️ Xóa
                        </a>
                    ` : ''}
                </div>
            </div>
        </div>
    `;

            if (isNew) {
                commentList.insertBefore(div, commentList.firstChild);
            } else {
                commentList.appendChild(div);
            }
        }

        // Xóa comment
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
                        // Hiệu ứng xóa
                        commentItem.style.transition = 'all 0.3s';
                        commentItem.style.opacity = '0';
                        commentItem.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            commentItem.remove();

                            // Kiểm tra nếu không còn comment
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

        // ========== COPY LINK ==========
        function copyLink() {
            const link = "<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/BTL/index.php?page=chitiettailieu&id=' . $id_bai_chia_se; ?>";

            navigator.clipboard.writeText(link).then(() => {
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '✅ Đã copy!';
                btn.style.backgroundColor = '#28a745';

                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.backgroundColor = '';
                }, 2000);
            }).catch(err => {
                console.error("Không copy được: ", err);
                alert("Trình duyệt không hỗ trợ copy.");
            });
        }
    </script>
</body>

</html>