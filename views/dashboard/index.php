<?php
/**
 * Dashboard Home
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
$fileManager = new FileManager();
$folderManager = new FolderManager();
$db = Database::getInstance();

// Get statistics
$totalFiles = $fileManager->getFileCount();

$db->query("SELECT COUNT(*) as count FROM folders");
$totalFolders = $db->fetchColumn();

$db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
$totalUsers = $db->fetchColumn();

$db->query("SELECT SUM(file_size) as total FROM files");
$totalSize = $db->fetchColumn() ?? 0;

// Get recent files
$recentFiles = $fileManager->getAllFiles(null, 10, 0);

// Get recent activity
$userId = $auth->getCurrentUserId();
$db->query("
    SELECT al.*, u.username, u.full_name 
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 15
");
$recentActivity = $db->fetchAll();

$title = 'Dashboard';
$showNav = true;
$showFooter = true;

include APP_ROOT . '/views/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-dashboard me-2"></i>Dashboard</h2>
        <p class="text-muted">Chào mừng trở lại, <?php echo $_SESSION['full_name'] ?? $_SESSION['username']; ?>!</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Tổng Files</h6>
                        <h3 class="mb-0"><?php echo number_format($totalFiles); ?></h3>
                    </div>
                    <i class="fas fa-file fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Thư mục</h6>
                        <h3 class="mb-0"><?php echo number_format($totalFolders); ?></h3>
                    </div>
                    <i class="fas fa-folder fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Dung lượng</h6>
                        <h3 class="mb-0"><?php echo Helper::formatFileSize($totalSize); ?></h3>
                    </div>
                    <i class="fas fa-database fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Người dùng</h6>
                        <h3 class="mb-0"><?php echo number_format($totalUsers); ?></h3>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Thao tác nhanh</h5>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($permission->can('file.upload')): ?>
                        <a href="<?php echo APP_URL; ?>/views/dashboard/upload.php" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload File
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($permission->can('folder.create')): ?>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                            <i class="fas fa-folder-plus me-2"></i>Tạo Thư mục
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($permission->can('file.view')): ?>
                        <a href="<?php echo APP_URL; ?>/views/dashboard/files.php" class="btn btn-info">
                            <i class="fas fa-list me-2"></i>Danh sách Files
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($auth->isAdmin()): ?>
                        <a href="<?php echo APP_URL; ?>/views/admin/users.php" class="btn btn-secondary">
                            <i class="fas fa-users-cog me-2"></i>Quản lý Users
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Files -->
    <div class="col-md-7 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file me-2"></i>Files gần đây</h5>
                <a href="<?php echo APP_URL; ?>/views/dashboard/files.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentFiles)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Chưa có file nào</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tên file</th>
                                    <th>Kích thước</th>
                                    <th>Ngày tải lên</th>
                                    <th>Người tải</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentFiles as $file): ?>
                                    <tr>
                                        <td>
                                            <i class="fas <?php echo Helper::getFileIcon($file['file_extension']); ?> me-2"></i>
                                            <?php echo htmlspecialchars($file['file_name']); ?>
                                        </td>
                                        <td><?php echo Helper::formatFileSize($file['file_size']); ?></td>
                                        <td><?php echo Helper::timeAgo($file['uploaded_at']); ?></td>
                                        <td><?php echo htmlspecialchars($file['uploader_name'] ?? $file['uploader_username'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-md-5 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Hoạt động gần đây</h5>
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                <?php if (empty($recentActivity)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Chưa có hoạt động nào</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-primary fw-bold">
                                        <?php echo htmlspecialchars($activity['full_name'] ?? $activity['username'] ?? 'System'); ?>
                                    </small>
                                    <small class="text-muted"><?php echo Helper::timeAgo($activity['created_at']); ?></small>
                                </div>
                                <p class="mb-0"><small><?php echo htmlspecialchars($activity['description']); ?></small></p>
                            </div>
                        <?php endforeach; ?>
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
            <form action="<?php echo APP_URL; ?>/api/folder-create.php" method="POST">
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

<?php include APP_ROOT . '/views/includes/footer.php'; ?>
