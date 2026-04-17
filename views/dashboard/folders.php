<?php
/**
 * Dashboard - Folder Management
 */

define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';
require_once APP_ROOT . '/includes/FolderManager.php';
require_once APP_ROOT . '/includes/Helper.php';

$auth = new Auth();
$auth->requireLogin();

$permission = new Permission();

if (!$permission->can('folder.manage')) {
    Helper::redirect(APP_URL . '/views/dashboard/index.php');
}

$folderManager = new FolderManager();

// Get folder tree
$folderTree = $folderManager->getFolderTree();

$title = 'Quản lý thư mục';
$showNav = true;
$showFooter = true;

include APP_ROOT . '/views/includes/header.php';
?>

<div class="row mb-3">
    <div class="col-md-12">
        <h2><i class="fas fa-folder-tree me-2"></i>Quản lý thư mục</h2>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mb-3">
    <div class="col-md-12">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal">
            <i class="fas fa-folder-plus me-2"></i>Tạo thư mục mới
        </button>
        <a href="<?php echo APP_URL; ?>/views/dashboard/files.php" class="btn btn-outline-secondary">
            <i class="fas fa-file me-2"></i>Xem tất cả files
        </a>
    </div>
</div>

<!-- Folder Tree -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-sitemap me-2"></i>Cấu trúc thư mục</h5>
                <hr>
                
                <?php if (empty($folderTree)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Chưa có thư mục nào. Hãy tạo thư mục đầu tiên!
                    </div>
                <?php else: ?>
                    <div class="folder-tree">
                        <?php renderFolderTree($folderTree); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
            <form id="createFolderForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="folder_name" class="form-label">Tên thư mục *</label>
                        <input type="text" class="form-control" id="folder_name" name="folder_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Thư mục cha (để trống nếu tạo ở thư mục gốc)</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">-- Thư mục gốc --</option>
                            <?php renderFolderOptions($folderTree); ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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

<!-- Edit Folder Modal -->
<div class="modal fade" id="editFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Sửa thư mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFolderForm">
                <input type="hidden" id="edit_folder_id" name="folder_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_folder_name" class="form-label">Tên thư mục *</label>
                        <input type="text" class="form-control" id="edit_folder_name" name="folder_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
/**
 * Render folder tree recursively
 */
function renderFolderTree($folders, $level = 0) {
    if (empty($folders)) return;
    
    echo '<ul class="folder-list ' . ($level == 0 ? 'root-level' : '') . '">';
    
    foreach ($folders as $folder) {
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        $fileCount = isset($folder['file_count']) ? $folder['file_count'] : 0;
        $subfolderCount = isset($folder['subfolder_count']) ? $folder['subfolder_count'] : 0;
        
        echo '<li class="folder-item" data-folder-id="' . $folder['id'] . '">';
        echo '<div class="folder-info d-flex align-items-center justify-content-between mb-2 p-2 border rounded">';
        echo '<div class="folder-details">';
        echo $indent;
        echo '<i class="fas fa-folder text-warning me-2"></i>';
        echo '<strong>' . htmlspecialchars($folder['folder_name']) . '</strong>';
        echo ' <span class="badge bg-secondary ms-2">' . $fileCount . ' files</span>';
        echo ' <span class="badge bg-info ms-1">' . $subfolderCount . ' folders</span>';
        
        if (!empty($folder['description'])) {
            echo '<br>' . $indent . '<small class="text-muted ms-4">' . htmlspecialchars($folder['description']) . '</small>';
        }
        
        echo '<br>' . $indent . '<small class="text-muted ms-4">';
        echo 'Tạo: ' . Helper::formatDate($folder['created_at']);
        echo ' bởi ' . htmlspecialchars($folder['creator_name'] ?? 'Unknown');
        echo '</small>';
        echo '</div>';
        
        echo '<div class="folder-actions">';
        echo '<div class="btn-group btn-group-sm">';
        echo '<a href="' . APP_URL . '/views/dashboard/files.php?folder=' . $folder['id'] . '" class="btn btn-outline-primary" title="Xem files">';
        echo '<i class="fas fa-eye"></i>';
        echo '</a>';
        echo '<button type="button" class="btn btn-outline-success" onclick="editFolder(' . $folder['id'] . ')" title="Sửa">';
        echo '<i class="fas fa-edit"></i>';
        echo '</button>';
        echo '<button type="button" class="btn btn-outline-danger" onclick="deleteFolder(' . $folder['id'] . ')" title="Xóa">';
        echo '<i class="fas fa-trash"></i>';
        echo '</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // Render subfolders
        if (!empty($folder['children'])) {
            renderFolderTree($folder['children'], $level + 1);
        }
        
        echo '</li>';
    }
    
    echo '</ul>';
}

/**
 * Render folder options for select dropdown
 */
function renderFolderOptions($folders, $level = 0) {
    if (empty($folders)) return;
    
    foreach ($folders as $folder) {
        $indent = str_repeat('--', $level);
        echo '<option value="' . $folder['id'] . '">';
        echo $indent . ' ' . htmlspecialchars($folder['folder_name']);
        echo '</option>';
        
        if (!empty($folder['children'])) {
            renderFolderOptions($folder['children'], $level + 1);
        }
    }
}

$extraJS = '
<script>
// Create folder
$("#createFolderForm").on("submit", function(e) {
    e.preventDefault();
    
    $.post("' . APP_URL . '/api/folder-create.php", $(this).serialize(), function(response) {
        if (response.success) {
            alert("✓ " + response.message);
            location.reload();
        } else {
            alert("✗ Lỗi: " + response.message);
        }
    }, "json").fail(function() {
        alert("✗ Lỗi kết nối server");
    });
});

// Edit folder
function editFolder(folderId) {
    // Get folder info via AJAX
    $.get("' . APP_URL . '/api/folder-info.php?id=" + folderId, function(response) {
        if (response.success) {
            $("#edit_folder_id").val(response.data.id);
            $("#edit_folder_name").val(response.data.folder_name);
            $("#edit_description").val(response.data.description || "");
            $("#editFolderModal").modal("show");
        } else {
            alert("✗ Lỗi: " + response.message);
        }
    }, "json");
}

// Update folder
$("#editFolderForm").on("submit", function(e) {
    e.preventDefault();
    
    $.post("' . APP_URL . '/api/folder-update.php", $(this).serialize(), function(response) {
        if (response.success) {
            alert("✓ " + response.message);
            location.reload();
        } else {
            alert("✗ Lỗi: " + response.message);
        }
    }, "json").fail(function() {
        alert("✗ Lỗi kết nối server");
    });
});

// Delete folder
function deleteFolder(folderId) {
    if (!confirm("Bạn có chắc muốn xóa thư mục này? Tất cả files và thư mục con cũng sẽ bị xóa!")) {
        return;
    }
    
    $.post("' . APP_URL . '/api/folder-delete.php", {
        folder_id: folderId
    }, function(response) {
        if (response.success) {
            alert("✓ " + response.message);
            location.reload();
        } else {
            alert("✗ Lỗi: " + response.message);
        }
    }, "json").fail(function() {
        alert("✗ Lỗi kết nối server");
    });
}
</script>

<style>
.folder-list {
    list-style: none;
    padding-left: 0;
}

.folder-list ul {
    list-style: none;
    padding-left: 20px;
}

.folder-item {
    margin-bottom: 10px;
}

.folder-info {
    background-color: #f8f9fa;
}

.folder-info:hover {
    background-color: #e9ecef;
}

.root-level {
    padding-left: 20px;
}
</style>
';

include APP_ROOT . '/views/includes/footer.php';
?>
