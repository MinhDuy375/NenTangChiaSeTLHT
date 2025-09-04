<?php
// chi_tiet_tai_lieu.php - Trang chi tiết tài liệu với preview
// Đặt file này trong thư mục src/Views/
include '../../config/ket_noi_csdl.php';

// Lấy ID tài liệu từ URL
$id_tai_lieu = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_tai_lieu <= 0) {
    header('Location: danh_sach_mon_hoc.php');
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
        header('Location: danh_sach_mon_hoc.php');
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
function tao_url_preview($duong_dan_file) {
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
    $file_url = $base_url . $duong_dan_file;
    return 'https://docs.google.com/viewer?url=' . urlencode($file_url) . '&embedded=true';
}

// Hàm lấy icon theo loại file
function lay_icon_file($duong_dan_file) {
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
function tinh_kich_thuoc_file($duong_dan_file) {
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
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
            background: rgba(255,255,255,0.3);
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
        
        .preview-iframe {
            flex: 1;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: white;
            min-height: 600px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            box-shadow: 0 2px 8px rgba(0,123,255,0.15);
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
            box-shadow: 0 3px 10px rgba(0,123,255,0.3);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(40,167,69,0.3);
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
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                <a href="danh_sach_mon_hoc.php">🏠 Trang chủ</a> / 
                <a href="danh_sach_mon_hoc.php">Danh sách môn học</a> / 
                <a href="tai_lieu_theo_mon.php?id_mon_hoc=<?php echo $tai_lieu['id_mon_hoc']; ?>">
                    <?php echo lam_sach_chuoi($tai_lieu['ten_mon']); ?>
                </a> / 
                Chi tiết tài liệu
            </div>
            <div class="header-title">
                <a href="tai_lieu_theo_mon.php?id_mon_hoc=<?php echo $tai_lieu['id_mon_hoc']; ?>" 
                   class="back-btn">
                    ← Quay lại
                </a>
                <?php echo lay_icon_file($tai_lieu['file_upload']); ?>
                <?php echo lam_sach_chuoi($tai_lieu['tieu_de']); ?>
            </div>
        </div>
        
        <div class="main-content">
            <div class="preview-section">
                <div class="preview-header">
                    <div class="preview-title">
                        <?php echo lay_icon_file($tai_lieu['file_upload']); ?>
                        Xem trước tài liệu
                    </div>
                    <div class="preview-meta">
                        📅 <?php echo dinh_dang_ngay($tai_lieu['ngay_tao']); ?> |
                        📎 <?php echo strtoupper(pathinfo($tai_lieu['file_upload'], PATHINFO_EXTENSION)); ?> |
                        💾 <?php echo tinh_kich_thuoc_file($tai_lieu['file_upload']); ?>
                    </div>
                </div>
                
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
                    <?php endif; ?>
                    
                    <?php if (!empty($tai_lieu_lien_quan)): ?>
                    <div class="related-section">
                        <h3>🔗 Tài liệu liên quan</h3>
                        <?php foreach ($tai_lieu_lien_quan as $lien_quan): ?>
                        <div class="related-item" 
                             onclick="window.location.href='chi_tiet_tai_lieu.php?id=<?php echo $lien_quan['id']; ?>'">
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
        function hien_thi_preview() {
            document.getElementById('loading-preview').style.display = 'none';
            document.getElementById('preview-iframe').style.display = 'block';
        }
        
        // Xử lý lỗi khi không thể load preview
        document.getElementById('preview-iframe').addEventListener('error', function() {
            document.getElementById('loading-preview').innerHTML = `
                <div style="text-align: center; color: #dc3545;">
                    <h4>❌ Không thể hiển thị preview</h4>
                    <p>File có thể không hỗ trợ xem trước hoặc có vấn đề với kết nối.</p>
                    <a href="<?php echo $tai_lieu['file_upload']; ?>" 
                       class="btn btn-primary" 
                       target="_blank">
                        📄 Mở file trực tiếp
                    </a>
                </div>
            `;
        });
        
        // Thêm hiệu ứng loading cho buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.textContent.includes('Tải Xuống')) {
                    const originalText = this.innerHTML;
                    this.innerHTML = '⬇️ Đang tải...';
                    setTimeout(() => {
                        this.innerHTML = '✅ Đã tải!';
                    }, 1000);
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 3000);
                }
            });
        });
        
        // Auto-retry nếu preview không load được
        setTimeout(() => {
            const iframe = document.getElementById('preview-iframe');
            const loading = document.getElementById('loading-preview');
            
            if (loading.style.display !== 'none') {
                // Thử load lại với URL khác
                iframe.src = '<?php echo $tai_lieu['file_upload']; ?>';
                loading.innerHTML = `
                    <div class="loading-spinner"></div>
                    <p>Đang thử phương thức khác...</p>
                `;
                
                // Nếu vẫn không được sau 10s thì hiển thị lỗi
                setTimeout(() => {
                    if (loading.style.display !== 'none') {
                        loading.innerHTML = `
                            <div style="text-align: center; color: #dc3545;">
                                <h4>❌ Không thể hiển thị preview</h4>
                                <p>Vui lòng tải file về để xem nội dung.</p>
                                <a href="<?php echo $tai_lieu['file_upload']; ?>" 
                                   class="btn btn-primary" 
                                   download target="_blank">
                                    📥 Tải file ngay
                                </a>
                            </div>
                        `;
                    }
                }, 10000);
            }
        }, 8000);
    </script>
</body>
</html>