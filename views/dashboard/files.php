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
    <div class="col-md-12">
        <div class="btn-group" role="group">
            <?php if ($permission->can('file.upload')): ?>
                <a href="<?php echo APP_URL; ?>/views/dashboard/upload.php?folder=<?php echo $folderId ?? ''; ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Upload File
                </a>
            <?php endif; ?>
            
            <?php if ($permission->can('folder.create')): ?>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createFolderModal">
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
                                
                                <?php if ($permission->can('folder.delete')): ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-muted" type="button" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item text-danger" href="#"
                                                   onclick="deleteFolder(<?php echo $folder['id']; ?>); return false;">
                                                    <i class="fas fa-trash me-2"></i>Xóa
                                                </a>
                                            </li>
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
                                    <th>Kích thước</th>
                                    <th>Loại</th>
                                    <th>Ngày tải lên</th>
                                    <th>Người tải</th>
                                    <th>Lượt tải</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td>
                                            <i class="fas <?php echo Helper::getFileIcon($file['file_extension']); ?> me-2"></i>
                                            <strong><?php echo htmlspecialchars($file['file_name']); ?></strong>
                                        </td>
                                        <td><?php echo Helper::formatFileSize($file['file_size']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo strtoupper($file['file_extension']); ?></span></td>
                                        <td><?php echo Helper::formatDate($file['uploaded_at']); ?></td>
                                        <td><?php echo htmlspecialchars($file['uploader_name'] ?? $file['uploader_username'] ?? '-'); ?></td>
                                        <td><?php echo number_format($file['download_count']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if ($permission->can('file.download')): ?>
                                                    <a href="<?php echo APP_URL; ?>/api/file-download.php?id=<?php echo $file['id']; ?>" 
                                                       class="btn btn-outline-primary" title="Tải xuống">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($permission->can('file.share')): ?>
                                                    <button type="button" class="btn btn-outline-info" 
                                                            onclick="shareFile(<?php echo $file['id']; ?>)" title="Chia sẻ">
                                                        <i class="fas fa-share"></i>
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

<?php
$extraJS = <<<'JS'
<script>
function deleteFile(fileId) {
    if (!confirm('Bạn có chắc muốn xóa file này?')) {
        return;
    }
    
    $.post('<?php echo APP_URL; ?>/api/file-delete.php', {
        file_id: fileId
    }, function(response) {
        if (response.success) {
            alert(response.message);
            location.reload();
        } else {
            alert('Lỗi: ' + response.message);
        }
    }, 'json');
}

function deleteFolder(folderId) {
    if (!confirm('Bạn có chắc muốn xóa thư mục này?')) {
        return;
    }
    
    $.post('<?php echo APP_URL; ?>/api/folder-delete.php', {
        folder_id: folderId
    }, function(response) {
        if (response.success) {
            alert(response.message);
            location.reload();
        } else {
            alert('Lỗi: ' + response.message);
        }
    }, 'json');
}

function shareFile(fileId) {
    alert('Tính năng chia sẻ file đang được phát triển');
}
</script>
JS;

include APP_ROOT . '/views/includes/footer.php';
?>
