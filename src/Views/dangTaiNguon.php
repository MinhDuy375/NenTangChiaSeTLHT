<?php
// src/Views/dangTaiNguon.php
include __DIR__ . '/../../config/ketNoiDB.php';

// Lấy danh mục
$danh_muc = $pdo->query("SELECT * FROM danh_muc ORDER BY ten_danh_muc")->fetchAll();

$thong_bao = '';
$loai_thong_bao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dang_tai_nguon'])) {
    // Làm sạch dữ liệu
    $tieu_de = htmlspecialchars(trim($_POST['tieu_de'] ?? ''));
    $mo_ta = htmlspecialchars(trim($_POST['mo_ta'] ?? ''));
    $id_danh_muc = $_POST['id_danh_muc'] ?? null;
    $link_source = htmlspecialchars(trim($_POST['link_source'] ?? ''));
    $link_host = htmlspecialchars(trim($_POST['link_host'] ?? ''));
    $cong_nghe = htmlspecialchars(trim($_POST['cong_nghe'] ?? ''));
    $id_nguoi_dung = $_SESSION['user_id']; // tạm thời (sau thay bằng session login)

    if (empty($tieu_de) || empty($mo_ta) || empty($id_danh_muc) || empty($link_source) || empty($cong_nghe)) {
        $thong_bao = "⚠️ Vui lòng nhập đầy đủ các trường bắt buộc!";
        $loai_thong_bao = "loi";
    } else {
        try {
            $sql = "INSERT INTO bai_chia_se 
                (loai, tieu_de, mo_ta, link_source, link_host, cong_nghe, id_danh_muc, id_nguoi_dung, ngay_tao)
                VALUES ('bai_viet', :tieu_de, :mo_ta, :link_source, :link_host, :cong_nghe, :id_danh_muc, :id_nguoi_dung, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'tieu_de' => $tieu_de,
                'mo_ta' => $mo_ta,
                'link_source' => $link_source,
                'link_host' => $link_host,
                'cong_nghe' => $cong_nghe,
                'id_danh_muc' => $id_danh_muc,
                'id_nguoi_dung' => $id_nguoi_dung
            ]);

            $thong_bao = "✅ Đăng mã nguồn thành công!";
            $loai_thong_bao = "thanh-cong";
        } catch (PDOException $e) {
            $thong_bao = "❌ Lỗi CSDL: " . $e->getMessage();
            $loai_thong_bao = "loi";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng tải mã nguồn</title>
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
</head>

<body>
    <div class="upload-container">
        <div class="hero-section">
            <div class="hero-content">
                <h1>🚀 Đăng Tải Nguồn</h1>
                <p>Chia sẻ mã nguồn, dự án để cộng đồng học tập và phát triển</p>
            </div>
        </div>

        <div class="form-section">
            <div class="form-card">
                <a href="index.php?page=source" class="back-btn">← Quay lại thư viện nguồn</a>

                <?php if (!empty($thong_bao)): ?>
                    <div class="thong-bao <?= $loai_thong_bao ?>">
                        <?= $thong_bao ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label for="tieu_de">📝 Tiêu đề <span class="required">*</span></label>
                        <input type="text" id="tieu_de" name="tieu_de" required>
                    </div>

                    <div class="form-group">
                        <label for="mo_ta">📄 Mô tả chi tiết <span class="required">*</span></label>
                        <textarea id="mo_ta" name="mo_ta" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="id_danh_muc">📂 Danh mục <span class="required">*</span></label>
                        <select id="id_danh_muc" name="id_danh_muc" required>
                            <?php foreach ($danh_muc as $dm): ?>
                                <option value="<?= $dm['id'] ?>"><?= htmlspecialchars($dm['ten_danh_muc']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cong_nghe">⚙️ Loại / Công nghệ <span class="required">*</span></label>
                        <input type="text" id="cong_nghe" name="cong_nghe" placeholder="Ví dụ: PHP, MySQL, NodeJS..." required>
                    </div>

                    <div class="form-group">
                        <label for="link_source">💻 Link Source (GitHub) <span class="required">*</span></label>
                        <input type="url" id="link_source" name="link_source" placeholder="https://github.com/..." required>
                    </div>

                    <div class="form-group">
                        <label for="link_host">🌐 Link Demo (nếu có)</label>
                        <input type="url" id="link_host" name="link_host" placeholder="https://example.com">
                    </div>

                    <button type="submit" name="dang_tai_nguon" class="submit-btn">
                        🚀 Đăng Tải Nguồn
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>


</html>