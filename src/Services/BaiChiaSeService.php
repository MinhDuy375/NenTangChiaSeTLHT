<?php
require_once __DIR__ . '/../Models/BaiChiaSeModel.php';

class BaiChiaSeService {
    private $model;

    public function __construct($db) {
        $this->model = new BaiChiaSeModel($db);
    }

    // Xử lý upload + thêm bài chia sẻ
    public function uploadTaiLieu($postData, $fileData) {
        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filePath = null;
        if ($fileData && $fileData['error'] === UPLOAD_ERR_OK) {
            $fileName = time() . "_" . basename($fileData['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($fileData['tmp_name'], $targetPath)) {
                $filePath = 'public/uploads/' . $fileName;
            } else {
                throw new Exception("Lỗi upload file!");
            }
        }

        $data = [
            ':loai' => $postData['loai'],
            ':tieu_de' => $postData['tieu_de'],
            ':mo_ta' => $postData['mo_ta'] ?? null,
            ':file_upload' => $filePath,
            ':link_host' => $postData['link_host'] ?? null,
            ':link_source' => $postData['link_source'] ?? null,
            ':cong_nghe' => $postData['cong_nghe'] ?? null,
            ':id_mon_hoc' => $postData['id_mon_hoc'] ?? null,
            ':id_danh_muc' => $postData['id_danh_muc'] ?? null,
            ':id_nguoi_dung' => $postData['id_nguoi_dung'],
            ':tom_tat' => $postData['tom_tat'] ?? null
        ];

        return $this->model->insert($data);
    }

    public function getDanhSachTheoMon($id_mon_hoc) {
        return $this->model->getByMonHoc($id_mon_hoc);
    }

    public function getChiTiet($id) {
        return $this->model->getById($id);
    }

    public function getTatCa() {
        return $this->model->getAll();
    }
}
