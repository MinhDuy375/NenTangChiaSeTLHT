<?php
// tai_lieu_theo_mon.php - Trang hiển thị tài liệu theo môn học
// Đặt file này trong thư mục src/Views/
include __DIR__ . '/ketNoiDB.php';


// Lấy ID môn học từ URL
$id_mon_hoc = isset($_GET['id_mon_hoc']) ? (int)$_GET['id_mon_hoc'] : 0;

if ($id_mon_hoc <= 0) {
    header('Location: danhSachMon.php');
    exit;
}

// Lấy thông tin môn học
try {
    $sql_mon_hoc = "SELECT * FROM mon_hoc WHERE id = :id_mon_hoc";
    $stmt_mon_hoc = $pdo->prepare($sql_mon_hoc);
    $stmt_mon_hoc->execute([':id_mon_hoc' => $id_mon_hoc]);
    $thong_tin_mon_hoc = $stmt_mon_hoc->fetch();
    
    if (!$thong_tin_mon_hoc) {
        header('Location: danhSachMon.php');
        exit;
    }
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Lấy danh sách tài liệu của môn học
try {
    $sql_tai_lieu = "SELECT bcs.*, nd.ho_ten as ten_nguoi_dang 
                     FROM bai_chia_se bcs 
                     LEFT JOIN nguoi_dung nd ON bcs.id_nguoi_dung = nd.id
                     WHERE bcs.id_mon_hoc = :id_mon_hoc AND bcs.loai = 'tai_lieu'
                     ORDER BY bcs.ngay_tao DESC";
    
    $stmt_tai_lieu = $pdo->prepare($sql_tai_lieu);
    $stmt_tai_lieu->execute([':id_mon_hoc' => $id_mon_hoc]);
    $danh_sach_tai_lieu = $stmt_tai_lieu->fetchAll();
} catch (PDOException $e) {
    $danh_sach_tai_lieu = array();
    $loi = "Không thể tải danh sách tài liệu: " . $e->getMessage();
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
    <title>Tài Liệu - <?php echo lam_sach_chuoi($thong_tin_mon_hoc['ten_mon']); ?></title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            color: white;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255,255,255,0.1) 10px,
                rgba(255,255,255,0.1) 20px
            );
            animation: move 20s linear infinite;
        }
        
        @keyframes move {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        .header-content {
            position: relative;
            z-index: 2;
        }
        
        .breadcrumb {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 10px;
        }
        
        .breadcrumb a {
            color: white;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .nav-menu {
            background: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: #495057;
            margin: 0 15px;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .nav-menu a:hover {
            background: #007bff;
            color: white;
        }
        
        .content {
            padding: 30px;
        }
        
        .search-sort-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
            padding: 12px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .search-box:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
        }
        
        .sort-select {
            padding: 10px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            font-size: 14px;
        }
        
        .tai-lieu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .tai-lieu-card {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .tai-lieu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #007bff;
        }
        
        .tai-lieu-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 20px;
            position: relative;
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
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .tai-lieu-meta {
            font-size: 0.85em;
            opacity: 0.9;
        }
        
        .tai-lieu-body {
            padding: 20px;
        }
        
        .tai-lieu-summary {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
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
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-1px);
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
        
        .empty-state p {
            margin-bottom: 25px;
            font-size: 1.1em;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #6c757d;
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
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .content {
                padding: 20px;
            }
            
            .search-sort-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                min-width: auto;
            }
            
            .tai-lieu-grid {
                grid-template-columns: 1fr;
            }
            
            .tai-lieu-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="breadcrumb">
                    <a href="danhSachMon.php">🏠 Trang chủ</a> / 
                    <a href="danhSachMon.php">Danh sách môn học</a> / 
                    <?php echo lam_sach_chuoi($thong_tin_mon_hoc['ten_mon']); ?>
                </div>
                <h1>📚 <?php echo lam_sach_chuoi($thong_tin_mon_hoc['ten_mon']); ?></h1>
                <p>
                    <?php 
                    if (!empty($thong_tin_mon_hoc['mo_ta'])) {
                        echo lam_sach_chuoi($thong_tin_mon_hoc['mo_ta']);
                    } else {
                        echo "Khám phá các tài liệu học tập chất lượng cao";
                    }
                    ?>
                </p>
            </div>
        </div>
        
        <div class="nav-menu">
            <a href="dangTaiTaiLieu.php">Upload Tài Liệu</a>
            <a href="danhSachMon.php">Danh Sách Môn Học</a>
        </div>
        
        <div class="content">
            <a href="danhSachMon.php" class="back-btn">
                ← Quay lại danh sách môn học
            </a>
            
            <?php if (isset($loi)): ?>
                <div class="alert alert-danger">
                    <?php echo $loi; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($danh_sach_tai_lieu)): ?>
                <div class="empty-state">
                    <h3>📭 Chưa có tài liệu nào</h3>
                    <p>Môn học này chưa có tài liệu nào được upload.</p>
                    <a href="dangTaiTaiLieu.php" class="btn btn-primary">
                        ➕ Upload Tài Liệu Đầu Tiên
                    </a>
                </div>
            <?php else: ?>
                <div class="search-sort-container">
                    <input type="text" 
                           class="search-box" 
                           id="search-input" 
                           placeholder="🔍 Tìm kiếm tài liệu...">
                    
                    <select class="sort-select" id="sort-select" onchange="sap_xep_tai_lieu()">
                        <option value="moi-nhat">📅 Mới nhất</option>
                        <option value="cu-nhat">📅 Cũ nhất</option>
                        <option value="ten-a-z">🔤 Tên A-Z</option>
                        <option value="ten-z-a">🔤 Tên Z-A</option>
                    </select>
                </div>
                
                <div class="tai-lieu-grid" id="tai-lieu-container">
                    <?php foreach ($danh_sach_tai_lieu as $tai_lieu): ?>
                        <div class="tai-lieu-card" 
                             data-title="<?php echo strtolower(lam_sach_chuoi($tai_lieu['tieu_de'])); ?>"
                             data-date="<?php echo $tai_lieu['ngay_tao']; ?>">
                            <div class="tai-lieu-header">
                                <span class="file-icon">
                                    <?php echo lay_icon_file($tai_lieu['file_upload']); ?>
                                </span>
                                <div class="tai-lieu-title">
                                    <?php echo lam_sach_chuoi($tai_lieu['tieu_de']); ?>
                                </div>
                                <div class="tai-lieu-meta">
                                    📅 <?php echo dinh_dang_ngay($tai_lieu['ngay_tao']); ?>
                                    <?php if (!empty($tai_lieu['ten_nguoi_dang'])): ?>
                                        | 👤 <?php echo lam_sach_chuoi($tai_lieu['ten_nguoi_dang']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="tai-lieu-body">
                                <?php if (!empty($tai_lieu['tom_tat'])): ?>
                                    <div class="tai-lieu-summary">
                                        <?php echo lam_sach_chuoi($tai_lieu['tom_tat']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="tai-lieu-info">
                                    <div class="info-item">
                                        📎 <?php echo strtoupper(pathinfo($tai_lieu['file_upload'], PATHINFO_EXTENSION)); ?>
                                    </div>
                                    <div class="info-item">
                                        💾 <?php echo tinh_kich_thuoc_file($tai_lieu['file_upload']); ?>
                                    </div>
                                </div>
                                
                                <div class="tai-lieu-actions">
                                    <a href="chiTietTaiLieu.php?id=<?php echo $tai_lieu['id']; ?>" 
                                       class="btn btn-primary">
                                        👁️ Xem Chi Tiết
                                    </a>
                                    <a href="<?php echo $tai_lieu['file_upload']; ?>" 
                                       class="btn btn-success" 
                                       download 
                                       target="_blank">
                                        📥 Tải Xuống
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="no-results" class="no-results" style="display: none;">
                    <h3>🔍 Không tìm thấy kết quả</h3>
                    <p>Không có tài liệu nào phù hợp với từ khóa tìm kiếm.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function tim_kiem_tai_lieu() {
            const searchInput = document.getElementById('search-input');
            const searchTerm = searchInput.value.toLowerCase().trim();
            const taiLieuCards = document.querySelectorAll('.tai-lieu-card');
            const noResults = document.getElementById('no-results');
            let hasResults = false;
            
            taiLieuCards.forEach(card => {
                const title = card.getAttribute('data-title');
                const shouldShow = title.includes(searchTerm);
                
                if (shouldShow) {
                    card.style.display = 'block';
                    hasResults = true;
                } else {
                    card.style.display = 'none';
                }
            });
            
            if (hasResults || searchTerm.length === 0) {
                noResults.style.display = 'none';
            } else {
                noResults.style.display = 'block';
            }
        }
        
        function sap_xep_tai_lieu() {
            const container = document.getElementById('tai-lieu-container');
            const cards = Array.from(container.querySelectorAll('.tai-lieu-card'));
            const sortType = document.getElementById('sort-select').value;
            
            cards.sort((a, b) => {
                switch (sortType) {
                    case 'moi-nhat':
                        return new Date(b.getAttribute('data-date')) - new Date(a.getAttribute('data-date'));
                    case 'cu-nhat':
                        return new Date(a.getAttribute('data-date')) - new Date(b.getAttribute('data-date'));
                    case 'ten-a-z':
                        return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title'));
                    case 'ten-z-a':
                        return b.getAttribute('data-title').localeCompare(a.getAttribute('data-title'));
                    default:
                        return 0;
                }
            });
            
            // Xóa tất cả cards và thêm lại theo thứ tự mới
            cards.forEach(card => container.removeChild(card));
            cards.forEach(card => container.appendChild(card));
            
            // Thêm hiệu ứng
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        }
        
        // Event listeners
        document.getElementById('search-input').addEventListener('keyup', tim_kiem_tai_lieu);
        
        // Thêm hiệu ứng loading cho các buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.textContent.includes('Xem Chi Tiết')) {
                    this.innerHTML = '⏳ Đang tải...';
                } else if (this.textContent.includes('Tải Xuống')) {
                    this.innerHTML = '⬇️ Đang tải...';
                    setTimeout(() => {
                        this.innerHTML = '✅ Đã tải!';
                    }, 1000);
                    setTimeout(() => {
                        this.innerHTML = '📥 Tải Xuống';
                    }, 3000);
                }
            });
        });
        
        // Animate cards on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.tai-lieu-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        // Auto focus search
        document.getElementById('search-input')?.focus();
    </script>
</body>
</html>