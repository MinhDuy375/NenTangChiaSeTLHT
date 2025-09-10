<?php
session_start();
require './config/ketNoiDB.php';

if (!isset($_SESSION['pending_user'])) {
    die("Không có dữ liệu đăng ký.");
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_input = $_POST['otp'];

    if ($otp_input == $_SESSION['pending_user']['otp']) {
        $data = $_SESSION['pending_user'];

        $stmt = $pdo->prepare(
            "INSERT INTO nguoi_dung (ten_dang_nhap, ho_ten, email, vai_tro, mat_khau, ngay_tao, ngay_cap_nhat, trang_thai)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?)"
        );
        $status = 'hoạt_dong'; // ✅ khi xác minh thành công thì trạng thái = hoatdong
        $stmt->execute([$data['username'], $data['name'], $data['email'], $data['role'], $data['password'], $status]);

        unset($_SESSION['pending_user']);
        $message = "🎉 Đăng ký thành công! Bạn có thể đăng nhập.";
    } else {
        $message = "❌ Mã OTP không đúng!";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Xác minh OTP</title>
</head>
<body>
    <h2>Nhập mã xác minh</h2>
    <?php if ($message): ?>
        <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Mã OTP:</label>
        <input type="text" name="otp" required>
        <button type="submit">Xác minh</button>
    </form>

    <form action="login.php" method="get" style="display:inline;">
        <button type="submit">Đăng nhập</button>
    </form>
</body>
</html>
