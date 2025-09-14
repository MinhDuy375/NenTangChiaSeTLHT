
<?php
// Kết nối database để lấy thống kê
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy thống kê tổng quan
try {
    $sql_thong_ke = "SELECT 
                        COUNT(DISTINCT mh.id) as tong_mon_hoc,
                        COUNT(bcs.id) as tong_tai_lieu,
                        COUNT(DISTINCT bcs.id_nguoi_dung) as tong_nguoi_dung
                     FROM mon_hoc mh
                     LEFT JOIN bai_chia_se bcs ON mh.id = bcs.id_mon_hoc AND bcs.loai = 'tai_lieu'";
    
    $stmt_thong_ke = $pdo->query($sql_thong_ke);
    $thong_ke = $stmt_thong_ke->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $thong_ke = array('tong_mon_hoc' => 0, 'tong_tai_lieu' => 0, 'tong_nguoi_dung' => 0);
}

// Lấy tài liệu mới nhất
try {
    $sql_tai_lieu_moi = "SELECT bcs.*, mh.ten_mon, nd.ho_ten 
                         FROM bai_chia_se bcs
                         JOIN mon_hoc mh ON bcs.id_mon_hoc = mh.id
                         LEFT JOIN nguoi_dung nd ON bcs.id_nguoi_dung = nd.id
                         WHERE bcs.loai = 'tai_lieu'
                         ORDER BY bcs.ngay_tao DESC
                         LIMIT 6";
    
    $stmt_tai_lieu_moi = $pdo->query($sql_tai_lieu_moi);
    $tai_lieu_moi = $stmt_tai_lieu_moi->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tai_lieu_moi = array();
}

// Lấy môn học phổ biến nhất
try {
    $sql_mon_pho_bien = "SELECT mh.*, COUNT(bcs.id) as so_tai_lieu
                         FROM mon_hoc mh
                         LEFT JOIN bai_chia_se bcs ON mh.id = bcs.id_mon_hoc AND bcs.loai = 'tai_lieu'
                         GROUP BY mh.id
                         ORDER BY so_tai_lieu DESC
                         LIMIT 4";
    
    $stmt_mon_pho_bien = $pdo->query($sql_mon_pho_bien);
    $mon_hoc_pho_bien = $stmt_mon_pho_bien->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mon_hoc_pho_bien = array();
}
?>

