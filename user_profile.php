<?php

require 'KetNoiDB.php'; // dùng file kết nối DB mà bạn gửi

// ===== GIẢ SỬ user đã đăng nhập =====
// Khi login thành công bạn nhớ set $_SESSION['user_id'] = id trong DB
if (!isset($_SESSION['user_id'])) {
    die("Bạn chưa đăng nhập!");
}

$id = $_SESSION['user_id'];

// ===== LẤY THÔNG TIN NGƯỜI DÙNG =====
$sql = "SELECT id, ten_dang_nhap, email, ngay_tao, mat_khau,ho_ten FROM nguoi_dung WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("Không tìm thấy người dùng.");
}

// Nếu người dùng cập nhật họ tên

if (isset($_POST['update_name'])) {
    $new_name = trim($_POST['ho_ten']);
    if (!empty($new_name)) {
        $sql = "UPDATE nguoi_dung SET ho_ten = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_name, $_SESSION['user_id']]);

        // 🔹 Cập nhật lại session để layout hiển thị ngay
        $_SESSION['fullname'] = $new_name;

        $message = "<p class='success-msg'>Cập nhật họ và tên thành công!</p>";

        // đồng bộ với biến $user để hiển thị trên chính trang này
        $user['ho_ten'] = $new_name;
    } else {
        $message = "<p class='error-msg'>Họ và tên không được để trống.</p>";
    }
}

// ===== XỬ LÝ ĐỔI MẬT KHẨU =====
$message = "";
if (isset($_POST['change_pass'])) {
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if ($new_pass !== $confirm_pass) {
        $message = "<p style='color:red'>Mật khẩu mới không khớp!</p>";
    } else {
        // Kiểm tra mật khẩu cũ
        if (!password_verify($old_pass, $user['mat_khau'])) {
            $message = "<p style='color:red'>Mật khẩu cũ không đúng!</p>";
        } else {
            // Hash mật khẩu mới rồi cập nhật
            $hash_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?");
            $update->execute([$hash_new_pass, $id]);

            $message = "<p style='color:green'>Đổi mật khẩu thành công!</p>";
        }
    }
}
?>

<div class="profile-container">
    <!-- Thanh tab -->
    <div class="profile-tabs">
        <a href="#" class="active">Thông tin cá nhân</a>
        <a href="#">Lịch sử thanh toán</a>
        <a href="#">Thông báo</a>
        <a href="#">Thay đổi mật khẩu</a>
    </div>

    <!-- Nội dung -->
    <div class="profile-content">
        <h2>Thông tin cá nhân</h2>
        <?= $message ?? '' ?>

        <!-- Cập nhật họ tên -->
        <form method="post" class="profile-form">
            <label for="username">Tên đăng nhập</label>
            <input type="text" id="username" value="<?= htmlspecialchars($user['ten_dang_nhap']) ?>" disabled>

            <label for="ho_ten">Họ và Tên</label>
            <input type="text" id="ho_ten" name="ho_ten" value="<?= htmlspecialchars($user['ho_ten']) ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>

            <label for="created">Ngày tạo</label>
            <input type="text" id="created" value="<?= dinh_dang_ngay($user['ngay_tao']) ?>" disabled>

            <button type="submit" name="update_name">Cập nhật thông tin</button>
        </form>

        <h3>Đổi mật khẩu</h3>
        <form method="post" class="profile-form">
            <label for="old_pass">Mật khẩu cũ</label>
            <input type="password" id="old_pass" name="old_pass" required>

            <label for="new_pass">Mật khẩu mới</label>
            <input type="password" id="new_pass" name="new_pass" required>

            <label for="confirm_pass">Xác nhận mật khẩu mới</label>
            <input type="password" id="confirm_pass" name="confirm_pass" required>

            <button type="submit" name="change_pass">Đổi mật khẩu</button>
        </form>
    </div>
</div>
<div class="back-home">
    <a href="index.php?page=home">← Quay lại trang chủ</a>
</div>

