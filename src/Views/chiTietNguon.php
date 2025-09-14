<?php
// src/Views/chiTietNguon.php
include __DIR__ . '/../../config/ketNoiDB.php';

$id = $_GET['id'] ?? 0;

$sql = "SELECT b.*, u.ten_dang_nhap, d.ten_danh_muc
        FROM bai_chia_se b
        LEFT JOIN nguoi_dung u ON b.id_nguoi_dung = u.id
        LEFT JOIN danh_muc d ON b.id_danh_muc = d.id
        WHERE b.id = :id AND b.loai = 'du_an'";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Mã Nguồn</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f6fa;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .breadcrumb {
            font-size: 14px;
            margin-bottom: 15px;
        }

        .breadcrumb a {
            text-decoration: none;
            color: #007bff;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        h1 {
            color: #007bff;
            margin-bottom: 10px;
        }

        .meta {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }

        .mo-ta {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-outline {
            border: 1px solid #007bff;
            color: #007bff;
            background: transparent;
        }

        .btn-outline:hover {
            background: #007bff;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (!$item): ?>
            <p>❌ Không tìm thấy mã nguồn.</p>
        <?php else: ?>
            <div class="breadcrumb">
                <!-- Nút quay lại -->
                <a href="javascript:history.back()" class="btn btn-outline">⬅ Quay lại</a><br><br>
                <a href="index.php?page=thuVienNguon">Thư viện nguồn</a> >>
                <?= htmlspecialchars($item['tieu_de']) ?>
            </div>

            <h1>💻 <?= htmlspecialchars($item['tieu_de']) ?></h1>

            <div class="meta">
                📅 <?= $item['ngay_tao'] ?>
                | 👤 <?= !empty($item['ten_dang_nhap']) ? htmlspecialchars($item['ten_dang_nhap']) : "Ẩn danh" ?>
                | 🏷 <?= htmlspecialchars($item['ten_danh_muc']) ?> - <?= htmlspecialchars($item['cong_nghe']) ?>
            </div>

            <div class="mo-ta">
                <strong>Mô tả:</strong><br>
                <?= nl2br(htmlspecialchars($item['mo_ta'])) ?>
            </div>

            <div class="actions">
                <?php if (!empty($item['link_host'])): ?>
                    <a href="<?= $item['link_host'] ?>" target="_blank" class="btn btn-outline">🌐 Link Host</a>
                <?php endif; ?>
                <?php if (!empty($item['link_source'])): ?>
                    <a href="<?= $item['link_source'] ?>" target="_blank" class="btn btn-primary">💻 Link Source</a>
                <?php endif; ?>
            </div>

            <div style="margin-top:20px;">
                👍 <?= $item['so_luot_like'] ?> | 👎 <?= $item['so_luot_dislike'] ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>