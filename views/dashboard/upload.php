<?php
/**
 * File Upload Page
 */

define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';
require_once APP_ROOT . '/includes/Helper.php';
require_once APP_ROOT . '/includes/FolderManager.php';

$auth = new Auth();
$auth->requireLogin();

$permission = new Permission();
$permission->requirePermission('file.upload');

$folderManager = new FolderManager();

// Get folder ID if specified
$folderId = isset($_GET['folder']) ? (int)$_GET['folder'] : null;
$currentFolder = $folderId ? $folderManager->getFolder($folderId) : null;

// Get all folders for dropdown
$allFolders = $folderManager->getFolderTree();

$title = 'Upload File';
$showNav = true;
$showSidebar = true;
$showFooter = true;

include APP_ROOT . '/views/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload File lên Google Drive</h5>
            </div>
            <div class="card-body">
                <?php if ($currentFolder): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-folder me-2"></i>Upload vào thư mục: <strong><?php echo htmlspecialchars($currentFolder['folder_name']); ?></strong>
                    </div>
                <?php endif; ?>
                
                <form id="uploadForm" action="<?php echo APP_URL; ?>/api/file-upload.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="folder_id" value="<?php echo $folderId ?? ''; ?>">
                    
                    <div class="mb-3">
                        <label for="fileInput" class="form-label">Chọn file</label>
                        <input class="form-control form-control-lg" type="file" id="fileInput" name="file" required>
                        <div class="form-text">
                            Dung lượng tối đa: <?php echo Helper::formatFileSize(MAX_UPLOAD_SIZE); ?>
                            <br>
                            Loại file được phép: <?php echo str_replace(',', ', ', strtoupper(ALLOWED_EXTENSIONS)); ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="folderSelect" class="form-label">Thư mục đích</label>
                        <select class="form-select" id="folderSelect" name="folder_id">
                            <option value="">-- Root --</option>
                            <?php
                            function renderFolderOptions($folders, $selected = null, $prefix = '') {
                                foreach ($folders as $folder) {
                                    $isSelected = ($selected == $folder['id']) ? 'selected' : '';
                                    echo '<option value="' . $folder['id'] . '" ' . $isSelected . '>';
                                    echo $prefix . htmlspecialchars($folder['folder_name']);
                                    echo '</option>';
                                    
                                    if (!empty($folder['children'])) {
                                        renderFolderOptions($folder['children'], $selected, $prefix . '&nbsp;&nbsp;&nbsp;');
                                    }
                                }
                            }
                            renderFolderOptions($allFolders, $folderId);
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả (tùy chọn)</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-3" id="uploadProgress" style="display: none;">
                        <label class="form-label">Tiến trình upload:</label>
                        <div class="progress">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%">0%</div>
                        </div>
                        <small id="uploadStatus" class="form-text text-muted"></small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" id="uploadBtn" class="btn btn-primary btn-lg">
                            <i class="fas fa-upload me-2"></i>Upload File
                        </button>
                        <a href="<?php echo APP_URL; ?>/views/dashboard/files.php<?php echo $folderId ? '?folder=' . $folderId : ''; ?>" 
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Lưu ý</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>File sẽ được upload trực tiếp lên Google Drive</li>
                    <li>Kiểm tra kết nối internet ổn định trước khi upload</li>
                    <li>File lớn có thể mất vài phút để upload</li>
                    <li>Hệ thống sẽ tự động tạo bản sao lưu metadata trong database</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$extraJS = '
<script>
$(document).ready(function() {
    $("#uploadForm").on("submit", function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var uploadBtn = $("#uploadBtn");
        var progressDiv = $("#uploadProgress");
        var progressBar = $("#progressBar");
        var statusText = $("#uploadStatus");
        
        // Disable button
        uploadBtn.prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin me-2\"></i>Đang upload...");
        
        // Show progress
        progressDiv.show();
        
        $.ajax({
            url: $(this).attr("action"),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(e) {
                    if (e.lengthComputable) {
                        var percentComplete = Math.round((e.loaded / e.total) * 100);
                        progressBar.css("width", percentComplete + "%").text(percentComplete + "%");
                        statusText.text("Đã upload: " + formatBytes(e.loaded) + " / " + formatBytes(e.total));
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    alert("✓ " + response.message);
                    window.location.href = "' . APP_URL . '/views/dashboard/files.php' . ($folderId ? '?folder=' . $folderId : '') . '";
                } else {
                    alert("✗ Lỗi: " + response.message);
                    uploadBtn.prop("disabled", false).html("<i class=\"fas fa-upload me-2\"></i>Upload File");
                    progressDiv.hide();
                }
            },
            error: function(xhr, status, error) {
                alert("✗ Lỗi khi upload: " + error);
                uploadBtn.prop("disabled", false).html("<i class=\"fas fa-upload me-2\"></i>Upload File");
                progressDiv.hide();
            }
        });
    });
    
    // Show file info when selected
    $("#fileInput").on("change", function() {
        var file = this.files[0];
        if (file) {
            var size = file.size;
            var maxSize = ' . MAX_UPLOAD_SIZE . ';
            
            if (size > maxSize) {
                alert("File quá lớn! Dung lượng tối đa: ' . Helper::formatFileSize(MAX_UPLOAD_SIZE) . '");
                $(this).val("");
                return;
            }
            
            var fileName = file.name;
            var ext = fileName.split(".").pop().toLowerCase();
            var allowedExts = "' . ALLOWED_EXTENSIONS . '".split(",");
            
            if (!allowedExts.includes(ext)) {
                alert("Loại file không được phép! Chỉ chấp nhận: ' . strtoupper(ALLOWED_EXTENSIONS) . '");
                $(this).val("");
                return;
            }
            
            console.log("File OK:", fileName, formatBytes(size));
        }
    });
    
    function formatBytes(bytes) {
        if (bytes === 0) return "0 Bytes";
        var k = 1024;
        var sizes = ["Bytes", "KB", "MB", "GB"];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
    }
});
</script>
';

include APP_ROOT . '/views/includes/footer.php';
?>