<style>
    .homepage-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: calc(100vh - 70px);
    }
    
    .hero-section {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
        color: white;
        padding: 80px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="white" opacity="0.1"><circle cx="100" cy="50" r="40"/><circle cx="300" cy="150" r="60"/><circle cx="500" cy="80" r="35"/><circle cx="700" cy="200" r="45"/><circle cx="900" cy="120" r="30"/></svg>');
        animation: float 12s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .hero-title {
        font-size: 3.5em;
        margin-bottom: 20px;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .hero-subtitle {
        font-size: 1.4em;
        margin-bottom: 30px;
        opacity: 0.9;
        line-height: 1.6;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .hero-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 40px;
    }
    
    .hero-btn {
        padding: 15px 35px;
        border: none;
        border-radius: 30px;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .hero-btn.primary {
        background: white;
        color: #667eea;
    }
    
    .hero-btn.secondary {
        background: rgba(255,255,255,0.2);
        color: white;
        border: 2px solid white;
    }
    
    .hero-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }
    
    .stats-section {
        background: white;
        padding: 60px 20px;
        margin-top: -30px;
        border-radius: 30px 30px 0 0;
        position: relative;
        z-index: 3;
    }
    
    .stats-container {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 40px 30px;
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        border-top: 5px solid #007bff;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(0,123,255,0.05) 0%, transparent 70%);
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .stat-card:hover::before {
        transform: scale(1.2);
    }
    
    .stat-icon {
        font-size: 3em;
        margin-bottom: 15px;
        position: relative;
        z-index: 2;
    }
    
    .stat-number {
        font-size: 2.8em;
        font-weight: bold;
        color: #007bff;
        margin-bottom: 10px;
        position: relative;
        z-index: 2;
    }
    
    .stat-label {
        color: #6c757d;
        font-weight: 600;
        font-size: 1.2em;
        position: relative;
        z-index: 2;
    }
    
    .content-section {
        background: white;
        padding: 60px 20px;
    }
    
    .section-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .section-header {
        text-align: center;
        margin-bottom: 50px;
    }
    
    .section-title {
        font-size: 2.5em;
        color: #2c3e50;
        margin-bottom: 15px;
        font-weight: 700;
    }
    
    .section-subtitle {
        font-size: 1.2em;
        color: #7f8c8d;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }
    
    .content-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 60px;
    }
    
    .content-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: all 0.3s;
        border: 1px solid #e1e5e9;
    }
    
    .content-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .card-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 20px;
        text-align: center;
    }
    
    .card-title {
        font-size: 1.3em;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .card-subtitle {
        opacity: 0.9;
        font-size: 0.9em;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .document-item {
        display: flex;
        align-items: center;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: background 0.3s;
        border-left: 3px solid transparent;
    }
    
    .document-item:hover {
        background: #f8f9fa;
        border-left-color: #007bff;
    }
    
    .document-icon {
        font-size: 1.5em;
        margin-right: 15px;
        color: #007bff;
    }
    
    .document-info {
        flex: 1;
    }
    
    .document-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 3px;
        font-size: 14px;
    }
    
    .document-meta {
        font-size: 12px;
        color: #6c757d;
    }
    
    .subject-item {
        padding: 20px;
        text-align: center;
        border-radius: 10px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .subject-item:hover {
        background: linear-gradient(135deg, #e7f3ff, #cce7ff);
        transform: translateY(-3px);
    }
    
    .subject-icon {
        font-size: 2.5em;
        margin-bottom: 15px;
        color: #007bff;
    }
    
    .subject-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }
    
    .subject-count {
        color: #6c757d;
        font-size: 14px;
    }
    
    .view-all-btn {
        display: block;
        width: fit-content;
        margin: 40px auto 0;
        padding: 15px 35px;
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        text-decoration: none;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s;
        box-shadow: 0 5px 15px rgba(40,167,69,0.3);
    }
    
    .view-all-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40,167,69,0.4);
        text-decoration: none;
        color: white;
    }
    
    .features-section {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 80px 20px;
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .feature-card {
        text-align: center;
        padding: 40px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .feature-card:hover {
        transform: translateY(-10px);
    }
    
    .feature-icon {
        font-size: 4em;
        margin-bottom: 20px;
        color: #007bff;
    }
    
    .feature-title {
        font-size: 1.5em;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
    }
    
    .feature-description {
        color: #6c757d;
        line-height: 1.6;
    }
    
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5em;
        }
        
        .hero-subtitle {
            font-size: 1.2em;
        }
        
        .hero-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .stats-container {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .content-grid,
        .features-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .section-title {
            font-size: 2em;
        }
    }
</style>

<div class="homepage-container">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title"> Chào mừng đến với Sharedy</h1>
            <p class="hero-subtitle">
                Nền tảng chia sẻ tài liệu học tập hàng đầu dành cho sinh viên Công nghệ thông tin. 
                Khám phá, học hỏi và chia sẻ kiến thức cùng cộng đồng.
            </p>
            <div class="hero-actions">
                <a href="index.php?page=monhoc" class="hero-btn primary">
                     Khám phá tài liệu
                </a>
                <a href="index.php?page=upload" class="hero-btn secondary">
                     Đăng tải ngay
                </a>
            </div>
        </div>

    </div>
    
    <!-- Stats Section -->
    <div class="stats-section">
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">📖</div>
                <div class="stat-number"><?php echo number_format($thong_ke['tong_mon_hoc'] ?? 0); ?></div>
                <div class="stat-label">Môn Học</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📄</div>
                <div class="stat-number"><?php echo number_format($thong_ke['tong_tai_lieu'] ?? 0); ?></div>
                <div class="stat-label">Tài Liệu</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo number_format($thong_ke['tong_nguoi_dung'] ?? 0); ?></div>
                <div class="stat-label">Thành Viên</div>
            </div>
        </div>
    </div>
    
    <!-- Content Section -->
    <div class="content-section">
        <div class="section-container">
            <div class="content-grid">
                <!-- Tài liệu mới nhất -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title"> Tài Liệu Mới Nhất</div>
                        <div class="card-subtitle">Cập nhật liên tục</div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($tai_lieu_moi)): ?>
                            <?php foreach (array_slice($tai_lieu_moi, 0, 5) as $tai_lieu): ?>
                                <div class="document-item">
                                    <div class="document-icon">📄</div>
                                    <div class="document-info">
                                        <div class="document-title">
                                            <?php echo htmlspecialchars(substr($tai_lieu['tieu_de'], 0, 30)); ?>
                                            <?php echo strlen($tai_lieu['tieu_de']) > 30 ? '...' : ''; ?>
                                        </div>
                                        <div class="document-meta">
                                            <?php echo htmlspecialchars($tai_lieu['ten_mon']); ?> • 
                                            <?php echo date('d/m/Y', strtotime($tai_lieu['ngay_tao'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 20px; color: #6c757d;">
                                <div style="font-size: 2em; margin-bottom: 10px;">📭</div>
                                <p>Chưa có tài liệu nào</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Môn học phổ biến -->
                <div class="content-card">
                    <div class="card-header">
                        <div class="card-title"> Môn Học Phổ Biến</div>
                        <div class="card-subtitle">Nhiều tài liệu nhất</div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($mon_hoc_pho_bien)): ?>
                            <?php foreach (array_slice($mon_hoc_pho_bien, 0, 4) as $index => $mon_hoc): ?>
                                <div class="subject-item" onclick="window.location.href='src/Views/taiLieuMon.php?id_mon_hoc=<?php echo $mon_hoc['id']; ?>'">
                                    <div class="subject-icon">
                                        <?php echo ['📊', '💻', '🔧', '📱'][$index % 4]; ?>
                                    </div>
                                    <div class="subject-name">
                                        <?php echo htmlspecialchars($mon_hoc['ten_mon']); ?>
                                    </div>
                                    <div class="subject-count">
                                        <?php echo $mon_hoc['so_tai_lieu']; ?> tài liệu
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 20px; color: #6c757d;">
                                <div style="font-size: 2em; margin-bottom: 10px;">📚</div>
                                <p>Chưa có môn học nào</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <a href="index.php?page=monhoc" class="view-all-btn">
                 Xem tất cả môn học
            </a>
        </div>
    </div>
    
    <!-- Features Section -->
    <div class="features-section">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">✨ Tính Năng Nổi Bật</h2>
                <p class="section-subtitle">
                    Những công cụ mạnh mẽ giúp bạn học tập hiệu quả hơn
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔍</div>
                    <h3 class="feature-title">Tìm Kiếm Thông Minh</h3>
                    <p class="feature-description">
                        Tìm kiếm tài liệu nhanh chóng và chính xác theo môn học, từ khóa hoặc tác giả
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">☁️</div>
                    <h3 class="feature-title">Lưu Trữ Đám Mây</h3>
                    <p class="feature-description">
                        Truy cập tài liệu mọi lúc, mọi nơi với hệ thống lưu trữ đám mây an toàn
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3 class="feature-title">Cộng Đồng Chia Sẻ</h3>
                    <p class="feature-description">
                        Kết nối với hàng ngàn sinh viên, chia sẻ kiến thức và học hỏi lẫn nhau
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>