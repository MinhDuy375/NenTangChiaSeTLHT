<?php
// src/Views/thuVienNguon.php
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy danh mục
$danh_muc = $pdo->query("SELECT * FROM danh_muc ORDER BY ten_danh_muc")->fetchAll();

// Lọc dữ liệu
$keyword = $_GET['keyword'] ?? '';
$id_danh_muc = $_GET['id_danh_muc'] ?? '';

$sql = "SELECT b.*, u.ten_dang_nhap, d.ten_danh_muc
        FROM bai_chia_se b
        LEFT JOIN nguoi_dung u ON b.id_nguoi_dung = u.id
        LEFT JOIN danh_muc d ON b.id_danh_muc = d.id
        WHERE b.loai = 'du_an'";

$params = [];
if (!empty($keyword)) {
    $sql .= " AND (b.tieu_de LIKE :kw OR b.mo_ta LIKE :kw OR b.cong_nghe LIKE :kw)";
    $params['kw'] = "%$keyword%";
}
if (!empty($id_danh_muc)) {
    $sql .= " AND b.id_danh_muc = :id_dm";
    $params['id_dm'] = $id_danh_muc;
}
$sql .= " ORDER BY b.ngay_tao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ds_ma_nguon = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện nguồn</title>
    <style>
        * { margin:0; padding:0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background:white; box-shadow:0 10px 30px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(45deg,#2196F3,#21CBF3); color:white; padding:20px; text-align:center; }
        .header h2 { font-size:2em; margin-bottom:10px; }
        .header p { font-size:1.1em; opacity:0.9; }

        .content { padding:30px; }
        .search-container { margin-bottom:30px; display:flex; justify-content:space-between; align-items: center; gap: 15px; }
        .search-form { display:flex; gap:10px; flex-wrap:wrap; flex: 1; }
        .search-box {
            padding:12px 20px;
            border:2px solid #e1e5e9;
            border-radius:25px;
            font-size:16px;
            transition: all 0.3s;
            min-width: 200px;
        }
        .search-box:focus { outline:none; border-color:#007bff; box-shadow:0 0 0 3px rgba(0,123,255,0.25); }

        .posts-container { display: flex; flex-direction: column; gap: 20px; }
        
        .post-card { 
            background: white; 
            border: 1px solid #e1e5e9; 
            border-radius: 12px;
            padding: 20px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .post-card:hover { 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            transform: translateY(-2px);
        }

        .post-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #28a745);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .post-info {
            flex: 1;
        }

        .post-author {
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }

        .post-meta {
            font-size: 13px;
            color: #666;
        }

        .post-category {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            margin-left: 8px;
        }

        .post-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .post-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .post-tech {
            display: inline-block;
            background: #f8f9fa;
            color: #495057;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .post-links {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .link-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            background: #f8f9fa;
        }

        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
        }

        .action-btn {
            background: none;
            border: none;
            color: #666;
            font-size: 14px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn:hover {
            background: #f0f0f0;
            color: #333;
        }

        .action-btn.liked {
            color: #e74c3c;
        }

        .view-detail-btn {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .view-detail-btn:hover {
            background: #0056b3;
        }

        .btn { 
            padding:10px 20px; 
            border:none; 
            border-radius:6px; 
            text-decoration:none; 
            font-size:14px; 
            font-weight:500; 
            cursor:pointer; 
            transition:all .3s; 
        }
        .btn-primary { background:#007bff; color:white; }
        .btn-primary:hover { background:#0056b3; }
        .btn-success { background:#28a745; color:white; }
        .btn-success:hover { background:#1e7e34; }

        .empty-state { 
            text-align:center; 
            padding:60px 20px; 
            color:#6c757d; 
            background: white;
            border-radius: 12px;
            border: 1px solid #e1e5e9;
        }
        .empty-state h3 { font-size:1.5em; margin-bottom:15px; }

        @media (max-width: 768px) {
            .search-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Thư Viện Nguồn</h2>
            <p>Nơi chia sẻ các dự án, mã nguồn hữu ích cho sinh viên & người mới học lập trình</p>
        </div>

        <div class="content">
            <!-- Form tìm kiếm -->
            <div class="search-container">
                <form method="get" action="index.php" class="search-form">
                    <input type="hidden" name="page" value="thuVienNguon">
                    <input type="text" name="keyword" placeholder="🔍 Tìm mã nguồn..." value="<?= htmlspecialchars($keyword) ?>" class="search-box">
                    <select name="id_danh_muc" class="search-box">
                        <option value="">Tất cả danh mục</option>
                        <?php foreach ($danh_muc as $dm): ?>
                            <option value="<?= $dm['id'] ?>" <?= $id_danh_muc == $dm['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dm['ten_danh_muc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">🔍 Tìm</button>
                </form>
                <a href="index.php?page=source_upload" class="btn btn-success">➕ Đăng mã nguồn</a>
            </div>

            <!-- Danh sách mã nguồn -->
            <?php if (empty($ds_ma_nguon)): ?>
                <div class="empty-state">
                    <h3>🔭 Chưa có mã nguồn nào</h3>
                    <p>Hãy là người đầu tiên chia sẻ mã nguồn hữu ích!</p>
                    <a href="index.php?page=dangTaiNguon" class="btn btn-primary">➕ Đăng mã nguồn</a>
                </div>
            <?php else: ?>
                <div class="posts-container">
                    <?php foreach ($ds_ma_nguon as $item): ?>
                        <div class="post-card">
                            <div class="post-header">
                                <div class="avatar">
                                    <?= strtoupper(substr($item['ten_dang_nhap'], 0, 1)) ?>
                                </div>
                                <div class="post-info">
                                    <div class="post-author"><?= htmlspecialchars($item['ten_dang_nhap']) ?></div>
                                    <div class="post-meta">
                                        <?= date('d/m/Y', strtotime($item['ngay_tao'])) ?>
                                        <span class="post-category"><?= htmlspecialchars($item['ten_danh_muc']) ?></span>
                                        <?php if (!empty($item['cong_nghe'])): ?>
                                <div class="post-tech">💻 <?= htmlspecialchars($item['cong_nghe']) ?></div>
                            <?php endif; ?>
                                    </div>
                                    
                                </div>

                            </div>
                            
                            <div class="post-title"><?= htmlspecialchars($item['tieu_de']) ?></div>
                            <div class="post-description">
                                <?php 
                                $mo_ta = htmlspecialchars($item['mo_ta']);
                                // Giới hạn độ dài mô tả khoảng 150-200 ký tự
                                if (strlen($mo_ta) > 200) {
                                    $mo_ta = substr($mo_ta, 0, 200) . '...';
                                }
                                echo $mo_ta;
                                ?>
                            </div>
                            
                            

                        

                            <div class="post-actions">
                                <div class="action-buttons">
                                    <button class="action-btn">
                                        👍 like
                                    </button>
                                    <button class="action-btn">
                                        👎 dislike
                                    </button>
                                    
                                </div>
                                <a href="index.php?page=source_detail&id=<?= $item['id'] ?>" class="view-detail-btn">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>