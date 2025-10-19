<?php
include __DIR__ . '/../../../config/ketNoiDB.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Chia Sẻ Tài Liệu</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;

            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 40px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .menu-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .menu-card h2 {
            color: white;
            margin-bottom: 15px;
            font-size: 1.8em;
        }

        .menu-card p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .menu-card a {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .menu-card a:hover {
            background: #f0f0f0;
            transform: scale(1.05);
        }

        .menu-card.subjects {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .menu-card.articles {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .menu-card.users {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .icon {
            font-size: 3em;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>⚙️ Quản trị hệ thống</h1>

        <div class="menu-grid">
            <div class="menu-card subjects">
                <div class="icon">📚</div>
                <h2>Quản Lý Môn Học - Tài liệu</h2>
                <p>Xem danh sách môn học và tài liệu liên quan. Thêm, sửa, xóa môn học và quản lý tài liệu theo từng môn.</p>
                <a href="index.php?page=adminMon">Vào Quản Lý</a>
            </div>

            <div class="menu-card articles">
                <div class="icon">📝</div>
                <h2>Quản Lý Danh mục - Bài Viết</h2>
                <p>Quản lý các bài viết chia sẻ. Lọc theo danh mục, tìm kiếm và thực hiện các thao tác CRUD.</p>
                <a href="index.php?page=adminDanhmuc">Vào Quản Lý</a>
            </div>

            <div class="menu-card users">
                <div class="icon">👥</div>
                <h2>Quản Lý Người Dùng</h2>
                <p>Xem và quản lý danh sách người dùng trong hệ thống. Thêm, sửa, xóa tài khoản người dùng.</p>
                <a href="index.php?page=adminUser">Vào Quản Lý</a>
            </div>
        </div>
    </div>
</body>

</html>