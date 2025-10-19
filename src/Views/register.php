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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = 'nguoi_dung';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($username) || empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "Vui lòng nhập đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email không hợp lệ.";
    } elseif (strlen($username) < 3) {
        $message = "Tên đăng nhập phải có ít nhất 3 ký tự.";
    } elseif (strlen($password) < 6) {
        $message = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif ($password !== $confirm_password) {
        $message = "Mật khẩu không khớp.";
    } else {
        try {
            // Kiểm tra username đã tồn tại
            $stmt = $pdo->prepare("SELECT id FROM nguoi_dung WHERE ten_dang_nhap = ?");
            $stmt->execute([$username]);

            if ($stmt->rowCount() > 0) {
                $message = "Tên đăng nhập đã tồn tại.";
            } else {
                // Kiểm tra email đã tồn tại
                $stmt = $pdo->prepare("SELECT id FROM nguoi_dung WHERE email = ?");
                $stmt->execute([$email]);

                if ($stmt->rowCount() > 0) {
                    $message = "Email đã được đăng ký.";
                } else {
                    // Tạo OTP và hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $otp = rand(100000, 999999);

                    // Lưu vào session với thời gian hết hạn
                    $_SESSION['pending_user'] = [
                        'username' => $username,
                        'name'     => $name,
                        'email'    => $email,
                        'role'     => $role,
                        'password' => $hashed_password,
                        'otp'      => $otp,
                        'otp_expire' => time() + 600 // OTP hết hạn sau 10 phút
                    ];

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
                        $mail->addAddress($email, $name);

                        $mail->isHTML(true);
                        $mail->Subject = 'Mã xác thực đăng ký tài khoản';
                        $mail->Body    = "
                            <div style='font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4;'>
                                <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px;'>
                                    <h2 style='color: #007bff;'>Xin chào $name!</h2>
                                    <p>Cảm ơn bạn đã đăng ký tài khoản tại Sharedy.</p>
                                    <p>Mã OTP của bạn là:</p>
                                    <div style='background: #007bff; color: white; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; border-radius: 5px; letter-spacing: 5px;'>
                                        $otp
                                    </div>
                                    <p style='margin-top: 20px;'>Mã này có hiệu lực trong <strong>10 phút</strong>.</p>
                                    <p style='color: #666; font-size: 14px; margin-top: 30px;'>Nếu bạn không yêu cầu đăng ký, vui lòng bỏ qua email này.</p>
                                </div>
                            </div>
                        ";

                        $mail->send();
                        header("Location: index.php?page=verify");
                        exit;
                    } catch (Exception $e) {
                        $message = "Không gửi được email. Lỗi: {$mail->ErrorInfo}";
                        unset($_SESSION['pending_user']);
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Register error: " . $e->getMessage());
            $message = "Đã xảy ra lỗi. Vui lòng thử lại sau.";
        }
    }
}
?>

<div class="container" style="display: flex; justify-content: center; align-items: center; ">
    <div class="form-box">
        <h2>Đăng ký tài khoản</h2>

        <?php if (!empty($message)): ?>
            <p class="message" style="color: red;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label for="username">Tên đăng nhập: <span style="color: red;">*</span></label>
            <input type="text" id="username" name="username" required minlength="3"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

            <label for="name">Họ và Tên: <span style="color: red;">*</span></label>
            <input type="text" id="name" name="name" required
                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">

            <label for="email">Email: <span style="color: red;">*</span></label>
            <input type="email" id="email" name="email" required
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">



            <label for="password">Mật khẩu: <span style="color: red;">*</span></label>
            <input type="password" id="password" name="password" required minlength="6">
            <small style="color: #666; font-size: 12px;">Tối thiểu 6 ký tự</small>

            <label for="confirm_password">Nhập lại mật khẩu: <span style="color: red;">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">

            <button type="submit" style="margin-top: 20px;">Đăng ký</button>
        </form>

        <div class="extra-links" style="margin-top: 15px;">
            Đã có tài khoản? <a href="index.php?page=login">Đăng nhập ngay</a>
        </div>
    </div>
</div>