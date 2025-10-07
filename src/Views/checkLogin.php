<?php
// checkLogin.php - File kiểm tra đăng nhập tập trung
// Đặt file này trong thư mục config/ hoặc src/Views/

// Hàm kiểm tra đăng nhập
function kiem_tra_dang_nhap($yeu_cau_bat_buoc = false)
{
    // Kiểm tra session đã được start chưa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Kiểm tra người dùng đã đăng nhập chưa
    $da_dang_nhap = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

    // Nếu yêu cầu bắt buộc đăng nhập và chưa đăng nhập
    if ($yeu_cau_bat_buoc && !$da_dang_nhap) {
        // Lưu trang hiện tại để redirect sau khi đăng nhập
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

        // Hiển thị popup
        hien_thi_popup_dang_nhap();
        exit; // Dừng thực thi code phía sau
    }

    return $da_dang_nhap;
}

// Hàm hiển thị popup
function hien_thi_popup_dang_nhap()
{
?>
    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Yêu cầu đăng nhập</title>
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
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .popup-overlay {
                background: rgba(0, 0, 0, 0.5);
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                animation: fadeIn 0.3s ease;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            .popup-content {
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 500px;
                width: 100%;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.3s ease;
                text-align: center;
            }

            @keyframes slideUp {
                from {
                    transform: translateY(50px);
                    opacity: 0;
                }

                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            .popup-icon {
                font-size: 80px;
                margin-bottom: 20px;
                animation: bounce 0.6s ease;
            }

            @keyframes bounce {

                0%,
                100% {
                    transform: translateY(0);
                }

                50% {
                    transform: translateY(-10px);
                }
            }

            .popup-title {
                font-size: 28px;
                color: #333;
                margin-bottom: 15px;
                font-weight: 700;
            }

            .popup-message {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
                line-height: 1.6;
            }

            .popup-buttons {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn {
                padding: 15px 30px;
                border: none;
                border-radius: 12px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                min-width: 160px;
                justify-content: center;
            }

            .btn-primary {
                background: linear-gradient(135deg, #007bff, #0056b3);
                color: white;
                box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
            }

            .btn-secondary {
                background: #f8f9fa;
                color: #6c757d;
                border: 2px solid #dee2e6;
            }

            .btn-secondary:hover {
                background: #e9ecef;
                transform: translateY(-2px);
            }

            .btn:active {
                transform: translateY(0);
            }

            .extra-info {
                margin-top: 25px;
                padding-top: 25px;
                border-top: 1px solid #e9ecef;
                font-size: 14px;
                color: #999;
            }

            @media (max-width: 480px) {
                .popup-content {
                    padding: 30px 20px;
                }

                .popup-title {
                    font-size: 24px;
                }

                .popup-buttons {
                    flex-direction: column;
                }

                .btn {
                    width: 100%;
                }
            }
        </style>
    </head>

    <body>
        <div class="popup-overlay">
            <div class="popup-content">
                <div class="popup-icon">🔐</div>
                <h2 class="popup-title">Yêu cầu đăng nhập</h2>
                <p class="popup-message">
                    Bạn cần đăng nhập để sử dụng tính năng này.<br>
                    Vui lòng đăng nhập hoặc tiếp tục khám phá các tính năng khác.
                </p>

                <div class="popup-buttons">
                    <a href="index.php?page=login" class="btn btn-primary">
                        🔑 Đăng nhập ngay
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        ← Quay lại
                    </a>
                </div>

                <div class="extra-info">
                    💡 Mẹo: Đăng nhập để lưu tài liệu, bình luận và nhiều tính năng khác
                </div>
            </div>
        </div>

        <script>
            // Tự động focus vào nút đăng nhập
            document.querySelector('.btn-primary')?.focus();

            // Cho phép ESC để quay lại
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    history.back();
                }
            });
        </script>
    </body>

    </html>
<?php
}
?>