<?php
// Kết nối database
require './config/ketNoiDB.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $message = "Vui lòng nhập email.";
    } else {
        // Kiểm tra email có tồn tại trong database không
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $message = "Email không tồn tại trong hệ thống.";
        } else {
            // Ở đây bạn sẽ tạo token reset mật khẩu và gửi email
            // Giả lập gửi email thành công
            $message = "Một email chứa hướng dẫn đổi mật khẩu đã được gửi tới: $email";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quên mật khẩu</title>
</head>
<body>

<h2>Quên mật khẩu</h2>

<?php if ($message): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="post" action="">
    <label for="email">Nhập email của bạn:</label><br>
    <input type="email" name="email" id="email" required><br><br>
    <input type="submit" value="Gửi yêu cầu">
</form>

   <!-- Nút Đăng nhập -->
    <form action="login.php" method="get" style="display:inline;">
        <button type="submit">Đăng nhập</button>
    </form>

</body>
</html>
