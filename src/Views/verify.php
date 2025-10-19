<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../../config/ketNoiDB.php';

// Kiểm tra có dữ liệu pending không
if (!isset($_SESSION['pending_user'])) {
    header("Location: index.php?page=register");
    exit;
}

// Kiểm tra OTP đã hết hạn chưa
if (isset($_SESSION['pending_user']['otp_expire']) && time() > $_SESSION['pending_user']['otp_expire']) {
    unset($_SESSION['pending_user']);
    header("Location: index.php?page=register");
    exit;
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_input = trim($_POST['otp'] ?? '');

    if (empty($otp_input)) {
        $message = "Vui lòng nhập mã OTP.";
    } elseif (strlen($otp_input) !== 6 || !ctype_digit($otp_input)) {
        $message = "Mã OTP phải là 6 chữ số.";
    } else {
        // Kiểm tra OTP có hết hạn không
        if (time() > $_SESSION['pending_user']['otp_expire']) {
            $message = "Mã OTP đã hết hạn. Vui lòng đăng ký lại.";
            unset($_SESSION['pending_user']);
        } elseif ($otp_input == $_SESSION['pending_user']['otp']) {
            // OTP đúng, thêm vào database
            $data = $_SESSION['pending_user'];

            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO nguoi_dung (ten_dang_nhap, ho_ten, email, vai_tro, mat_khau, ngay_tao, ngay_cap_nhat, trang_thai)
                     VALUES (?, ?, ?, ?, ?, NOW(), NOW(), 'hoat_dong')"
                );
                $stmt->execute([
                    $data['username'],
                    $data['name'],
                    $data['email'],
                    $data['role'],
                    $data['password']
                ]);

                unset($_SESSION['pending_user']);
                $success = true;
                $message = "🎉 Đăng ký thành công! Bạn có thể đăng nhập ngay.";
            } catch (PDOException $e) {
                error_log("Verify registration error: " . $e->getMessage());
                $message = "Đã xảy ra lỗi khi tạo tài khoản. Vui lòng thử lại.";
            }
        } else {
            $message = "❌ Mã OTP không đúng!";
        }
    }
}

// Tính thời gian còn lại
$time_remaining = 0;
if (isset($_SESSION['pending_user']['otp_expire'])) {
    $time_remaining = max(0, $_SESSION['pending_user']['otp_expire'] - time());
}
?>

<div class="container" style="display: flex; justify-content: center; align-items: center;  ">
    <div class="form-box" style="width: 500px">
        <h2>Xác nhận OTP</h2>
        <p>Vui lòng nhập mã xác nhận được gửi về email:
            <strong><?= htmlspecialchars($_SESSION['pending_user']['email'] ?? '') ?></strong>
        </p>

        <?php if ($time_remaining > 0): ?>
            <p style="color: #007bff; font-size: 14px; text-align: center;">
                ⏱️ Mã OTP còn hiệu lực: <span id="countdown"><?= gmdate("i:s", $time_remaining) ?></span>
            </p>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <p class="message" style="color: <?= $success ? 'green' : 'red' ?>; text-align: center; padding: 10px; background: <?= $success ? '#d4edda' : '#f8d7da' ?>; border-radius: 5px;">
                <?= $message ?>
            </p>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="post" onsubmit="combineOTP(); return true;">
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
                <a href="index.php?page=register">← Quay lại đăng ký</a>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php?page=login" style="display: inline-block; padding: 12px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 8px;">
                    Đăng nhập ngay
                </a>
            </div>
        <?php endif; ?>
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
    });

    // Countdown timer
    <?php if ($time_remaining > 0): ?>
        let timeLeft = <?= $time_remaining ?>;
        const countdown = setInterval(function() {
            if (timeLeft <= 0) {
                clearInterval(countdown);
                document.getElementById('countdown').innerHTML = "Đã hết hạn";
                document.getElementById('countdown').style.color = "red";

                // Tự động redirect sau 3 giây
                setTimeout(function() {
                    window.location.href = 'index.php?page=register';
                }, 3000);
            } else {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                document.getElementById('countdown').innerHTML =
                    String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                timeLeft--;
            }
        }, 1000);
    <?php endif; ?>
</script>