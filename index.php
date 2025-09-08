<?php
// index.php

// Xác định page từ URL
$page = $_GET['page'] ?? 'home';

// Bản đồ route
$routes = [
    'home'            => __DIR__ . '/src/Views/TrangChu.php',
    'danhSachMon'     => __DIR__ . '/src/Views/danhSachMon.php',
    'lienHe'          => __DIR__ . '/src/Views/lienHe.php',
    'dangTaiTaiLieu'  => __DIR__ . '/src/Views/dangTaiTaiLieu.php',
    'chiTietTaiLieu'  => __DIR__ . '/src/Views/chiTietTaiLieu.php',
    'taiLieuMon'      => __DIR__ . '/src/Views/taiLieuMon.php',
    'thuVienNguon'    => __DIR__ . '/src/Views/thuVienNguon.php',
    'chiTietNguon'    => __DIR__ . '/src/Views/chiTietNguon.php',
    'dangTaiNguon'    => __DIR__ . '/src/Views/dangTaiNguon.php',

];

// Xác định file view
$viewFile = $routes[$page] ?? $routes['home'];

// Bắt output từ view
ob_start();
include $viewFile;
$content = ob_get_clean();

// Include layout
$title = ucfirst($page) . " | Sharedy";
include __DIR__ . '/layout.php';
