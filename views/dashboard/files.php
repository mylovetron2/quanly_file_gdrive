<?php
/**
 * File List Page
 */

define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';
require_once APP_ROOT . '/includes/Helper.php';
require_once APP_ROOT . '/includes/FileManager.php';
require_once APP_ROOT . '/includes/FolderManager.php';

$auth = new Auth();
$auth->requireLogin();

$permission = new Permission();
$permission->requirePermission('file.view');

$fileManager = new FileManager();
$folderManager = new FolderManager();

// Get folder ID if specified
$folderId = isset($_GET['folder']) ? (int)$_GET['folder'] : null;
$currentFolder = $folderId ? $folderManager->getFolder($folderId) : null;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Get files
$files = $fileManager->getAllFiles($folderId, $limit, $offset);
$totalFiles = $fileManager->getFileCount($folderId);
$pagination = Helper::paginate($totalFiles, $limit, $page);

// Get folders in current directory
$folders = $folderManager->getAllFolders($folderId);

// Get breadcrumb
$breadcrumb = $folderId ? $folderManager->getBreadcrumb($folderId) : [];

$title = 'Quản lý Files';
$showNav = true;
$showSidebar = true;
$showFooter = true;

include APP_ROOT . '/views/includes/header.php';
?>

<div class="row mb-3">
    <div class="col-md-12">
        <h2><i class="fas fa-file me-2"></i>Quản lý Files</h2>
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/views/dashboard/files.php">Root</a></li>
                <?php foreach ($breadcrumb as $crumb): ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo APP_URL; ?>/views/dashboard/files.php?folder=<?php echo $crumb['id']; ?>">
                            <?php echo htmlspecialchars($crumb['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mb-3">
    <div class="col-12">
        <div class="btn-group flex-wrap" role="group">
            <?php if ($permission->can('file.upload')): ?>
                <a href="<?php echo APP_URL; ?>/views/dashboard/upload.php?folder=<?php echo $folderId ?? ''; ?>" 
                   class="btn btn-primary mb-2 mb-md-0">
                    <i class="fas fa-upload me-2"></i>Upload File
                </a>
            <?php endif; ?>
            
            <?php if ($permission->can('folder.create')): ?>
                <button type="button" class="btn btn-success mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                    <i class="fas fa-folder-plus me-2"></i>Tạo Thư mục
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Folders -->
<?php if (!empty($folders)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <h5><i class="fas fa-folder me-2"></i>Thư mục</h5>
        <div class="row">
            <?php foreach ($folders as $folder): ?>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <i class="fas fa-folder fa-3x text-warning mb-2"></i>
                                    <h6 class="card-title">
                                        <a href="<?php echo APP_URL; ?>/views/dashboard/files.php?folder=<?php echo $folder['id']; ?>" 
                                           class="text-decoration-none">
                                            <?php echo htmlspecialchars($folder['folder_name']); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <?php echo isset($folder['file_count']) ? $folder['file_count'] : 0; ?> files, 
                                        <?php echo isset($folder['subfolder_count']) ? $folder['subfolder_count'] : 0; ?> folders
                                    </small>
                                </div>
                                
                                <?php if ($permission->can('folder.delete') || $permission->can('folder.manage')): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted" type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <?php if ($permission->can('folder.manage')): ?>
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                       onclick='renameFolder(<?php echo $folder["id"]; ?>, <?php echo json_encode($folder["folder_name"]); ?>); return false;'>
                                                        <i class="fas fa-edit me-2"></i>Sửa tên
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($permission->can('folder.delete')): ?>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                       onclick="deleteFolder(<?php echo $folder['id']; ?>); return false;">
                                                        <i class="fas fa-trash me-2"></i>Xóa
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Files -->
<div class="row">
    <div class="col-md-12">
        <h5><i class="fas fa-file me-2"></i>Files (<?php echo number_format($totalFiles); ?>)</h5>
        
        <?php if (empty($files)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có file nào trong thư mục này</p>
                    <?php if ($permission->can('file.upload')): ?>
                        <a href="<?php echo APP_URL; ?>/views/dashboard/upload.php?folder=<?php echo $folderId ?? ''; ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload File đầu tiên
                        </a>
                    <?php endif; ?>
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
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="mt-3">
                    <?php echo Helper::renderPagination($pagination, APP_URL . '/views/dashboard/files.php' . ($folderId ? '?folder=' . $folderId : '')); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-folder-plus me-2"></i>Tạo thư mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo APP_URL; ?>/api/folder-create.php" method="POST">
                <input type="hidden" name="parent_id" value="<?php echo $folderId ?? ''; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="folderName" class="form-label">Tên thư mục</label>
                        <input type="text" class="form-control" id="folderName" name="folder_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tạo thư mục</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rename Folder Modal -->
<div class="modal fade" id="renameFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa tên thư mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="renameFolderForm">
                <input type="hidden" id="renameFolderId" name="folder_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="renameFolderName" class="form-label">Tên thư mục mới</label>
                        <input type="text" class="form-control" id="renameFolderName" name="folder_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit File Description Modal -->
<div class="modal fade" id="editDescriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa mô tả file</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editDescriptionForm">
                <input type="hidden" id="editFileId" name="file_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editFileDescription" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="editFileDescription" name="description" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View File Modal -->
<div class="modal fade" id="viewFileModal" tabindex="-1">
    <div class="modal-dialog modal-xl" style="max-width: calc(100vw - 2rem); margin: 1rem;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-truncate" style="max-width: calc(100% - 50px);"><i class="fas fa-eye me-2"></i><span id="viewFileName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="overflow: hidden;">
                <div id="fileViewerContainer" style="height: 75vh; width: 100%; max-width: 100%;">
                    <iframe id="fileViewerFrame" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="viewFileOpenNew" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-external-link-alt me-2"></i>Mở tab mới
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<?php
$extraJS = '
<script>
function deleteFile(fileId) {
    if (!confirm("Bạn có chắc muốn xóa file này?")) {
        return;
    }
    
    $.post("' . APP_URL . '/api/file-delete.php", {
        file_id: fileId
    }, function(response) {
        if (response.success) {
            alert(response.message);
            location.reload();
        } else {
            alert("Lỗi: " + response.message);
        }
    }, "json");
}

function deleteFolder(folderId) {
    if (!confirm("Bạn có chắc muốn xóa thư mục này?")) {
        return;
    }
    
    $.post("' . APP_URL . '/api/folder-delete.php", {
        folder_id: folderId
    }, function(response) {
        if (response.success) {
            alert(response.message);
            location.reload();
        } else {
            alert("Lỗi: " + response.message);
        }
    }, "json");
}

function renameFolder(folderId, currentName) {
    $("#renameFolderId").val(folderId);
    $("#renameFolderName").val(currentName);
    
    var renameModal = new bootstrap.Modal(document.getElementById("renameFolderModal"));
    renameModal.show();
}

$("#renameFolderForm").on("submit", function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize();
    var submitBtn = $(this).find("button[type=submit]");
    var originalBtnText = submitBtn.html();
    
    submitBtn.prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin me-2\"></i>Đang lưu...");
    
    $.ajax({
        url: "' . APP_URL . '/api/folder-update.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(response) {
            if (response.success) {
                alert("✓ " + response.message);
                bootstrap.Modal.getInstance(document.getElementById("renameFolderModal")).hide();
                location.reload();
            } else {
                alert("✗ Lỗi: " + response.message);
                submitBtn.prop("disabled", false).html(originalBtnText);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response Text:", xhr.responseText);
            
            var errorMsg = "Lỗi kết nối server";
            try {
                var jsonResponse = JSON.parse(xhr.responseText);
                if (jsonResponse.message) {
                    errorMsg = jsonResponse.message;
                }
            } catch(e) {}
            
            alert("✗ " + errorMsg);
            submitBtn.prop("disabled", false).html(originalBtnText);
        }
    });
});

function editDescription(fileId, currentDescription) {
    $("#editFileId").val(fileId);
    $("#editFileDescription").val(currentDescription);
    
    var editModal = new bootstrap.Modal(document.getElementById("editDescriptionModal"));
    editModal.show();
}

function viewFile(fileId, fileName, webLink) {
    console.log("viewFile called:", fileId, fileName, webLink);
    $("#viewFileName").text(fileName);
    
    if (webLink && webLink !== "") {
        console.log("Using existing link:", webLink);
        displayFileViewer(webLink);
    } else {
        console.log("Fetching link from API...");
        $.get("' . APP_URL . '/api/file-view.php", {id: fileId}, function(response) {
            console.log("API response:", response);
            if (response.success) {
                displayFileViewer(response.web_link);
            } else {
                alert("Lỗi: " + response.message);
            }
        }, "json").fail(function(xhr, status, error) {
            console.error("API error:", status, error);
            alert("Không thể lấy link xem file");
        });
    }
}

function displayFileViewer(webLink) {
    console.log("displayFileViewer:", webLink);
    var viewerUrl = webLink;
    
    if (!webLink.includes("/view") && !webLink.includes("/preview")) {
        viewerUrl = "https://docs.google.com/viewer?url=" + encodeURIComponent(webLink) + "&embedded=true";
    }
    
    console.log("Viewer URL:", viewerUrl);
    $("#fileViewerFrame").attr("src", viewerUrl);
    $("#viewFileOpenNew").attr("href", webLink);
    
    var viewModal = new bootstrap.Modal(document.getElementById("viewFileModal"));
    viewModal.show();
}

$("#editDescriptionForm").on("submit", function(e) {
    e.preventDefault();
    
    var formData = $(this).serialize();
    var submitBtn = $(this).find("button[type=submit]");
    var originalBtnText = submitBtn.html();
    
    submitBtn.prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin me-2\"></i>Đang lưu...");
    
    $.ajax({
        url: "' . APP_URL . '/api/file-update.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(response) {
            if (response.success) {
                alert("✓ " + response.message);
                bootstrap.Modal.getInstance(document.getElementById("editDescriptionModal")).hide();
                location.reload();
            } else {
                alert("✗ Lỗi: " + response.message);
                submitBtn.prop("disabled", false).html(originalBtnText);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.error("Response Text:", xhr.responseText);
            
            var errorMsg = "Lỗi kết nối server";
            try {
                var jsonResponse = JSON.parse(xhr.responseText);
                if (jsonResponse.message) {
                    errorMsg = jsonResponse.message;
                }
            } catch(e) {}
            
            alert("✗ " + errorMsg);
            submitBtn.prop("disabled", false).html(originalBtnText);
        }
    });
});
</script>
';

include APP_ROOT . '/views/includes/footer.php';
?>
