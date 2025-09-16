<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'ketNoiDB.php'; // file này tạo kết nối PDO: $conn = new PDO(...)
require 'vendor/autoload.php'; // cần cài: composer require phpmailer/phpmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $message = "Vui lòng nhập email.";
    } else {
        try {
            // Kiểm tra email có tồn tại trong DB không
            $stmt =$pdo ->prepare("SELECT id FROM nguoi_dung WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $message = "Email chưa được đăng ký.";
            } else {
                // Tạo OTP ngẫu nhiên
                $otp = rand(100000, 999999);

                // Lưu OTP vào session (hết hạn sau 5 phút)
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_otp']   = $otp;
                $_SESSION['otp_expire']  = time() + 300;

                // Gửi OTP qua email
                $mail = new PHPMailer(true);
                try {
                   $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'doyousay987@gmail.com';
                $mail->Password   = 'ppty zzgt xtho wquw'; // app password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('doyousay987@gmail.com', 'web sharing learning materials');
                $mail->addAddress($email, $name);

                    $mail->isHTML(true);
                    $mail->Subject = 'Mã OTP đặt lại mật khẩu';
                    $mail->Body    = "Mã OTP của bạn là: <b>$otp</b>. Có hiệu lực trong 5 phút.";

                    $mail->send();

                    // Chuyển sang trang verify OTP
                    header("Location: index.php?page=verify_forgot");
                    exit;
                } catch (Exception $e) {
                    $message = "Không thể gửi OTP. Lỗi: {$mail->ErrorInfo}";
                }
            }
        } catch (PDOException $e) {
            $message = "Lỗi truy vấn: " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <div class="form-box">
        <h2>Quên mật khẩu</h2>

        <?php if (!empty($message)): ?>
            <p class="message" style="color:red;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label for="email">Nhập email của bạn:</label>
            <input type="email" name="email" id="email" required>
            <input type="submit" value="Gửi OTP">
        </form>

        <!-- Nút về trang đăng nhập -->
        <form action="index.php" method="get" style="margin-top:10px;">
            <input type="hidden" name="page" value="login">
            <button type="submit" class="login-btn">Đăng nhập</button>
        </form>
    </div>
</div>
