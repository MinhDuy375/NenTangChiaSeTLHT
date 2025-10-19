<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../../config/ketNoiDB.php';
include __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';

// Nếu đã đăng nhập, chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php?page=home");
    exit;
}

// Xóa session cũ nếu có
if (isset($_SESSION['otp_verified'])) {
    unset($_SESSION['otp_verified']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $message = "Vui lòng nhập email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email không hợp lệ.";
    } else {
        try {
            // Kiểm tra email có tồn tại trong DB không
            $stmt = $pdo->prepare("SELECT id, ho_ten, trang_thai FROM nguoi_dung WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $message = "Email chưa được đăng ký.";
            } elseif ($user['trang_thai'] === 'khoa') {
                $message = "Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.";
            } else {
                // Tạo OTP ngẫu nhiên
                $otp = rand(100000, 999999);

                // Lưu OTP vào session (hết hạn sau 10 phút)
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_otp']   = $otp;
                $_SESSION['otp_expire']  = time() + 600; // 10 phút

                // Gửi OTP qua email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'doyousay987@gmail.com';
                    $mail->Password   = 'ppty zzgt xtho wquw';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom('doyousay987@gmail.com', 'Sharedy Platform');
                    $mail->addAddress($email, $user['ho_ten']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Mã OTP đặt lại mật khẩu';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4;'>
                            <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px;'>
                                <h2 style='color: #007bff;'>Xin chào {$user['ho_ten']}!</h2>
                                <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản tại Sharedy.</p>
                                <p>Mã OTP của bạn là:</p>
                                <div style='background: #007bff; color: white; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; border-radius: 5px; letter-spacing: 5px;'>
                                    $otp
                                </div>
                                <p style='margin-top: 20px;'>Mã này có hiệu lực trong <strong>10 phút</strong>.</p>
                                <p style='color: #ff4d4f; margin-top: 20px;'>
                                    <strong>⚠️ Lưu ý:</strong> Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này và bảo mật tài khoản của bạn.
                                </p>
                                <p style='color: #666; font-size: 14px; margin-top: 30px;'>
                                    Đây là email tự động, vui lòng không trả lời email này.
                                </p>
                            </div>
                        </div>
                    ";

                    $mail->send();

                    // Chuyển sang trang verify OTP
                    header("Location: index.php?page=verify_forgot");
                    exit;
                } catch (Exception $e) {
                    error_log("Email sending error: " . $mail->ErrorInfo);
                    $message = "Không thể gửi OTP. Vui lòng thử lại sau.";

                    // Xóa session nếu gửi email thất bại
                    unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['otp_expire']);
                }
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $message = "Đã xảy ra lỗi. Vui lòng thử lại sau.";
        }
    }
}
?>

<div class="container" style="display: flex; justify-content: center; align-items: center; ">
    <div class="form-box">
        <h2>Quên mật khẩu</h2>
        <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 20px;">
            Nhập email đã đăng ký để nhận mã OTP đặt lại mật khẩu
        </p>

        <?php if (!empty($message)): ?>
            <p class="message" style="color: red; text-align: center; padding: 10px; background: #f8d7da; border-radius: 5px;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="">
            <label for="email">Email: <span style="color: red;">*</span></label>
            <input type="email"
                name="email"
                id="email"
                required
                autofocus
                placeholder="Nhập email của bạn"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <button type="submit" style="margin-top: 10px;">Gửi mã OTP</button>
        </form>

        <div class="extra-links" style="margin-top: 15px;">
            <a href="index.php?page=login">← Quay lại đăng nhập</a> |
            <a href="index.php?page=register">Đăng ký tài khoản mới</a>
        </div>
    </div>
</div>

<style>
    input[type="email"]::placeholder {
        color: #999;
        font-size: 14px;
    }
</style>