<?php
// Đảm bảo đường dẫn đúng đến file kết nối DB
include __DIR__ . '/../../config/ketNoiDB.php';

$thong_bao = '';
$loai_thong_bao = '';

// Hàm helper nếu chưa có
if (!function_exists('lam_sach_chuoi')) {
    function lam_sach_chuoi($str) {
        return htmlspecialchars(strip_tags(trim($str)));
    }
}

if (!function_exists('kiem_tra_loai_file')) {
    function kiem_tra_loai_file($ten_file) {
        $phan_mo_rong = strtolower(pathinfo($ten_file, PATHINFO_EXTENSION));
        $loai_file_hop_le = ['pdf', 'doc', 'docx', 'txt', 'ppt', 'pptx'];
        return in_array($phan_mo_rong, $loai_file_hop_le);
    }
}

if (!function_exists('tao_ten_file_duy_nhat')) {
    function tao_ten_file_duy_nhat($ten_file_goc) {
        $phan_mo_rong = pathinfo($ten_file_goc, PATHINFO_EXTENSION);
        $ten_khong_mo_rong = pathinfo($ten_file_goc, PATHINFO_FILENAME);
        return date('YmdHis') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $ten_khong_mo_rong) . '.' . $phan_mo_rong;
    }
}

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
                $thu_muc_upload = __DIR__ . '/../../uploads/tai_lieu/';
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
                            ':file_upload' => 'uploads/tai_lieu/' . $ten_file_moi,
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
                            unlink($duong_dan_file);
                        }
                    } catch (PDOException $e) {
                        $thong_bao = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
                        $loai_thong_bao = 'loi';
                        if (file_exists($duong_dan_file)) {
                            unlink($duong_dan_file);
                        }
                    }
                } else {
                    $thong_bao = 'Không thể upload file!';
                    $loai_thong_bao = 'loi';
                }
            } else {
                $thong_bao = 'Chỉ chấp nhận file PDF, DOC, DOCX, TXT, PPT, PPTX!';
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
    $danh_sach_mon_hoc = $stmt_mon_hoc->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $danh_sach_mon_hoc = array();
}
?>

