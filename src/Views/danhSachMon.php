<?php
// Đảm bảo đường dẫn đúng đến file kết nối DB
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy danh sách môn học cùng số lượng tài liệu
try {
    $sql = "SELECT mh.*, COUNT(bcs.id) as so_luong_tai_lieu   
            FROM mon_hoc mh 
            LEFT JOIN bai_chia_se bcs ON mh.id = bcs.id_mon_hoc AND bcs.loai = 'tai_lieu'
            GROUP BY mh.id, mh.ten_mon, mh.mo_ta
            ORDER BY mh.ten_mon";

    $stmt = $pdo->query($sql);
    $danh_sach_mon_hoc = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    $thong_ke = $stmt_thong_ke->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $thong_ke = array('tong_mon_hoc' => 0, 'tong_tai_lieu' => 0, 'tong_nguoi_dung' => 0);
}

// Hàm làm sạch chuỗi (nếu chưa có trong helpers)
if (!function_exists('lam_sach_chuoi')) {
    function lam_sach_chuoi($str)
    {
        return htmlspecialchars(strip_tags(trim($str)));
    }
}
?>

<style>
    .page-container {
        background: white;
        min-height: calc(100vh - 70px);
    }

    .hero-section {
        position: relative;
        background: url('/NenTangChiaSeTLHT/public/docbackground.jpg') center/cover no-repeat;
        color: white;
        /* padding: 60px 20px; */
        text-align: center;
        overflow: hidden;
    }

    .hero-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,0 1000,0 1000,60 0,100"/></svg>');
        background-size: cover;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        margin: 0 auto;
    }

    .hero-section h1 {
        font-size: 2.5em;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .hero-section p {
        font-size: 1.2em;
        opacity: 0.9;
        margin-bottom: 30px;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        /* padding: 40px 20px; */
        background: #f8f9fa;
        max-width: 1200px;
        margin: -30px auto 0;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 3;
    }

    .stat-card {
        background: white;
        padding: 30px 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        border-top: 4px solid #007bff;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .stat-number {
        font-size: 2.5em;
        font-weight: bold;
        color: #007bff;
        margin-bottom: 10px;
        display: block;
    }

    .stat-label {
        color: #6c757d;
        font-weight: 600;
        font-size: 1.1em;
    }

    .content {
        /* padding: 60px 20px 40px; */
        max-width: 1200px;
        margin: 0 auto;
    }

    .section-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .section-title {
        font-size: 2.2em;
        color: #2c3e50;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .section-subtitle {
        font-size: 1.1em;
        color: #7f8c8d;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .search-box {
        flex: 1;
        min-width: 300px;
        max-width: 500px;
        padding: 15px 25px;
        border: 2px solid #e1e5e9;
        border-radius: 30px;
        font-size: 16px;
        transition: all 0.3s;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .search-box:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.15);
        transform: translateY(-1px);
    }

    .upload-btn {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        white-space: nowrap;
    }

    .upload-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        text-decoration: none;
        color: white;
    }

    .mon-hoc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
        margin-top: 20px;
    }

    .mon-hoc-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .mon-hoc-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        border-color: #007bff;
    }

    .mon-hoc-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 25px;
        position: relative;
        overflow: hidden;
    }

    .mon-hoc-header::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-10px) rotate(5deg);
        }
    }

    .mon-hoc-title {
        font-size: 1.3em;
        font-weight: 600;
        margin-bottom: 8px;
        position: relative;
        z-index: 1;
    }

    .mon-hoc-count {
        font-size: 0.95em;
        opacity: 0.9;
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .mon-hoc-body {
        padding: 25px;
    }

    .mon-hoc-description {
        color: #6c757d;
        margin-bottom: 20px;
        line-height: 1.6;
        font-size: 14px;
    }

    .mon-hoc-actions {
        display: flex;
        gap: 12px;
    }

    .btn {
        padding: 12px 20px;
        border: none;
        border-radius: 20px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex: 1;
        justify-content: center;
        text-align: center;
    }

    .btn-primary {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white;
        box-shadow: 0 3px 10px rgba(0, 123, 255, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        text-decoration: none;
        color: white;
    }

    .btn-outline {
        background: transparent;
        color: #6c757d;
        border: 2px solid #e1e5e9;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .empty-state,
    .no-results {
        text-align: center;
        padding: 80px 20px;
        color: #6c757d;
    }

    .empty-state h3,
    .no-results h3 {
        font-size: 1.8em;
        margin-bottom: 15px;
        color: #495057;
    }

    .empty-state p,
    .no-results p {
        font-size: 1.1em;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .icon {
        font-size: 1.2em;
    }

    @media (max-width: 768px) {
        .hero-section {
            padding: 40px 15px;
        }

        .hero-section h1 {
            font-size: 2em;
        }

        .stats-container {
            grid-template-columns: 1fr;
            padding: 30px 15px;
            margin: -20px 15px 0;
        }

        .content {
            padding: 40px 15px;
        }

        .actions-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            min-width: auto;
            max-width: none;
        }

        .mon-hoc-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .section-title {
            font-size: 1.8em;
        }
    }

    .btn-small-primary {
        background: linear-gradient(45deg, #007bff, #0056b3);
        color: white !important;
        padding: 18px 28px;
        /* nhỏ gọn hơn */
        font-size: 16px;
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.25);
        transition: all 0.3s;
        text-decoration: none;
        /* bỏ gạch chân */
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        white-space: nowrap;
    }

    .btn-small-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.35);
        color: white !important;
        text-decoration: none;
    }
</style>

<div class="page-container">
    <div class="hero-section">
        <div class="hero-content">
            <h1>📚 Thư Viện Tài Liệu</h1>
            <p>Khám phá kho tài liệu phong phú cho các môn học Công nghệ thông tin</p>
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($thong_ke['tong_mon_hoc'] ?? 0); ?></div>
            <div class="stat-label">📖 Môn Học</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($thong_ke['tong_tai_lieu'] ?? 0); ?></div>
            <div class="stat-label">📄 Tài Liệu</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($thong_ke['tong_nguoi_dung'] ?? 0); ?></div>
            <div class="stat-label">👥 Người Đóng Góp</div>
        </div>
    </div>

    <div class="content">
        <?php if (isset($loi)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 30px; text-align: center;">
                ⚠️ <?php echo $loi; ?>
            </div>
        <?php endif; ?>

        <div class="section-header">
            <h2 class="section-title">Danh Sách Môn Học</h2>
            <p class="section-subtitle">Chọn môn học để xem và tải xuống tài liệu học tập</p>
        </div>

        <div class="actions-bar">
            <input type="text"
                class="search-box"
                id="search-input-local"
                placeholder="🔍 Tìm kiếm môn học..."
                onkeyup="tim_kiem_mon_hoc_local()">

            <a href="index.php?page=them_mon" class="btn-small-primary">
                ➕ Thêm môn học mới
            </a>


            <a href="index.php?page=upload" class="upload-btn">
                <span class="icon">⬆️</span> Đăng tải tài liệu
            </a>
        </div>

        <?php if (empty($danh_sach_mon_hoc)): ?>
            <div class="empty-state">
                <h3>📚 Chưa có môn học nào</h3>
                <p>Hệ thống chưa có môn học nào được thêm vào.<br>Hãy bắt đầu bằng cách upload tài liệu đầu tiên!</p>
                <a href="index.php?page=upload" class="btn btn-primary" style="width: auto; display: inline-flex;">
                    <span class="icon">⬆️</span> Upload Tài Liệu Đầu Tiên
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
                                <span class="icon">📄</span>
                                <?php echo $mon_hoc['so_luong_tai_lieu']; ?> tài liệu
                            </div>
                        </div>
                        <div class="mon-hoc-body">
                            <?php if (!empty($mon_hoc['mo_ta'])): ?>
                                <div class="mon-hoc-description">
                                    <?php echo lam_sach_chuoi(substr($mon_hoc['mo_ta'], 0, 120)); ?>
                                    <?php echo strlen($mon_hoc['mo_ta']) > 120 ? '...' : ''; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mon-hoc-actions">
                                <?php if ($mon_hoc['so_luong_tai_lieu'] > 0): ?>
                                    <a href="index.php?page=tailieumon&id_mon_hoc=<?php echo $mon_hoc['id']; ?>"
                                        class="btn btn-primary">
                                        <span class="icon">👁️</span> Xem Tài Liệu
                                    </a>
                                <?php else: ?>
                                    <span class="btn btn-outline">
                                        <span class="icon">🔭</span> Chưa có tài liệu
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="no-results" class="no-results" style="display: none;">
                <h3>🔍 Không tìm thấy kết quả</h3>
                <p>Không có môn học nào phù hợp với từ khóa tìm kiếm của bạn.<br>Hãy thử với từ khóa khác.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function tim_kiem_mon_hoc_local() {
        const searchInput = document.getElementById('search-input-local');
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
        // Click vào card để chuyển trang
        card.addEventListener('click', function(e) {
            // Không chuyển trang nếu click vào button
            if (e.target.closest('.btn')) return;

            const link = this.querySelector('.btn-primary');
            if (link && !link.classList.contains('btn-outline')) {
                window.location.href = link.href;
            }
        });
    });

    // Auto focus vào search box
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input-local');
        if (searchInput) {
            searchInput.focus();
        }
    });
</script>