<?php
// dangTaiTaiLieu.php
include __DIR__ . '/../../config/ketNoiDB.php';

$thong_bao = '';
$loai_thong_bao = '';

// Xử lý upload tài liệu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dang_tai_lieu'])) {
    $tieu_de = lam_sach_chuoi($_POST['tieu_de']);
    $mo_ta = lam_sach_chuoi($_POST['mo_ta']);
    $id_mon_hoc = (int)$_POST['id_mon_hoc'];
    $id_nguoi_dung = 1; // tạm thời fix cứng

    if (empty($tieu_de) || empty($id_mon_hoc) || !isset($_FILES['file_tai_lieu'])) {
        $thong_bao = 'Vui lòng nhập đầy đủ thông tin và chọn file!';
        $loai_thong_bao = 'loi';
    } else {
        $file = $_FILES['file_tai_lieu'];
        if ($file['error'] == 0) {
            if (kiem_tra_loai_file($file['name'])) {
                $thu_muc_upload = __DIR__ . '/../../uploads/tai_lieu/';
                if (!is_dir($thu_muc_upload)) {
                    mkdir($thu_muc_upload, 0755, true);
                }
                $ten_file_moi = tao_ten_file_duy_nhat($file['name']);
                $duong_dan_file = $thu_muc_upload . $ten_file_moi;

                if (move_uploaded_file($file['tmp_name'], $duong_dan_file)) {
                    try {
                        $sql = "INSERT INTO bai_chia_se (loai, tieu_de, mo_ta, file_upload, id_mon_hoc, id_nguoi_dung, ngay_tao) 
                                VALUES ('tai_lieu', :tieu_de, :mo_ta, :file_upload, :id_mon_hoc, :id_nguoi_dung, NOW())";
                        $stmt = $pdo->prepare($sql);
                        $ket_qua = $stmt->execute([
                            ':tieu_de' => $tieu_de,
                            ':mo_ta' => $mo_ta,
                            ':file_upload' => 'uploads/tai_lieu/' . $ten_file_moi,
                            ':id_mon_hoc' => $id_mon_hoc,
                            ':id_nguoi_dung' => $id_nguoi_dung
                        ]);
                        if ($ket_qua) {
                            $thong_bao = 'Upload tài liệu thành công!';
                            $loai_thong_bao = 'thanh-cong';
                            $_POST = [];
                        } else {
                            $thong_bao = 'Có lỗi khi lưu vào cơ sở dữ liệu!';
                            $loai_thong_bao = 'loi';
                            unlink($duong_dan_file);
                        }
                    } catch (PDOException $e) {
                        $thong_bao = 'Lỗi CSDL: ' . $e->getMessage();
                        $loai_thong_bao = 'loi';
                        unlink($duong_dan_file);
                    }
                } else {
                    $thong_bao = 'Không thể upload file!';
                    $loai_thong_bao = 'loi';
                }
            } else {
                $thong_bao = 'Chỉ chấp nhận file PDF, DOC, DOCX!';
                $loai_thong_bao = 'loi';
            }
        } else {
            $thong_bao = 'Có lỗi khi upload file!';
            $loai_thong_bao = 'loi';
        }
    }
}

// Lấy danh sách môn học
try {
    $sql_mon_hoc = "SELECT * FROM mon_hoc ORDER BY ten_mon";
    $stmt_mon_hoc = $pdo->query($sql_mon_hoc);
    $danh_sach_mon_hoc = $stmt_mon_hoc->fetchAll();
} catch (PDOException $e) {
    $danh_sach_mon_hoc = [];
}

ob_start();
?>

<style>
.upload-container {
    max-width: 800px;
    margin: 20px auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.thong-bao {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: 500;
}
.thong-bao.thanh-cong {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.thong-bao.loi {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.form-group { margin-bottom: 20px; }
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
}
.form-group textarea {
    resize: vertical; /* chỉ cho phép kéo dọc */
    height: 100px;
    max-height: 300px;
    min-height: 100px;
}
.file-input-container { position: relative; width: 100%; }
.file-input-container input[type="file"] {
    opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;
}
.file-input-label {
    display: block;
    padding: 12px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}
.file-input-label:hover { background: #e9ecef; border-color: #adb5bd; }
.file-info {
    margin-top: 10px; padding: 10px; background: #e7f3ff; border-radius: 5px; display: none;
}
.btn {
    background: linear-gradient(45deg, #007bff, #0056b3);
    color: white; padding: 12px 30px; border: none; border-radius: 8px;
    font-size: 16px; font-weight: 600; cursor: pointer; width: 100%;
    transition: all 0.3s;
}
.btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,123,255,0.4); }
</style>

<div class="upload-container">
    <h2>📚 Upload tài liệu</h2>

    <?php if (!empty($thong_bao)): ?>
        <div class="thong-bao <?= $loai_thong_bao; ?>">
            <?= $thong_bao; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="tieu_de">Tiêu đề <span style="color:red">*</span></label>
            <input type="text" name="tieu_de" id="tieu_de" required
                   value="<?= isset($_POST['tieu_de']) ? htmlspecialchars($_POST['tieu_de']) : '' ?>">
        </div>

        <div class="form-group">
            <label for="id_mon_hoc">Môn học <span style="color:red">*</span></label>
            <select name="id_mon_hoc" id="id_mon_hoc" required>
                <option value="">-- Chọn môn học --</option>
                <?php foreach ($danh_sach_mon_hoc as $mon): ?>
                    <option value="<?= $mon['id'] ?>" <?= (isset($_POST['id_mon_hoc']) && $_POST['id_mon_hoc']==$mon['id'])?'selected':'' ?>>
                        <?= htmlspecialchars($mon['ten_mon']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="mo_ta">Mô tả</label>
            <textarea name="mo_ta" id="mo_ta"><?= isset($_POST['mo_ta']) ? htmlspecialchars($_POST['mo_ta']) : '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="file_tai_lieu">File tài liệu <span style="color:red">*</span></label>
            <div class="file-input-container">
                <input type="file" name="file_tai_lieu" id="file_tai_lieu" accept=".pdf,.doc,.docx" required>
                <label for="file_tai_lieu" class="file-input-label">📁 Chọn file tài liệu</label>
            </div>
            <div class="file-info" id="file-info"></div>
        </div>

        <button type="submit" name="dang_tai_lieu" class="btn">🚀 Upload</button>
    </form>
</div>

<script>
document.getElementById('file_tai_lieu').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const info = document.getElementById('file-info');
    if (file) {
        info.innerHTML = `<b>${file.name}</b> - ${(file.size/1024/1024).toFixed(2)} MB`;
        info.style.display = 'block';
    } else {
        info.style.display = 'none';
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout.php';