<style>
    .upload-container {
        background: white;
        min-height: calc(100vh - 70px);
    }
    
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 20px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><circle cx="200" cy="50" r="30"/><circle cx="400" cy="20" r="20"/><circle cx="600" cy="70" r="25"/><circle cx="800" cy="30" r="35"/></svg>');
        animation: float 8s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateX(0px); }
        50% { transform: translateX(20px); }
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .hero-section h1 {
        font-size: 2.5em;
        margin-bottom: 15px;
        font-weight: 700;
    }
    
    .hero-section p {
        font-size: 1.2em;
        opacity: 0.9;
    }
    
    .form-section {
        max-width: 800px;
        margin: -30px auto 0;
        padding: 0 20px 60px;
        position: relative;
        z-index: 3;
    }
    
    .form-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        border: 1px solid #e1e5e9;
    }
    
    .thong-bao {
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 12px;
        font-weight: 500;
        text-align: center;
        border: 1px solid;
    }
    
    .thong-bao.thanh-cong {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        color: #155724;
        border-color: #c3e6cb;
    }
    
    .thong-bao.loi {
        background: linear-gradient(135deg, #f8d7da, #f5c6cb);
        color: #721c24;
        border-color: #f5c6cb;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #2c3e50;
        font-size: 16px;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #e1e5e9;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s;
        background: #f8f9fa;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #007bff;
        background: white;
        box-shadow: 0 0 0 4px rgba(0,123,255,0.15);
        transform: translateY(-1px);
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 120px;
        line-height: 1.6;
    }
    
    .file-input-container {
        position: relative;
        margin-top: 10px;
    }
    
    .file-input-container input[type="file"] {
        opacity: 0;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        z-index: 2;
    }
    
    .file-input-label {
        display: block;
        padding: 40px 20px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border: 3px dashed #dee2e6;
        border-radius: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }
    
    .file-input-label::before {
        content: "📄";
        font-size: 3em;
        display: block;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .file-input-label:hover {
        background: linear-gradient(135deg, #e9ecef, #d1ecf1);
        border-color: #007bff;
        transform: translateY(-2px);
    }
    
    .file-input-label.has-file {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        border-color: #28a745;
    }
    
    .file-input-label.has-file::before {
        content: "✅";
        color: #28a745;
    }
    
    .file-info {
        margin-top: 15px;
        padding: 15px;
        background: linear-gradient(135deg, #e7f3ff, #cce7ff);
        border-radius: 10px;
        border-left: 4px solid #007bff;
        display: none;
    }
    
    .file-types {
        font-size: 14px;
        color: #6c757d;
        margin-top: 10px;
        text-align: center;
        font-style: italic;
    }
    
    .submit-btn {
        width: 100%;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        padding: 18px 30px;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 30px;
        box-shadow: 0 5px 20px rgba(0,123,255,0.3);
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,123,255,0.4);
    }
    
    .submit-btn:active {
        transform: translateY(0);
    }
    
    .required {
        color: #dc3545;
    }
    
    .form-help {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        border-left: 4px solid #17a2b8;
    }
    
    .form-help h3 {
        color: #17a2b8;
        margin-bottom: 10px;
        font-size: 1.2em;
    }
    
    .form-help ul {
        margin: 0;
        padding-left: 20px;
        color: #495057;
    }
    
    .form-help li {
        margin-bottom: 5px;
    }
    
    @media (max-width: 768px) {
        .hero-section {
            padding: 40px 15px;
        }
        
        .hero-section h1 {
            font-size: 2em;
        }
        
        .form-section {
            margin-top: -20px;
            padding: 0 15px 40px;
        }
        
        .form-card {
            padding: 25px;
            border-radius: 15px;
        }
        
        .file-input-label {
            padding: 30px 15px;
        }
    }
</style>

<div class="upload-container">
    <div class="hero-section">
        <div class="hero-content">
            <h1>📚 Đăng Tải Tài Liệu</h1>
            <p>Chia sẻ kiến thức, góp phần xây dựng thư viện tài liệu chung</p>
        </div>
    </div>
    
    <div class="form-section">
        <div class="form-card">
            <?php if (!empty($thong_bao)): ?>
                <div class="thong-bao <?php echo $loai_thong_bao; ?>">
                    <?php echo $thong_bao; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-help">
                <h3>💡 Hướng dẫn upload tài liệu</h3>
                <ul>
                    <li>Chọn môn học phù hợp với tài liệu</li>
                    <li>Đặt tiêu đề mô tả rõ ràng nội dung tài liệu</li>
                    <li>Viết mô tả chi tiết để người khác dễ tìm kiếm</li>
                    <li>Chỉ upload các tài liệu có bản quyền hợp lệ</li>
                </ul>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="tieu_de">📝 Tiêu đề tài liệu <span class="required">*</span></label>
                    <input type="text" id="tieu_de" name="tieu_de" required 
                           value="<?php echo isset($_POST['tieu_de']) ? htmlspecialchars($_POST['tieu_de']) : ''; ?>"
                           placeholder="VD: Bài giảng Lập trình Java - Chương 1">
                </div>
                
                <div class="form-group">
                    <label for="id_mon_hoc">📚 Môn học <span class="required">*</span></label>
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
                    <label for="mo_ta">📄 Mô tả chi tiết</label>
                    <textarea id="mo_ta" name="mo_ta" 
                              placeholder="Mô tả nội dung, chương, bài học liên quan..."><?php echo isset($_POST['mo_ta']) ? htmlspecialchars($_POST['mo_ta']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>📁 File tài liệu <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="file_tai_lieu" name="file_tai_lieu" 
                               accept=".pdf,.doc,.docx,.txt,.ppt,.pptx" required>
                        <label for="file_tai_lieu" class="file-input-label" id="file-label">
                            <strong>Chọn file tài liệu</strong><br>
                            <span style="font-size: 14px; opacity: 0.8;">Kéo thả file vào đây hoặc click để chọn</span>
                        </label>
                    </div>
                    <div class="file-types">
                        💾 Hỗ trợ: PDF, DOC, DOCX, TXT, PPT, PPTX (Tối đa 50MB)
                    </div>
                    <div id="file-info" class="file-info"></div>
                </div>
                
                <button type="submit" name="dang_tai_lieu" class="submit-btn">
                    🚀 Đăng Tải Tài Liệu
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Xử lý hiển thị thông tin file
    document.getElementById('file_tai_lieu').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const fileInfo = document.getElementById('file-info');
        const label = document.getElementById('file-label');
        
        if (file) {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            fileInfo.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>📄 File đã chọn:</strong> ${file.name}<br>
                        <strong>📊 Kích thước:</strong> ${fileSize} MB<br>
                        <strong>🏷️ Loại:</strong> ${file.type || 'Không xác định'}
                    </div>
                    <div style="font-size: 2em;">✅</div>
                </div>
            `;
            fileInfo.style.display = 'block';
            label.classList.add('has-file');
            label.innerHTML = `<strong>✅ ${file.name}</strong><br><span style="font-size: 14px; opacity: 0.8;">Click để thay đổi file</span>`;
        } else {
            fileInfo.style.display = 'none';
            label.classList.remove('has-file');
            label.innerHTML = '<strong>Chọn file tài liệu</strong><br><span style="font-size: 14px; opacity: 0.8;">Kéo thả file vào đây hoặc click để chọn</span>';
        }
    });
    
    // Xử lý kéo thả file
    const fileLabel = document.getElementById('file-label');
    const fileInput = document.getElementById('file_tai_lieu');
    
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
        fileLabel.style.borderColor = '#007bff';
        fileLabel.style.background = 'linear-gradient(135deg, #e7f3ff, #cce7ff)';
    }
    
    function unhighlight(e) {
        fileLabel.style.borderColor = '#dee2e6';
        fileLabel.style.background = 'linear-gradient(135deg, #f8f9fa, #e9ecef)';
    }
    
    fileLabel.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
        }
    }
    
    // Validation form trước khi submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const tieuDe = document.getElementById('tieu_de').value.trim();
        const monHoc = document.getElementById('id_mon_hoc').value;
        const file = document.getElementById('file_tai_lieu').files[0];
        
        if (!tieuDe || !monHoc || !file) {
            e.preventDefault();
            alert('⚠️ Vui lòng điền đầy đủ thông tin bắt buộc!');
            return;
        }
        
        if (file.size > 50 * 1024 * 1024) { // 50MB
            e.preventDefault();
            alert('⚠️ File quá lớn! Vui lòng chọn file nhỏ hơn 50MB.');
            return;
        }
        
        // Hiển thị loading
        const submitBtn = document.querySelector('.submit-btn');
        submitBtn.innerHTML = '⏳ Đang upload...';
        submitBtn.disabled = true;
    });
</script>