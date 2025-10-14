<?php
include __DIR__ . '/../../config/ketNoiDB.php';
include __DIR__ . '/checkLogin.php'; // Thêm dòng này

// Kiểm tra đăng nhập (bắt buộc)
kiem_tra_dang_nhap(true);
$thong_bao = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_dm = trim($_POST['ten_danh_muc'] ?? '');
    $mo_ta  = trim($_POST['mo_ta'] ?? '');

    if ($ten_dm === '') {
        $thong_bao = "⚠️ Vui lòng nhập tên danh mục!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO danh_muc (ten_danh_muc, mo_ta) VALUES (:ten_danh_muc, :mo_ta)");
        if ($stmt->execute([':ten_danh_muc' => $ten_dm, ':mo_ta' => $mo_ta])) {
            $thong_bao = "✅ Thêm danh mục thành công!";
        } else {
            $thong_bao = "❌ Lỗi khi thêm danh mục!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng tải danh mục</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
    /* ==================== CSS ĐỒNG BỘ VỚI TRANG ĐĂNG TẢI MÔN HỌC ==================== */
    .upload-container {
        background: white;
        min-height: calc(100vh - 70px);
    }

    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 20px;
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
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><circle cx="200" cy="50" r="30"/><circle cx="400" cy="20" r="20"/><circle cx="600" cy="70" r="25"/><circle cx="800" cy="30" r="35"/></svg>');
        animation: float 8s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateX(0px);
        }

        50% {
            transform: translateX(20px);
        }
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
    }

    .form-section {
        max-width: 800px;
        margin: -30px auto 0;
        padding: 0 20px 60px;
        position: relative;
        z-index: 3;
    }

    .form-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        border: 1px solid #e1e5e9;
    }

    .thong-bao {
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 12px;
        font-weight: 500;
        text-align: center;
        border: 1px solid;
    }

    .thong-bao.thanh-cong {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
        border-color: #c3e6cb;
    }

    .thong-bao.loi {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
        border-color: #f5c6cb;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #2c3e50;
        font-size: 16px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #e1e5e9;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s;
        background: #f8f9fa;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #007bff;
        background: white;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.15);
        transform: translateY(-1px);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
        line-height: 1.6;
    }

    .submit-btn {
        width: 100%;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 18px 30px;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 30px;
        box-shadow: 0 5px 20px rgba(0, 123, 255, 0.3);
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
    }

    .required {
        color: #dc3545;
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
        .hero-section {
            padding: 40px 15px;
        }

        .hero-section h1 {
            font-size: 2em;
        }

        .form-section {
            margin-top: -20px;
            padding: 0 15px 40px;
        }

        .form-card {
            padding: 25px;
            border-radius: 15px;
        }
    }
</style>

<body>
    <div class="upload-container">
        <div class="hero-section">
            <div class="hero-content">
                <h1>➕ Đăng Tải Danh Mục</h1>
                <p>Tạo danh mục mới để sắp xếp tài liệu / nguồn</p>
            </div>
        </div>

        <div class="form-section">
            <div class="form-card">
                <a href="index.php?page=source" class="back-btn">← Quay lại thư viện nguồn</a>

                <?php if ($thong_bao): ?>
                    <div class="thong-bao"><?php echo $thong_bao; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="ten_danh_muc">Tên danh mục <span class="required">*</span></label>
                        <input type="text" id="ten_danh_muc" name="ten_danh_muc" required>
                    </div>

                    <div class="form-group">
                        <label for="mo_ta">Mô tả</label>
                        <textarea id="mo_ta" name="mo_ta"></textarea>
                    </div>

                    <button type="submit" class="submit-btn">💾 Lưu danh mục</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>