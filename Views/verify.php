<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'ketNoiDB.php';

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


<div class="container">
    <div class="form-box">
        <h2>Xác nhận OTP</h2>
        <p>Vui lòng nhập mã xác nhận được gửi về email đăng ký</p>

        <?php if (!empty($message)): ?>
            <p class="message" style="color:red;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" onsubmit="combineOTP(); return true;">
            <div class="otp-inputs">
                <input type="text" maxlength="1" oninput="moveNext(this, 'otp2')" id="otp1">
                <input type="text" maxlength="1" oninput="moveNext(this, 'otp3')" id="otp2">
                <input type="text" maxlength="1" oninput="moveNext(this, 'otp4')" id="otp3">
                <input type="text" maxlength="1" oninput="moveNext(this, 'otp5')" id="otp4">
                <input type="text" maxlength="1" oninput="moveNext(this, 'otp6')" id="otp5">
                <input type="text" maxlength="1" id="otp6">
            </div>

            <!-- Input ẩn để gửi OTP gộp -->
            <input type="hidden" name="otp" id="otpHidden">

            <button type="submit">Xác nhận</button>
        </form>

        <form action="index.php?page=login" method="get" style="margin-top:10px;">
            <button type="submit" class="login-btn">Đăng nhập</button>
        </form>
    </div>
</div>

<script>
function moveNext(current, nextId) {
    if (current.value.length === 1 && nextId) {
        document.getElementById(nextId).focus();
    }
}

function combineOTP() {
    let otp = '';
    for (let i = 1; i <= 6; i++) {
        otp += document.getElementById('otp' + i).value;
    }
    document.getElementById('otpHidden').value = otp;
}
</script>
