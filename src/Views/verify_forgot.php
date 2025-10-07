<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';

// Kiểm tra có session reset không
if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['otp_expire']) || !isset($_SESSION['reset_email'])) {
    header("Location: index.php?page=forgotpass");
    exit;
}

// Kiểm tra OTP đã hết hạn chưa (khi load trang)
if (time() > $_SESSION['otp_expire']) {
    unset($_SESSION['reset_otp'], $_SESSION['otp_expire'], $_SESSION['reset_email']);
    header("Location: index.php?page=forgotpass");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    if (empty($otp)) {
        $message = "Vui lòng nhập mã OTP.";
    } elseif (strlen($otp) !== 6 || !ctype_digit($otp)) {
        $message = "Mã OTP phải là 6 chữ số.";
    } elseif (time() > $_SESSION['otp_expire']) {
        $message = "Mã OTP đã hết hạn. Vui lòng yêu cầu gửi lại.";
        unset($_SESSION['reset_otp'], $_SESSION['otp_expire'], $_SESSION['reset_email']);
    } elseif ($otp != $_SESSION['reset_otp']) {
        $message = "❌ Mã OTP không đúng. Vui lòng kiểm tra lại.";
    } else {
        // OTP đúng -> đánh dấu đã xác thực
        $_SESSION['otp_verified'] = true;

        // Xóa OTP để không dùng lại (giữ email và otp_verified)
        unset($_SESSION['reset_otp'], $_SESSION['otp_expire']);

        // Chuyển sang trang đổi mật khẩu
        header("Location: index.php?page=reset_password");
        exit;
    }
}

// Tính thời gian còn lại
$time_remaining = max(0, $_SESSION['otp_expire'] - time());
?>

<div class="container">
    <div class="form-box">
        <h2>Xác nhận mã OTP</h2>
        <p>Mã OTP đã được gửi đến email:
            <strong style="color: #007bff;"><?= htmlspecialchars($_SESSION['reset_email']) ?></strong>
        </p>
        <p style="font-size: 14px; color: #666; text-align: center;">
            Vui lòng kiểm tra hộp thư đến (hoặc thư rác)
        </p>

        <?php if ($time_remaining > 0): ?>
            <p style="color: #007bff; font-size: 14px; text-align: center; margin: 15px 0;">
                ⏱️ Mã OTP còn hiệu lực: <span id="countdown"><?= gmdate("i:s", $time_remaining) ?></span>
            </p>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <p class="message" style="color: red; text-align: center; padding: 10px; background: #f8d7da; border-radius: 5px;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="post" onsubmit="return combineOTP();">
            <div class="otp-inputs">
                <input type="text" maxlength="1" class="otp-input" oninput="moveNext(this, 'otp2')" id="otp1" autofocus>
                <input type="text" maxlength="1" class="otp-input" oninput="moveNext(this, 'otp3')" id="otp2">
                <input type="text" maxlength="1" class="otp-input" oninput="moveNext(this, 'otp4')" id="otp3">
                <input type="text" maxlength="1" class="otp-input" oninput="moveNext(this, 'otp5')" id="otp4">
                <input type="text" maxlength="1" class="otp-input" oninput="moveNext(this, 'otp6')" id="otp5">
                <input type="text" maxlength="1" class="otp-input" id="otp6">
            </div>

            <input type="hidden" name="otp" id="otpHidden">

            <button type="submit">Xác nhận</button>
        </form>

        <div class="extra-links" style="margin-top: 15px;">
            <a href="index.php?page=forgotpass">← Gửi lại mã OTP</a> |
            <a href="index.php?page=login">Đăng nhập</a>
        </div>
    </div>
</div>

<style>
    .otp-input {
        width: 50px !important;
        height: 60px !important;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        border: 2px solid #ddd;
        border-radius: 8px;
        margin: 0 5px;
        transition: all 0.3s;
    }

    .otp-input:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .otp-input:invalid {
        border-color: #ff4d4f;
    }
</style>

<script>
    function moveNext(current, nextId) {
        // Chỉ cho phép nhập số
        current.value = current.value.replace(/[^0-9]/g, '');

        if (current.value.length === 1) {
            const nextInput = document.getElementById(nextId);
            if (nextInput) {
                nextInput.focus();
            }
        }
    }

    function combineOTP() {
        let otp = '';
        for (let i = 1; i <= 6; i++) {
            const val = document.getElementById('otp' + i).value;
            if (val === '') {
                alert('Vui lòng nhập đầy đủ 6 số OTP');
                document.getElementById('otp1').focus();
                return false;
            }
            otp += val;
        }
        document.getElementById('otpHidden').value = otp;
        return true;
    }

    // Xử lý phím Backspace
    document.querySelectorAll('.otp-input').forEach((input, index) => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '' && index > 0) {
                document.getElementById('otp' + index).focus();
            }
        });

        // Paste toàn bộ OTP
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
            if (pastedData.length === 6) {
                for (let i = 0; i < 6; i++) {
                    document.getElementById('otp' + (i + 1)).value = pastedData[i];
                }
                document.getElementById('otp6').focus();
            }
        });
    });

    // Countdown timer
    let timeLeft = <?= $time_remaining ?>;
    const countdown = setInterval(function() {
        if (timeLeft <= 0) {
            clearInterval(countdown);
            const countdownEl = document.getElementById('countdown');
            if (countdownEl) {
                countdownEl.innerHTML = "Đã hết hạn";
                countdownEl.style.color = "red";
            }

            // Hiển thị thông báo
            alert('Mã OTP đã hết hạn. Bạn sẽ được chuyển về trang gửi lại OTP.');

            // Auto redirect sau 2 giây
            setTimeout(function() {
                window.location.href = 'index.php?page=forgotpass';
            }, 2000);
        } else {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            const countdownEl = document.getElementById('countdown');
            if (countdownEl) {
                countdownEl.innerHTML =
                    String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

                // Cảnh báo khi còn 1 phút
                if (timeLeft === 60) {
                    countdownEl.style.color = '#ff9800';
                    countdownEl.style.fontWeight = 'bold';
                }
                // Cảnh báo khi còn 30 giây
                if (timeLeft === 30) {
                    countdownEl.style.color = '#ff4d4f';
                }
            }
            timeLeft--;
        }
    }, 1000);
</script>