<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'ketNoiDB.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $name     = $_POST['name'] ?? '';
    $email    = $_POST['email'] ?? '';
    $role     = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $message = "Mật khẩu không khớp.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM nguoi_dung WHERE ten_dang_nhap = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $message = "Tên đăng nhập đã tồn tại.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $otp = rand(100000, 999999); // sinh mã OTP 6 số

            // lưu tạm vào session
            $_SESSION['pending_user'] = [
                'username' => $username,
                'name'     => $name,
                'email'    => $email,
                'role'     => $role,
                'password' => $hashed_password,
                'otp'      => $otp
            ];

            // Gửi OTP bằng PHPMailer
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
                $mail->Subject = 'Registration verification code';
                $mail->Body    = "hello $name,<br>your OTP is: <b>$otp</b>";

                $mail->send();
                header("Location: index.php?page=verify"); // chuyển sang trang nhập OTP
                exit;
            } catch (Exception $e) {
                $message = "Không gửi được email: {$mail->ErrorInfo}";
            }
        }
    }
}
?>


<div class="container">
    <div class="form-box">
        <h2>Đăng ký tài khoản</h2>

        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label>Tên đăng nhập:</label>
            <input type="text" name="username" required>

            <label>Họ và Tên:</label>
            <input type="text" name="name" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Vai trò:</label>
            <select name="role" required>
                <option value="nguoi_dung">Người dùng</option>
                <option value="khach">Khách</option>
            </select>

            <div>
                <label>Mật khẩu:</label>
                <input type="password" name="password" required>
            </div>

            <label>Nhập lại mật khẩu:</label>
            <input type="password" name="confirm_password" required>

            <button style="margin-bottom: 20px;" type="submit">Đăng ký</button>
        </form>

        <form action="index.php" method="get" style="margin-top:10px;">
            <input type="hidden" name="page" value="login">
            <button type="submit" class="login-btn">Đăng nhập</button>
        </form>
    </div>
</div>
