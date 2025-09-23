<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);

    // Kiểm tra OTP trong session
    if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['otp_expire'])) {
        $message = "OTP không tồn tại hoặc đã hết hạn.";
    } elseif (time() > $_SESSION['otp_expire']) {
        $message = "OTP đã hết hạn.";
        unset($_SESSION['reset_otp'], $_SESSION['otp_expire']);
    } elseif ($otp != $_SESSION['reset_otp']) {
        $message = "OTP không đúng.";
    } else {
        // OTP đúng -> đánh dấu đã xác thực
        $_SESSION['otp_verified'] = true;

        // Xóa OTP để không dùng lại
        unset($_SESSION['reset_otp'], $_SESSION['otp_expire']);

        // Chuyển sang trang đổi mật khẩu
        header("Location: index.php?page=reset_password");
        exit;
    }
}
?>

<div class="container">
    <div class="form-box">
        <h2>Xác nhận OTP</h2>
        <p>Vui lòng nhập mã xác nhận được gửi về email đăng ký</p>

        <?php if (!empty($message)): ?>
            <p class="message" style="color:red;"><?php echo htmlspecialchars($message); ?></p>
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
