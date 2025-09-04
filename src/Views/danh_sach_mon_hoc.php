<?php
// danh_sach_mon_hoc.php - Trang hiển thị danh sách môn học
// Đặt file này trong thư mục src/Views/
include '../../config/ket_noi_csdl.php';

// Lấy danh sách môn học cùng số lượng tài liệu
try {
    $sql = "SELECT mh.*, COUNT(bcs.id) as so_luong_tai_lieu 
            FROM mon_hoc mh 
            LEFT JOIN bai_chia_se bcs ON mh.id = bcs.id_mon_hoc AND bcs.loai = 'tai_lieu'
            GROUP BY mh.id, mh.ten_mon, mh.mo_ta
            ORDER BY mh.ten_mon";
    
    $stmt = $pdo->query($sql);
    $danh_sach_mon_hoc = $stmt->fetchAll();
} catch (PDOException $e) {
    $danh_sach_mon_hoc = array();
    $loi = "Không thể tải danh sách môn học: " . $e->getMessage();
}

// Lấy thống kê tổng quan
try {
    $sql_thong_ke = "SELECT 
                        COUNT(DISTINCT mh.id) as tong_mon_hoc,
                        COUNT(bcs.id) as tong_tai_lieu,
                        COUNT(DISTINCT bcs.id_nguoi_dung) as tong_nguoi_dung
                     FROM mon_hoc mh
                     LEFT JOIN bai_chia_se bcs ON mh.id = bcs.id_mon_hoc AND bcs.loai = 'tai_lieu'";
    
    $stmt_thong_ke = $pdo->query($sql_thong_ke);
    $thong_ke = $stmt_thong_ke->fetch();
} catch (PDOException $e) {
    $thong_ke = array('tong_mon_hoc' => 0, 'tong_tai_lieu' => 0, 'tong_nguoi_dung' => 0);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Môn Học</title>
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
            text-align: center;
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
        
        .nav-menu a:hover, .nav-menu a.active {
            background: #007bff;
            color: white;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .content {
            padding: 30px;
        }
        
        .search-container {
            margin-bottom: 30px;
        }
        
        .search-box {
            width: 100%;
            max-width: 400px;
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
        
        .mon-hoc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .mon-hoc-card {
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            cursor: pointer;
        }
        
        .mon-hoc-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #007bff;
        }
        
        .mon-hoc-header {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .mon-hoc-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        
        .mon-hoc-title {
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }
        
        .mon-hoc-count {
            font-size: 0.9em;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .mon-hoc-body {
            padding: 20px;
        }
        
        .mon-hoc-description {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .mon-hoc-actions {
            display: flex;
            gap: 10px;
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
        }
        
        .btn-outline {
            background: transparent;
            color: #007bff;
            border: 1px solid #007bff;
        }
        
        .btn-outline:hover {
            background: #007bff;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state h3 {
            font-size: 1.5em;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            margin-bottom: 25px;
        }
        
        .icon {
            font-size: 1.5em;
            margin-right: 5px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #6c757d;
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
            
            .stats-container {
                grid-template-columns: 1fr;
                padding: 20px;
                gap: 15px;
            }
            
            .mon-hoc-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Thư Viện Tài Liệu</h1>
            <p>Khám phá và tải xuống tài liệu học tập chất lượng cao</p>
        </div>
        
        <div class="nav-menu">
            <a href="upload_tai_lieu.php">Upload Tài Liệu</a>
            <a href="danh_sach_mon_hoc.php" class="active">Danh Sách Môn Học</a>
        </div>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($thong_ke['tong_mon_hoc']); ?></div>
                <div class="stat-label">Môn Học</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($thong_ke['tong_tai_lieu']); ?></div>
                <div class="stat-label">Tài Liệu</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($thong_ke['tong_nguoi_dung']); ?></div>
                <div class="stat-label">Người Đóng Góp</div>
            </div>
        </div>
        
        <div class="content">
            <?php if (isset($loi)): ?>
                <div class="alert alert-danger">
                    <?php echo $loi; ?>
                </div>
            <?php endif; ?>
            
            <div class="search-container">
                <input type="text" 
                       class="search-box" 
                       id="search-input" 
                       placeholder="🔍 Tìm kiếm môn học..."
                       onkeyup="tim_kiem_mon_hoc()">
            </div>
            
            <?php if (empty($danh_sach_mon_hoc)): ?>
                <div class="empty-state">
                    <h3>📚 Chưa có môn học nào</h3>
                    <p>Hệ thống chưa có môn học nào được thêm vào.</p>
                    <a href="upload_tai_lieu.php" class="btn btn-primary">
                        <span class="icon">➕</span> Upload Tài Liệu Đầu Tiên
                    </a>
                </div>
            <?php else: ?>
                <div class="mon-hoc-grid" id="mon-hoc-container">
                    <?php foreach ($danh_sach_mon_hoc as $mon_hoc): ?>
                        <div class="mon-hoc-card" data-ten-mon="<?php echo strtolower(lam_sach_chuoi($mon_hoc['ten_mon'])); ?>">
                            <div class="mon-hoc-header">
                                <div class="mon-hoc-title">
                                    <?php echo lam_sach_chuoi($mon_hoc['ten_mon']); ?>
                                </div>
                                <div class="mon-hoc-count">
                                    <?php echo $mon_hoc['so_luong_tai_lieu']; ?> tài liệu
                                </div>
                            </div>
                            <div class="mon-hoc-body">
                                <div class="mon-hoc-description">
                                    <?php 
                                    if (!empty($mon_hoc['mo_ta'])) {
                                        echo lam_sach_chuoi($mon_hoc['mo_ta']);
                                    } else {
                                        echo "Môn học " . lam_sach_chuoi($mon_hoc['ten_mon']) . " với nhiều tài liệu học tập hữu ích.";
                                    }
                                    ?>
                                </div>
                                <div class="mon-hoc-actions">
                                    <?php if ($mon_hoc['so_luong_tai_lieu'] > 0): ?>
                                        <a href="tai_lieu_theo_mon.php?id_mon_hoc=<?php echo $mon_hoc['id']; ?>" 
                                           class="btn btn-primary">
                                            <span class="icon">👁️</span> Xem Tài Liệu
                                        </a>
                                    <?php else: ?>
                                        <span class="btn btn-outline" style="cursor: not-allowed; opacity: 0.6;">
                                            <span class="icon">📭</span> Chưa có tài liệu
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div id="no-results" class="no-results" style="display: none;">
                    <h3>🔍 Không tìm thấy kết quả</h3>
                    <p>Không có môn học nào phù hợp với từ khóa tìm kiếm.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function tim_kiem_mon_hoc() {
            const searchInput = document.getElementById('search-input');
            const searchTerm = searchInput.value.toLowerCase().trim();
            const monHocCards = document.querySelectorAll('.mon-hoc-card');
            const noResults = document.getElementById('no-results');
            let hasResults = false;
            
            monHocCards.forEach(card => {
                const tenMon = card.getAttribute('data-ten-mon');
                const shouldShow = tenMon.includes(searchTerm);
                
                if (shouldShow) {
                    card.style.display = 'block';
                    hasResults = true;
                    
                    // Highlight tìm kiếm
                    if (searchTerm.length > 0) {
                        card.style.transform = 'translateY(-2px)';
                        card.style.boxShadow = '0 5px 20px rgba(0,123,255,0.3)';
                    } else {
                        card.style.transform = '';
                        card.style.boxShadow = '';
                    }
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Hiển thị thông báo không tìm thấy
            if (hasResults || searchTerm.length === 0) {
                noResults.style.display = 'none';
            } else {
                noResults.style.display = 'block';
            }
        }
        
        // Thêm hiệu ứng khi hover vào card
        document.querySelectorAll('.mon-hoc-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                const searchTerm = document.getElementById('search-input').value.toLowerCase().trim();
                if (searchTerm.length > 0) {
                    this.style.transform = 'translateY(-2px)';
                } else {
                    this.style.transform = '';
                }
            });
            
            // Click vào card để chuyển trang
            card.addEventListener('click', function(e) {
                // Không chuyển trang nếu click vào button
                if (e.target.closest('.btn')) return;
                
                const link = this.querySelector('.btn-primary');
                if (link && !link.style.cursor === 'not-allowed') {
                    window.location.href = link.href;
                }
            });
        });
        
        // Thêm hiệu ứng loading khi chuyển trang
        document.querySelectorAll('a[href]').forEach(link => {
            link.addEventListener('click', function() {
                const btn = this;
                if (!btn.classList.contains('btn-outline')) {
                    btn.innerHTML = '<span class="icon">⏳</span> Đang tải...';
                    btn.style.pointerEvents = 'none';
                }
            });
        });
        
        // Auto focus vào search box
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>