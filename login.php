<?php
session_start();
require './config/ketNoiDB.php'; // PDO: $pdo

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (function_exists('lam_sach_chuoi')) {
        $username = lam_sach_chuoi($username);
        $password = lam_sach_chuoi($password);
    }

    $stmt = $pdo->prepare("SELECT id, mat_khau FROM nguoi_dung WHERE ten_dang_nhap = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['mat_khau'])) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id']  = $user['id'];

            $update = $pdo->prepare("UPDATE nguoi_dung SET trang_thai = 'hoat_dong', ngay_cap_nhat = NOW() WHERE id = ?");
            $update->execute([$user['id']]);

            header("Location: index.php");
            exit;
        } else {
            $message = "Sai mật khẩu.";
        }
    } else {
        $message = "Tài khoản không tồn tại.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Đăng nhập</title>
    <meta charset="UTF-8">
</head>
<body>
    <h2>Đăng nhập</h2>

    <?php if ($message): ?>
        <p style="color:red;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Tên đăng nhập:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Mật khẩu:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Đăng nhập</button>
    </form>

    <br>
    <form action="register.php" method="get" style="display:inline;">
        <button type="submit">Đăng ký</button>
    </form>
</body>
</html>
