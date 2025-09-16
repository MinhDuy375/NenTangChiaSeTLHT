<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'ketNoiDB.php'; // file PDO: $pdo

$message = '';

// Nếu đã có cookie remember_token thì tự động đăng nhập
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $stmt = $pdo->prepare("SELECT id, ten_dang_nhap, ho_ten FROM nguoi_dung WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['ten_dang_nhap'];
        $_SESSION['fullname'] = $user['ho_ten'];
        header("Location: index.php");
        exit;
    }
}

// Nếu người dùng submit form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (function_exists('lam_sach_chuoi')) {
        $username = lam_sach_chuoi($username);
        $password = lam_sach_chuoi($password);
    }

    $stmt = $pdo->prepare("SELECT id, ten_dang_nhap, ho_ten, mat_khau FROM nguoi_dung WHERE ten_dang_nhap = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['mat_khau'])) {
            // Tạo session
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['ten_dang_nhap']; // tên đăng nhập
            $_SESSION['fullname'] = $user['ho_ten'];        // họ tên

            // Cập nhật trạng thái hoạt động
            $update = $pdo->prepare("UPDATE nguoi_dung SET trang_thai = 'hoat_dong', ngay_cap_nhat = NOW() WHERE id = ?");
            $update->execute([$user['id']]);

            // Nếu chọn ghi nhớ đăng nhập
            if ($remember) {
                $token = bin2hex(random_bytes(16)); // tạo token ngẫu nhiên
                setcookie("remember_token", $token, time() + (86400 * 30), "/"); // cookie sống 30 ngày

                $updToken = $pdo->prepare("UPDATE nguoi_dung SET remember_token = ? WHERE id = ?");
                $updToken->execute([$token, $user['id']]);
            }

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
<?php if (!empty($message)): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<div class="container">
    <div class="form-box">
        <h2>Đăng nhập tài khoản</h2>
        <form method="post" action="">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" required>

            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" required>

            <label for="showPassword" style="display:inline-flex; align-items:center; cursor:pointer; ">
                <input type="checkbox" id="showPassword" onclick="togglePassword()"> Hiện mật khẩu
            </label>

            <label>
                <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
            </label>

            <button type="submit">Đăng nhập</button>
        </form>

        <div class="extra-links">
            <a href="index.php?page=register">Đăng ký</a> |
            <a href="index.php?page=forgotpass">Quên mật khẩu?</a>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const showCheckbox = document.getElementById("showPassword");
    passwordInput.type = showCheckbox.checked ? "text" : "password";
}
</script>
