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
            background-color: #f8f9fa;
        }
        
        .main-header { 
            position: fixed;   
            top: 0;            
            left: 0;
            right: 0;
            z-index: 1000; 
            background: linear-gradient(135deg, #007bff, #0056b3); 
            color: white; 
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            padding: 10px 0;
        }
        
        .logo:hover {
            opacity: 0.9;
        }

        .header-search-container {
            flex: 1;
            max-width: 400px;
            margin: 0 30px;
        }
        
        .header-search-box {
            width: 100%;
            height: 40px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 20px;
            font-size: 14px;
            transition: all 0.3s;
            padding: 0 20px;
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .header-search-box::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .header-search-box:focus {
            outline: none;
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.8);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.1);
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
            background: rgba(255,255,255,0.2);
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
            color: rgba(255,255,255,0.9);
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
            border: 2px solid rgba(255,255,255,0.3);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
            border-color: #fff;
        }
        
        .logout-btn {
            background: rgba(255,77,79,0.9);
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
            background: #343a40; 
            color: white;
            text-align: center; 
            padding: 20px;
            margin-top: auto;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                height: auto;
                padding: 10px;
            }
            
            .header-search-container {
                margin: 10px 0;
                max-width: 100%;
            }
            
            .nav-menu {
                flex-wrap: wrap;
                gap: 10px;
                justify-content: center;
            }
            
            .user-section {
                margin-left: 0;
                margin-top: 10px;
            }
            
            main {
                padding-top: 120px;
            }
            
            .user-name {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .nav-menu a {
                padding: 6px 12px;
                font-size: 14px;
            }
            
            .logo {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <a href="index.php?page=home" class="logo">📚 Sharedy</a>
        
        <div class="header-search-container">
            <input type="text" 
                   class="header-search-box" 
                   id="search-input" 
                   placeholder="Tìm kiếm nhanh..."
                   onkeyup="tim_kiem_mon_hoc()">
        </div>
        
        <nav class="nav-menu">
            <a href="index.php?page=home" <?= ($page ?? 'home') == 'home' ? 'class="active"' : '' ?>>
                Trang chủ
            </a>
            <a href="index.php?page=monhoc" <?= ($page ?? '') == 'monhoc' ? 'class="active"' : '' ?>>
                Môn học
            </a>
            <a href="index.php?page=upload" <?= ($page ?? '') == 'upload' ? 'class="active"' : '' ?>>
                Đăng tải
            </a>
            <a href="index.php?page=contact" <?= ($page ?? '') == 'contact' ? 'class="active"' : '' ?>>
                Liên hệ
            </a>
        </nav>
        
        <div class="user-section">
            <span class="user-name">Minh Duy</span>
            <div class="user-avatar" title="Minh Duy">MD</div>
            <form action="logout.php" method="post" style="margin:0;">
                <button type="submit" class="logout-btn">Đăng xuất</button>
            </form>
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
        // Hàm tìm kiếm chung
        function tim_kiem_mon_hoc() {
            const searchInput = document.getElementById('search-input');
            const searchTerm = searchInput.value.toLowerCase().trim();
            
            // Kiểm tra xem có đang ở trang danh sách môn học không
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
    </script>
</body>
</html>