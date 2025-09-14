<?php
class BaiChiaSeModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Thêm bài chia sẻ mới
    public function insert($data) {
        $sql = "INSERT INTO bai_chia_se (loai, tieu_de, mo_ta, file_upload, link_host, link_source, cong_nghe, id_mon_hoc, id_danh_muc, id_nguoi_dung, tom_tat)
                VALUES (:loai, :tieu_de, :mo_ta, :file_upload, :link_host, :link_source, :cong_nghe, :id_mon_hoc, :id_danh_muc, :id_nguoi_dung, :tom_tat)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    // Lấy tất cả tài liệu theo môn
    public function getByMonHoc($id_mon_hoc) {
        $sql = "SELECT * FROM bai_chia_se WHERE id_mon_hoc = :id_mon_hoc ORDER BY ngay_tao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id_mon_hoc' => $id_mon_hoc]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết 1 bài chia sẻ
    public function getById($id) {
        $sql = "SELECT * FROM bai_chia_se WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy tất cả bài chia sẻ (dùng cho trang chủ)
    public function getAll() {
        $sql = "SELECT * FROM bai_chia_se ORDER BY ngay_tao DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
