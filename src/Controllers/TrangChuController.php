<?php
require_once __DIR__ . '/../Services/BaiChiaSeService.php';

class TrangChuController {
    private $service;

    public function __construct($db) {
        $this->service = new BaiChiaSeService($db);
    }

    // Hiển thị trang chủ với tất cả bài chia sẻ
    public function index() {
        return $this->service->getTatCa();
    }

    // Hiển thị danh sách tài liệu theo môn
    public function danhSachMon($id_mon_hoc) {
        return $this->service->getDanhSachTheoMon($id_mon_hoc);
    }

    // Hiển thị chi tiết 1 tài liệu
    public function chiTiet($id) {
        return $this->service->getChiTiet($id);
    }

    // Xử lý form đăng tài liệu
    public function dangTaiTaiLieu($postData, $fileData) {
        return $this->service->uploadTaiLieu($postData, $fileData);
    }
}
