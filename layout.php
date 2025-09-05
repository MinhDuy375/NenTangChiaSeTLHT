<!-- layout.php -->
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? "Website Demo" ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #007bff;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
        }

        h2 a {
            margin-left: 30px;
            text-decoration: none;
            color: white;
        }

        /* 
        .header-search-container {
           width: 250px;
        }
        .header-search-box {
            width: 100%;
            max-width: 500px;
            height: 40px;
            border: 2px solid #d1d1d1ff;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s;
            padding: 5px 5px 5px 20px;
        }
        
        .header-search-box:focus {
            outline: none;
            border-color: #eaf4ffff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
        } */
        nav {
            margin: auto 20px;
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;

        }

        .user-name {
            font-weight: 500;
        }

        .user-avatar {
            width: 33px;
            height: 33px;
            border-radius: 50%;
            background: #5cf17cff;
            color: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            border: 2px solid #fff;
        }

        .logout-btn {
            background: #ff4d4f;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }

        nav a {
            color: white;
            margin-right: 18px;
            text-decoration: none;
            font-weight: 500
        }

        main {
            padding-top: 60px;
            min-height: 500px;
        }

        footer {
            background: #f1f1f1;
            text-align: center;
            padding: 10px;
        }
    </style>
</head>

<body>
    <header>
        <h2><a href="index.php?page=home">Sharedy</a></h2>
        <!-- <div class="header-search-container">
                <input type="text" 
                       class="header-search-box" 
                       id="search-input" 
                       placeholder="Tìm kiếm nhanh..."
                       onkeyup="tim_kiem_mon_hoc()">
            </div> -->

        <div class="user-box">
            <nav>
                <a href="index.php?page=home">Trang chủ</a>
                <a href="index.php?page=danhSachMon">Môn học</a>
                <a href="index.php?page=duAn">Dự án</a>
                <a href="index.php?page=lienHe">Liên hệ</a>
                <a href="index.php?page=dangTaiTaiLieu">Upload Tài liệu</a>
            </nav>

            <span class="user-name">Minh Duy</span>
            <div class="user-avatar">MD</div>
            <form action="logout.php" method="post" style="margin:0;">
                <button type="submit" class="logout-btn">Đăng xuất</button>
            </form>
        </div>
    </header>
    <main>
        <?= $content ?? '' ?>
    </main>
    <footer>
        &copy; <?= date("Y") ?> - Demo Website
    </footer>
</body>

</html>