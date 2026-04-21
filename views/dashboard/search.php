<?php
/**
 * File Search Page
 */

define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';
require_once APP_ROOT . '/includes/Helper.php';
require_once APP_ROOT . '/includes/FileManager.php';

$auth = new Auth();
$auth->requireLogin();

$permission = new Permission();
$permission->requirePermission('file.view');

$fileManager = new FileManager();

// Get search keyword
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$files = [];
$totalFiles = 0;

if (!empty($keyword)) {
    // Search files
    $files = $fileManager->searchFiles($keyword, 100);
    $totalFiles = count($files);
}

$title = 'Tìm kiếm file - ' . htmlspecialchars($keyword);
$showNav = true;
$showSidebar = true;
$showFooter = true;

include APP_ROOT . '/views/includes/header.php';
?>

<div class="row mb-3">
    <div class="col-md-12">
        <h2><i class="fas fa-search me-2"></i>Tìm kiếm file</h2>
        
        <!-- Search form -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="<?php echo APP_URL; ?>/views/dashboard/search.php" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" 
                               placeholder="Nhập tên file cần tìm..." 
                               value="<?php echo htmlspecialchars($keyword); ?>" 
                               required autofocus>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-1"></i>Tìm kiếm
                        </button>
                    </div>
                    <?php if (!empty($keyword)): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                Tìm thấy <strong><?php echo number_format($totalFiles); ?></strong> kết quả cho: 
                                <strong>"<?php echo htmlspecialchars($keyword); ?>"</strong>
                                <a href="<?php echo APP_URL; ?>/views/dashboard/search.php" class="ms-2">
                                    <i class="fas fa-times"></i> Xóa tìm kiếm
                                </a>
                            </small>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($keyword)): ?>
    <!-- Search Results -->
    <div class="row">
        <div class="col-md-12">
            <h5><i class="fas fa-file me-2"></i>Kết quả tìm kiếm (<?php echo number_format($totalFiles); ?>)</h5>
            
            <?php if (empty($files)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Không tìm thấy kết quả</h4>
                        <p class="text-muted">Không có file nào khớp với từ khóa "<strong><?php echo htmlspecialchars($keyword); ?></strong>"</p>
                        <p class="text-muted">Thử tìm kiếm với từ khóa khác hoặc kiểm tra lại chính tả</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Tên file</th>
                                        <th class="d-none d-md-table-cell">Mô tả</th>
                                        <th class="d-none d-sm-table-cell">Kích thước</th>
                                        <th>Loại</th>
                                        <th class="d-none d-md-table-cell">Thư mục</th>
                                        <th class="d-none d-md-table-cell">Ngày tải lên</th>
                                        <th class="d-none d-lg-table-cell">Người tải</th>
                                        <th class="d-none d-xl-table-cell">Lượt tải</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($files as $file): ?>
                                        <tr>
                                            <td style="max-width: 200px;">
                                                <i class="fas <?php echo Helper::getFileIcon($file['file_extension']); ?> me-1"></i>
                                                <strong title="<?php echo htmlspecialchars($file['file_name']); ?>" class="d-inline-block text-truncate" style="max-width: 180px; vertical-align: middle;"><?php echo htmlspecialchars($file['file_name']); ?></strong>
                                            </td>
                                            <td class="d-none d-md-table-cell" style="max-width: 150px;">
                                                <span class="text-muted d-inline-block text-truncate" style="font-size: 11px; max-width: 150px;" title="<?php echo htmlspecialchars($file['description'] ?? ''); ?>"><?php echo htmlspecialchars($file['description'] ?? '-'); ?></span>
                                            </td>
                                            <td class="d-none d-sm-table-cell"><?php echo Helper::formatFileSize($file['file_size']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo strtoupper($file['file_extension']); ?></span></td>
                                            <td class="d-none d-md-table-cell">
                                                <?php if ($file['folder_name']): ?>
                                                    <a href="<?php echo APP_URL; ?>/views/dashboard/files.php?folder=<?php echo $file['folder_id']; ?>" class="text-decoration-none">
                                                        <i class="fas fa-folder text-warning me-1"></i>
                                                        <small><?php echo htmlspecialchars($file['folder_name']); ?></small>
                                                    </a>
                                                <?php else: ?>
                                                    <small class="text-muted">Root</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="d-none d-md-table-cell"><?php echo Helper::formatDate($file['uploaded_at']); ?></td>
                                            <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($file['uploader_name'] ?? $file['uploader_username'] ?? '-'); ?></td>
                                            <td class="d-none d-xl-table-cell text-center"><?php echo number_format($file['download_count']); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php 
                                                    // Các file có thể xem trực tiếp
                                                    $viewableExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'bmp'];
                                                    $canView = in_array(strtolower($file['file_extension']), $viewableExtensions);
                                                    ?>
                                                    
                                                    <?php if ($permission->can('file.view') && $canView): ?>
                                                        <button type="button" class="btn btn-outline-success" 
                                                                onclick='viewFile(<?php echo $file["id"]; ?>, <?php echo json_encode($file["file_name"]); ?>, <?php echo json_encode($file["gdrive_web_link"] ?? ""); ?>)' 
                                                                title="Xem file">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($permission->can('file.download')): ?>
                                                        <a href="<?php echo APP_URL; ?>/api/file-download.php?id=<?php echo $file['id']; ?>" 
                                                           class="btn btn-outline-primary" title="Tải xuống">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($permission->can('file.upload')): ?>
                                                        <button type="button" class="btn btn-outline-secondary" 
                                                                onclick='editDescription(<?php echo $file["id"]; ?>, <?php echo json_encode($file["description"] ?? ""); ?>)' 
                                                                title="Sửa mô tả">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($permission->can('file.delete')): ?>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="deleteFile(<?php echo $file['id']; ?>)" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- No search yet -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h4>Tìm kiếm file</h4>
                    <p class="text-muted">Nhập tên file vào ô tìm kiếm bên trên để bắt đầu</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- View File Modal -->
<div class="modal fade" id="viewFileModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Xem file: <span id="viewFileName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="overflow: hidden;">
                <div id="fileViewerContainer" style="height: 80vh;">
                    <div class="text-center py-5">
                        <div class="spinner-border" role="status"></div>
                        <p class="mt-2">Đang tải file...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="viewFileDownloadBtn" class="btn btn-primary" target="_blank">
                    <i class="fas fa-download me-2"></i>Tải xuống
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Description Modal -->
<div class="modal fade" id="editDescriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa mô tả file</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDescriptionForm">
                <div class="modal-body">
                    <input type="hidden" id="editFileId" name="file_id">
                    <div class="mb-3">
                        <label for="editFileDescription" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="editFileDescription" name="description" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/includes/footer.php'; ?>

<script>
// View file function
function viewFile(fileId, fileName, webViewLink) {
    $("#viewFileName").text(fileName);
    
    if (webViewLink) {
        $("#fileViewerContainer").html('<iframe src="' + webViewLink + '" width="100%" height="100%" frameborder="0"></iframe>');
    } else {
        $.ajax({
            url: '<?php echo APP_URL; ?>/api/file-view.php',
            method: 'POST',
            data: { file_id: fileId },
            success: function(response) {
                if (response.success && response.view_link) {
                    $("#fileViewerContainer").html('<iframe src="' + response.view_link + '" width="100%" height="100%" frameborder="0"></iframe>');
                } else {
                    $("#fileViewerContainer").html('<div class="alert alert-danger m-3">Không thể tải file: ' + (response.message || 'Lỗi không xác định') + '</div>');
                }
            },
            error: function() {
                $("#fileViewerContainer").html('<div class="alert alert-danger m-3">Lỗi kết nối đến server</div>');
            }
        });
    }
    
    $("#viewFileDownloadBtn").attr("href", "<?php echo APP_URL; ?>/api/file-download.php?id=" + fileId);
    
    var viewModal = new bootstrap.Modal(document.getElementById("viewFileModal"));
    viewModal.show();
}

function editDescription(fileId, currentDescription) {
    $("#editFileId").val(fileId);
    $("#editFileDescription").val(currentDescription);
    
    var editModal = new bootstrap.Modal(document.getElementById("editDescriptionModal"));
    editModal.show();
}

// Edit description form submit
$("#editDescriptionForm").on("submit", function(e) {
    e.preventDefault();
    
    var fileId = $("#editFileId").val();
    var description = $("#editFileDescription").val();
    
    $.ajax({
        url: '<?php echo APP_URL; ?>/api/file-update.php',
        method: 'POST',
        data: {
            file_id: fileId,
            description: description
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert("Lỗi: " + (response.message || "Không thể cập nhật mô tả"));
            }
        },
        error: function() {
            alert("Lỗi kết nối đến server");
        }
    });
});

function deleteFile(fileId) {
    if (!confirm("Bạn có chắc chắn muốn xóa file này?")) {
        return;
    }
    
    $.ajax({
        url: '<?php echo APP_URL; ?>/api/file-delete.php',
        method: 'POST',
        data: { file_id: fileId },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert("Lỗi: " + (response.message || "Không thể xóa file"));
            }
        },
        error: function() {
            alert("Lỗi kết nối đến server");
        }
    });
}
</script>
