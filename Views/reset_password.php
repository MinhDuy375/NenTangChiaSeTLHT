<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'ketNoiDB.php'; // file này tạo kết nối PDO: $conn = new PDO(...)

$message = '';

if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: index.php?page=forgotpass");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (empty($password) || empty($confirm)) {
        $message = "Vui lòng nhập đầy đủ.";
    } elseif ($password !== $confirm) {
        $message = "Mật khẩu không khớp.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $email = $_SESSION['reset_email'];

        try {
            $stmt = $pdo->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE email = ?");
            $stmt->execute([$hashed, $email]);

            // Xóa session để tránh reuse
            unset($_SESSION['otp_verified'], $_SESSION['reset_email']);

            $message = "Đặt lại mật khẩu thành công! <a href='index.php?page=login.'>Đăng nhập</a>";
        } catch (PDOException $e) {
            $message = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <div class="form-box">
        <h2>Đặt lại mật khẩu</h2>

        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <label for="password">Mật khẩu mới:</label>
            <input type="password" name="password" id="password" required>

            <label for="confirm">Xác nhận mật khẩu:</label>
            <input type="password" name="confirm" id="confirm" required>

            <button style="margin-bottom: 20px;" type="submit">Đổi mật khẩu</button>
        </form>

        <form action="index.php?page=login" method="get">
            <button type="submit" class="login-btn">Đăng nhập</button>
        </form>
    </div>
</div>


