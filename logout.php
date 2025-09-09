<?php
session_start();
require 'ketNoiDB.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE nguoi_dung SET trang_thai = 'khoa', ngay_cap_nhat = NOW() WHERE id = ?");
    $stmt->execute([$user_id]);

    session_unset();
    session_destroy();
}

header("Location: login.php");
exit;
