<?php
session_start();
require './config/ketNoiDB.php';
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
                header("Location: verify.php"); // chuyển sang trang nhập OTP
                exit;
            } catch (Exception $e) {
                $message = "Không gửi được email: {$mail->ErrorInfo}";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Đăng ký</title>
    <meta charset="UTF-8">
</head>
<body>
    <h2>Đăng ký tài khoản</h2>
    <?php if ($message): ?>
        <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Tên đăng nhập:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Họ và Tên:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Vai trò:</label><br>
        <select name="role" required>
            <option value="nguoi_dung">Người dùng</option>
            <option value="quan_tri_vien">Quản trị viên</option>
            <option value="khach">Khách</option>
        </select><br><br>

        <label>Mật khẩu:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Nhập lại mật khẩu:</label><br>
        <input type="password" name="confirm_password" required><br><br>

        <button type="submit">Đăng ký</button>
    </form>

    <br>
    <form action="login.php" method="get" style="display:inline;">
        <button type="submit">Đăng nhập</button>
    </form>
</body>
</html>
