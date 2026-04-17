<?php
/**
 * Google Drive Re-authorization Page
 * Use this to re-authorize with updated scopes
 */

define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/GoogleDriveAPI.php';

$auth = new Auth();
$auth->requireLogin();

// Delete existing token to force re-authorization
$tokenFile = APP_ROOT . '/config/gdrive_token.json';
if (file_exists($tokenFile)) {
    unlink($tokenFile);
}

// Get authorization URL
$driveAPI = new GoogleDriveAPI();
$authUrl = $driveAPI->getAuthUrl();

$title = 'Kết nối lại Google Drive';
$showNav = true;
$showFooter = true;

include APP_ROOT . '/views/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fab fa-google-drive me-2"></i>
                    Kết nối lại với Google Drive
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Cần cập nhật quyền truy cập</strong>
                </div>
                
                <p>
                    Để sử dụng tính năng <strong>hiển thị dung lượng Google Drive</strong>, 
                    ứng dụng cần thêm quyền đọc thông tin metadata.
                </p>
                
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-shield-alt me-2"></i>
                            Quyền truy cập yêu cầu:
                        </h6>
                        <ul class="mb-0">
                            <li>
                                <strong>drive.file:</strong> Quản lý files do app tạo ra 
                                <span class="badge bg-success">Đã có</span>
                            </li>
                            <li>
                                <strong>drive.metadata.readonly:</strong> Đọc thông tin dung lượng 
                                <span class="badge bg-warning">Cần thêm</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Bạn sẽ được chuyển đến trang đăng nhập Google để cấp quyền mới.
                </div>
                
                <div class="d-grid gap-2">
                    <a href="<?php echo htmlspecialchars($authUrl); ?>" class="btn btn-primary btn-lg">
                        <i class="fab fa-google me-2"></i>
                        Kết nối với Google Drive
                    </a>
                    
                    <a href="<?php echo APP_URL; ?>/views/dashboard/index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Quay lại Dashboard
                    </a>
                </div>
            </div>
            
            <div class="card-footer text-muted">
                <small>
                    <i class="fas fa-lock me-1"></i>
                    Ứng dụng chỉ có quyền truy cập files do chính nó tạo ra. 
                    Không thể đọc hoặc sửa các files khác trong Google Drive của bạn.
                </small>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/includes/footer.php'; ?>
