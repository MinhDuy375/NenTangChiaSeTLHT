<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../../config/ketNoiDB.php';

$message = '';

// Kiểm tra cookie remember_token khi trang load
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    try {
        $stmt = $pdo->prepare("SELECT id, ten_dang_nhap, ho_ten, vai_tro FROM nguoi_dung WHERE remember_token = ? AND trang_thai = 'hoat_dong'");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Tự động đăng nhập từ cookie
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['ten_dang_nhap'];
            $_SESSION['fullname'] = $user['ho_ten'];
            $_SESSION['vai_tro']  = $user['vai_tro'];

            // Cập nhật ngày hoạt động
            $update = $pdo->prepare("UPDATE nguoi_dung SET ngay_cap_nhat = NOW() WHERE id = ?");
            $update->execute([$user['id']]);

            header("Location: index.php?page=home");
            exit;
        } else {
            // Token không hợp lệ, xóa cookie
            setcookie("remember_token", "", time() - 3600, "/");
        }
    } catch (PDOException $e) {
        error_log("Error checking remember token: " . $e->getMessage());
    }
}

// Nếu đã đăng nhập rồi, chuyển về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php?page=home");
    exit;
}

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $message = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        try {
            // Làm sạch username nếu có hàm
            if (function_exists('lam_sach_chuoi')) {
                $username = lam_sach_chuoi($username);
            }

            $stmt = $pdo->prepare("SELECT id, ten_dang_nhap, ho_ten, mat_khau, trang_thai, vai_tro FROM nguoi_dung WHERE ten_dang_nhap = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Kiểm tra trạng thái tài khoản
                if ($user['trang_thai'] === 'khoa') {
                    $message = "Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.";
                } elseif (password_verify($password, $user['mat_khau'])) {
                    // Đăng nhập thành công
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['ten_dang_nhap'];
                    $_SESSION['fullname'] = $user['ho_ten'];
                    $_SESSION['vai_tro']  = $user['vai_tro'];

                    // Cập nhật trạng thái hoạt động
                    $update = $pdo->prepare("UPDATE nguoi_dung SET trang_thai = 'hoat_dong', ngay_cap_nhat = NOW() WHERE id = ?");
                    $update->execute([$user['id']]);

                    // Xử lý "Ghi nhớ đăng nhập"
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie("remember_token", $token, time() + (86400 * 30), "/", "", false, true); // 30 ngày, httpOnly

                        $updToken = $pdo->prepare("UPDATE nguoi_dung SET remember_token = ? WHERE id = ?");
                        $updToken->execute([$token, $user['id']]);
                    } else {
                        // Xóa remember token nếu không chọn ghi nhớ
                        $updToken = $pdo->prepare("UPDATE nguoi_dung SET remember_token = NULL WHERE id = ?");
                        $updToken->execute([$user['id']]);
                    }

                    header("Location: index.php?page=home");
                    exit;
                } else {
                    $message = "Sai mật khẩu.";
                }
            } else {
                $message = "Tài khoản không tồn tại.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $message = "Đã xảy ra lỗi. Vui lòng thử lại sau.";
        }
    }
}
?>

<?php if (!empty($message)): ?>
    <p class="message" style="color: red; text-align: center; padding: 10px;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<div class="container" style="display: flex; justify-content: center; align-items: center; ">
    <div class="form-box">
        <h2>Đăng nhập tài khoản</h2>
        <form method="post" action="">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" required>

            <label for="showPassword" style="display:inline-flex; align-items:center; cursor:pointer; margin-bottom: 15px;">
                <input type="checkbox" id="showPassword" onclick="togglePassword()" style="width: auto; margin-right: 5px;">
                Hiển mật khẩu
            </label>

            <label style="display:inline-flex; align-items:center; cursor:pointer; margin-bottom: 15px;">
                <input type="checkbox" name="remember" style="width: auto; margin-right: 5px;">
                Ghi nhớ đăng nhập
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