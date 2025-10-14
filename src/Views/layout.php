<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : "Sharedy - Hệ thống chia sẻ tài liệu" ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4ff;
        }

        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(135deg, #f4020297, #0056b3);
            color: white;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            padding: 10px 10px;
            z-index: 1001;
        }

        .logo:hover {
            opacity: 0.9;
        }

        .header-search-container {
            flex: 1;
            max-width: 300px;
            margin: 0 30px;
        }

        .header-search-box {
            width: 100%;
            height: 40px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            font-size: 14px;
            transition: all 0.3s;
            padding: 0 20px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .header-search-box::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .header-search-box:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }

        /* Menu hamburger button */
        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
            z-index: 1001;
        }

        .menu-toggle span {
            width: 25px;
            height: 3px;
            background: white;
            margin: 3px 0;
            transition: all 0.3s;
            border-radius: 3px;
        }

        .menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-left: 20px;
        }

        .user-name {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            color: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.3s;
        }

        .user-avatar:hover {
            transform: scale(1.05);
            border-color: #fff;
        }

        .logout-btn {
            background: rgba(255, 77, 79, 0.9);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
        }

        .logout-btn:hover {
            background: #ff4d4f;
            transform: translateY(-1px);
        }

        main {
            padding-top: 70px;
            min-height: calc(100vh - 130px);
            background-color: #f8f9fa;
        }

        footer {
            background: #137ce5ff;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: auto;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .container {
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 60px);
            background-image: url(https://static.tramdoc.vn/image/img.news/0/0/0/8341.jpg?v=1&w=300&h=200&nocache=1);
            padding: 20px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .form-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            width: 350px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .form-box h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-box label {
            font-size: 14px;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .form-box input[type="text"],
        .form-box input[type="password"],
        .form-box input[type="email"],
        .form-box select {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
        }

        .form-box input:focus,
        .form-box select:focus {
            border-color: #007bff;
        }

        .form-box button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .form-box button:hover {
            background: #1a252f;
        }

        .form-box .message {
            text-align: center;
            color: red;
            margin-bottom: 15px;
        }

        .extra-links {
            margin-top: 12px;
            text-align: center;
            font-size: 14px;
        }

        .extra-links a {
            color: #2c3e50;
            text-decoration: none;
            margin: 0 8px;
            transition: 0.3s;
        }

        .extra-links a:hover {
            color: #f39c12;
        }

        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 15px 0;
        }

        .otp-inputs input {
            width: 40px;
            height: 50px;
            text-align: center;
            font-size: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .link-white-no-underline {
            text-decoration: none;
            color: white;
        }

        .link-white-no-underline:hover {
            color: #ffcc00;
            text-decoration: underline;
        }

        /* Responsive cho màn hình nhỏ */
        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }

            .header-search-container {
                display: none;
            }

            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: linear-gradient(135deg, #f4020297, #0056b3);
                flex-direction: column;
                justify-content: flex-start;
                padding-top: 30px;
                gap: 0;
                transition: left 0.3s ease-in-out;
                overflow-y: auto;
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-menu a {
                width: 90%;
                text-align: center;
                padding: 15px 20px;
                margin: 5px 0;
                border-radius: 10px;
                font-size: 16px;
            }

            .user-section {
                position: fixed;
                bottom: 0;
                left: -100%;
                width: 100%;
                background: rgba(0, 0, 0, 0.2);
                padding: 20px;
                flex-direction: column;
                gap: 10px;
                margin: 0;
                transition: left 0.3s ease-in-out;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
            }

            .user-section.active {
                left: 0;
            }

            .user-name {
                display: block;
                font-size: 16px;
            }

            .logout-btn,
            .user-section a {
                width: 90%;
                max-width: 300px;
            }

            main {
                padding-top: 70px;
            }
        }

        @media (max-width: 480px) {
            .logo {
                font-size: 20px;
            }

            .main-header {
                padding: 0 15px;
            }
        }
    </style>
</head>

<body>
    <header class="main-header">
        <a href="index.php?page=home" class="logo">Sharedy</a>

        <div class="menu-toggle" id="menuToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <!-- <div class="header-search-container">
            <input type="text"
                class="header-search-box"
                id="search-input"
                placeholder="Tìm kiếm nhanh..."
                onkeyup="tim_kiem_mon_hoc()">
        </div> -->

        <nav class="nav-menu" id="navMenu">
            <a href="index.php?page=home" <?= ($page ?? '') == 'contact' ? 'class="active"' : '' ?>>
                Trang chủ
            </a>
            <a href="index.php?page=monhoc" <?= ($page ?? '') == 'monhoc' ? 'class="active"' : '' ?>>
                Môn học
            </a>
            <a href="index.php?page=source" <?= ($page ?? '') == 'upload' ? 'class="active"' : '' ?>>
                Thư viện nguồn
            </a>
            <a href="index.php?page=thu_vien" <?= ($page ?? '') == 'thu_vien' ? 'class="active"' : '' ?>>
                Yêu thích
            </a>
            <a href="index.php?page=admin" <?= ($page ?? '') == 'admin' ? 'class="active"' : '' ?>>
                Hệ thống
            </a>
        </nav>

        <div class="user-section" id="userSection">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <?php
                $fullname = htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']);
                $avatar = strtoupper(substr($fullname, 0, 2));
                ?>
                <span class="user-name"><?= $fullname ?></span>
                <div class="user-avatar" title="<?= $fullname ?>"><?= $avatar ?></div>
                <form action="src/Views/logout.php" method="post" style="margin:0;">
                    <button type="submit" class="logout-btn">Đăng xuất</button>
                </form>
            <?php else: ?>
                <a href="src/Views/logout.php" style="text-decoration: none; font-weight:bold; color:white;">Đăng nhập</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <?php echo $content ?? '<div style="padding: 50px; text-align: center;"><h2>Không có nội dung</h2></div>'; ?>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; <?= date("Y") ?> Sharedy - Hệ thống chia sẻ tài liệu học tập</p>
            <p style="margin-top: 5px; font-size: 14px; opacity: 0.8;">
                Phát triển bởi nhóm sinh viên CNTT
            </p>
        </div>
    </footer>

    <script>
        // Toggle menu hamburger
        const menuToggle = document.getElementById('menuToggle');
        const navMenu = document.getElementById('navMenu');
        const userSection = document.getElementById('userSection');

        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            navMenu.classList.toggle('active');
            userSection.classList.toggle('active');

            // Prevent body scroll when menu is open
            if (navMenu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Close menu when clicking on a link
        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                menuToggle.classList.remove('active');
                navMenu.classList.remove('active');
                userSection.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        // Hàm tìm kiếm chung
        function tim_kiem_mon_hoc() {
            const searchInput = document.getElementById('search-input');
            const searchTerm = searchInput.value.toLowerCase().trim();

            const monHocCards = document.querySelectorAll('.mon-hoc-card');
            const noResults = document.getElementById('no-results');

            if (monHocCards.length > 0) {
                let hasResults = false;

                monHocCards.forEach(card => {
                    const tenMon = card.getAttribute('data-ten-mon');
                    const shouldShow = tenMon && tenMon.includes(searchTerm);

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

                if (noResults) {
                    noResults.style.display = (hasResults || searchTerm.length === 0) ? 'none' : 'block';
                }
            }
        }

        // Highlight active menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = new URLSearchParams(window.location.search).get('page') || 'home';
            const navLinks = document.querySelectorAll('.nav-menu a');

            navLinks.forEach(link => {
                const linkPage = new URL(link.href).searchParams.get('page') || 'home';
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
        });

        function kiemTraDangNhap(url) {
            <?php if (empty($_SESSION['user_id'])): ?>
                if (confirm('Bạn cần đăng nhập để sử dụng tính năng này.\n\nBấm OK để đăng nhập hoặc Cancel để quay lại.')) {
                    window.location.href = 'index.php?page=login&redirect=' + encodeURIComponent(url);
                }
                return false;
            <?php else: ?>
                window.location.href = url;
            <?php endif; ?>
        };
    </script>
</body>

</html>