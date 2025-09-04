<?php
// upload_tai_lieu.php - Trang upload tài liệu
include '../../config/ket_noi_csdl.php';

$thong_bao = '';
$loai_thong_bao = '';

// Xử lý upload tài liệu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dang_tai_lieu'])) {
    $tieu_de = lam_sach_chuoi($_POST['tieu_de']);
    $mo_ta = lam_sach_chuoi($_POST['mo_ta']);
    $id_mon_hoc = (int)$_POST['id_mon_hoc'];
    $id_nguoi_dung = 1; // Tạm thời set cố định, trong thực tế lấy từ session
    
    // Kiểm tra dữ liệu đầu vào
    if (empty($tieu_de) || empty($id_mon_hoc) || !isset($_FILES['file_tai_lieu'])) {
        $thong_bao = 'Vui lòng nhập đầy đủ thông tin và chọn file!';
        $loai_thong_bao = 'loi';
    } else {
        $file = $_FILES['file_tai_lieu'];
        
        if ($file['error'] == 0) {
            if (kiem_tra_loai_file($file['name'])) {
                $thu_muc_upload = 'uploads/tai_lieu/';
                if (!is_dir($thu_muc_upload)) {
                    mkdir($thu_muc_upload, 0755, true);
                }
                
                $ten_file_moi = tao_ten_file_duy_nhat($file['name']);
                $duong_dan_file = $thu_muc_upload . $ten_file_moi;
                
                if (move_uploaded_file($file['tmp_name'], $duong_dan_file)) {
                    // Lưu vào cơ sở dữ liệu
                    try {
                        $sql = "INSERT INTO bai_chia_se (loai, tieu_de, mo_ta, file_upload, id_mon_hoc, id_nguoi_dung, ngay_tao) 
                                VALUES ('tai_lieu', :tieu_de, :mo_ta, :file_upload, :id_mon_hoc, :id_nguoi_dung, NOW())";
                        
                        $stmt = $pdo->prepare($sql);
                        $ket_qua = $stmt->execute([
                            ':tieu_de' => $tieu_de,
                            ':mo_ta' => $mo_ta,
                            ':file_upload' => $duong_dan_file,
                            ':id_mon_hoc' => $id_mon_hoc,
                            ':id_nguoi_dung' => $id_nguoi_dung
                        ]);
                        
                        if ($ket_qua) {
                            $thong_bao = 'Upload tài liệu thành công!';
                            $loai_thong_bao = 'thanh_cong';
                            // Reset form
                            $_POST = array();
                        } else {
                            $thong_bao = 'Có lỗi khi lưu vào cơ sở dữ liệu!';
                            $loai_thong_bao = 'loi';
                            unlink($duong_dan_file); // Xóa file nếu lưu DB thất bại
                        }
                    } catch (PDOException $e) {
                        $thong_bao = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
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
    $danh_sach_mon_hoc = array();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Tài Liệu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(45deg, #2196F3, #21CBF3);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .nav-menu {
            background: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: #495057;
            margin: 0 15px;
            padding: 8px 16px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .nav-menu a:hover, .nav-menu a.active {
            background: #007bff;
            color: white;
        }
        
        .form-container {
            padding: 30px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
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
            resize: vertical;
            height: 100px;
        }
        
        .file-input-container {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-container input[type="file"] {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
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
        
        .file-input-label:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }
        
        .file-info {
            margin-top: 10px;
            padding: 10px;
            background: #e7f3ff;
            border-radius: 5px;
            display: none;
        }
        
        .btn {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.4);
        }
        
        .required {
            color: #dc3545;
        }
        
        .file-types {
            font-size: 14px;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Hệ Thống Quản Lý Tài Liệu</h1>
            <p>Upload và chia sẻ tài liệu học tập</p>
        </div>
        
        <div class="nav-menu">
            <a href="upload_tai_lieu.php" class="active">Upload Tài Liệu</a>
            <a href="danh_sach_mon_hoc.php">Danh Sách Môn Học</a>
        </div>
        
        <div class="form-container">
            <?php if (!empty($thong_bao)): ?>
                <div class="thong-bao <?php echo $loai_thong_bao; ?>">
                    <?php echo $thong_bao; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="tieu_de">Tiêu đề tài liệu <span class="required">*</span></label>
                    <input type="text" id="tieu_de" name="tieu_de" required 
                           value="<?php echo isset($_POST['tieu_de']) ? htmlspecialchars($_POST['tieu_de']) : ''; ?>"
                           placeholder="Nhập tiêu đề tài liệu...">
                </div>
                
                <div class="form-group">
                    <label for="id_mon_hoc">Môn học <span class="required">*</span></label>
                    <select id="id_mon_hoc" name="id_mon_hoc" required>
                        <option value="">-- Chọn môn học --</option>
                        <?php foreach ($danh_sach_mon_hoc as $mon_hoc): ?>
                            <option value="<?php echo $mon_hoc['id']; ?>"
                                    <?php echo (isset($_POST['id_mon_hoc']) && $_POST['id_mon_hoc'] == $mon_hoc['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mon_hoc['ten_mon']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
               
                
                <div class="form-group">
                    <label for="mo_ta">Mô tả chi tiết</label>
                    <textarea id="mo_ta" name="mo_ta" 
                              placeholder="Mô tả chi tiết về nội dung tài liệu..."><?php echo isset($_POST['mo_ta']) ? htmlspecialchars($_POST['mo_ta']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>File tài liệu <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="file_tai_lieu" name="file_tai_lieu" accept=".pdf,.doc,.docx" required>
                        <label for="file_tai_lieu" class="file-input-label">
                            <strong>📁 Chọn file tài liệu</strong><br>
                            Kéo thả file vào đây hoặc click để chọn
                        </label>
                    </div>
                    <div class="file-types">
                        Hỗ trợ: PDF, DOC, DOCX (Tối đa 50MB)
                    </div>
                    <div id="file-info" class="file-info"></div>
                </div>
                
                <button type="submit" name="dang_tai_lieu" class="btn">
                    🚀 Upload Tài Liệu
                </button>
            </form>
        </div>
    </div>
    
    <script>
        // Xử lý hiển thị thông tin file
        document.getElementById('file_tai_lieu').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const fileInfo = document.getElementById('file-info');
            const label = document.querySelector('.file-input-label');
            
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                fileInfo.innerHTML = `
                    <strong>File đã chọn:</strong> ${file.name}<br>
                    <strong>Kích thước:</strong> ${fileSize} MB<br>
                    <strong>Loại:</strong> ${file.type || 'Không xác định'}
                `;
                fileInfo.style.display = 'block';
                label.style.background = '#d4edda';
                label.style.borderColor = '#c3e6cb';
                label.innerHTML = `<strong>✅ ${file.name}</strong><br>Click để thay đổi file`;
            } else {
                fileInfo.style.display = 'none';
                label.style.background = '#f8f9fa';
                label.style.borderColor = '#dee2e6';
                label.innerHTML = '<strong>📁 Chọn file tài liệu</strong><br>Kéo thả file vào đây hoặc click để chọn';
            }
        });
        
        // Xử lý kéo thả file
        const fileLabel = document.querySelector('.file-input-label');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileLabel.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            fileLabel.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileLabel.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight(e) {
            fileLabel.style.background = '#e7f3ff';
        }
        
        function unhighlight(e) {
            fileLabel.style.background = '#f8f9fa';
        }
        
        fileLabel.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            document.getElementById('file_tai_lieu').files = files;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            document.getElementById('file_tai_lieu').dispatchEvent(event);
        }
    </script>
</body>
</html>