<?php
include __DIR__ . '/../../config/ketNoiDB.php';

$thong_bao = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_mon = trim($_POST['ten_mon'] ?? '');
    $mo_ta   = trim($_POST['mo_ta'] ?? '');

    if ($ten_mon === '') {
        $thong_bao = "⚠️ Vui lòng nhập tên môn học!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO mon_hoc (ten_mon, mo_ta) VALUES (:ten_mon, :mo_ta)");
        if ($stmt->execute([':ten_mon' => $ten_mon, ':mo_ta' => $mo_ta])) {
            $thong_bao = "✅ Thêm môn học thành công!";
        } else {
            $thong_bao = "❌ Lỗi khi thêm môn học!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng tải môn học</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
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

    .file-input-container {
        position: relative;
        margin-top: 10px;
    }

    .file-input-container input[type="file"] {
        opacity: 0;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        z-index: 2;
    }

    .file-input-label {
        display: block;
        padding: 40px 20px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border: 3px dashed #dee2e6;
        border-radius: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .file-input-label::before {
        content: "📄";
        font-size: 3em;
        display: block;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .file-input-label:hover {
        background: linear-gradient(135deg, #e9ecef, #d1ecf1);
        border-color: #007bff;
        transform: translateY(-2px);
    }

    .file-input-label.has-file {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        border-color: #28a745;
    }

    .file-input-label.has-file::before {
        content: "✅";
        color: #28a745;
    }

    .file-info {
        margin-top: 15px;
        padding: 15px;
        background: linear-gradient(135deg, #e7f3ff, #cce7ff);
        border-radius: 10px;
        border-left: 4px solid #007bff;
        display: none;
    }

    .file-types {
        font-size: 14px;
        color: #6c757d;
        margin-top: 10px;
        text-align: center;
        font-style: italic;
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

    .submit-btn:active {
        transform: translateY(0);
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

        .file-input-label {
            padding: 30px 15px;
        }
    }
</style>

<body>
    <div class="upload-container">
        <div class="hero-section">
            <div class="hero-content">
                <h1>➕ Đăng Tải Môn Học</h1>
                <p>Tạo môn học mới để quản lý tài liệu học tập</p>
            </div>
        </div>

        <div class="form-section">
            <div class="form-card">
                <a href="index.php?page=monhoc" class="back-btn">← Quay lại danh sách môn</a>

                <?php if ($thong_bao): ?>
                    <div class="thong-bao"><?php echo $thong_bao; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="ten_mon">Tên môn học <span class="required">*</span></label>
                        <input type="text" id="ten_mon" name="ten_mon" required>
                    </div>

                    <div class="form-group">
                        <label for="mo_ta">Mô tả</label>
                        <textarea id="mo_ta" name="mo_ta"></textarea>
                    </div>

                    <button type="submit" class="submit-btn">💾 Lưu môn học</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>