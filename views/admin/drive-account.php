<?php
/**
 * Admin View: Drive Storage Account Info
 * Shows which Google account is being used for file storage
 */

define('APP_ROOT', dirname(dirname(__DIR__)));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/Permission.php';

$auth = new Auth();
$auth->requireAdmin();

$permission = new Permission();

$pageTitle = "Thông tin lưu trữ Google Drive";
include APP_ROOT . '/views/includes/header.php';
include APP_ROOT . '/views/includes/navbar.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fab fa-google-drive text-primary"></i> Thông tin lưu trữ Google Drive</h2>
            <p class="text-muted">Quản lý account Google Drive được sử dụng để lưu trữ file</p>
        </div>
    </div>

        <!-- Account Info Card -->
        <div class="row" id="accountInfoContainer">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Đang tải thông tin...</p>
            </div>
        </div>

        <!-- Re-authentication Instructions -->
        <div class="row mt-4" id="reAuthInstructions" style="display: none;">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Chuyển đổi account lưu trữ</h5>
                    </div>
                    <div class="card-body">
                        <h6>Để chuyển sang account lưu trữ khác:</h6>
                        <ol>
                            <li>Xóa file token hiện tại: <code>config/gdrive_token.json</code></li>
                            <li>Truy cập: <a href="<?php echo APP_URL; ?>/api/google-auth.php" target="_blank"><?php echo APP_URL; ?>/api/google-auth.php</a></li>
                            <li>Click "Login with Google" và <strong>chọn account mới</strong> bạn muốn dùng để lưu file</li>
                            <li>Sau khi xác thực xong, quay lại trang này để kiểm tra</li>
                        </ol>
                        
                        <div class="alert alert-info mt-3">
                            <strong><i class="fas fa-info-circle"></i> Lưu ý:</strong>
                            <ul class="mb-0">
                                <li><strong>OAuth App Setup:</strong> vẫn giữ nguyên trong <code><?php echo GOOGLE_ACCOUNT_EMAIL; ?></code></li>
                                <li><strong>File Storage:</strong> sẽ lưu vào account bạn chọn khi Login</li>
                                <li>File cũ vẫn ở trong account cũ, cần di chuyển thủ công nếu muốn</li>
                            </ul>
                        </div>

                        <hr>
                        
                        <h6>Cách xóa token nhanh:</h6>
                        <button class="btn btn-danger" id="deleteTokenBtn">
                            <i class="fas fa-trash"></i> Xóa token hiện tại
                        </button>
                        <small class="text-muted ms-2">Sau khi xóa, bạn sẽ cần authenticate lại</small>
                    </div>
                </div>
            </div>
        </div>
</div>

<script>
let storageInfo = null;
const oauthAppAccount = '<?php echo GOOGLE_ACCOUNT_EMAIL; ?>';
const clientIdPreview = '<?php echo substr(GDRIVE_CLIENT_ID, 0, 20); ?>';

// Load storage info
async function loadStorageInfo() {
    try {
        const response = await fetch('<?php echo APP_URL; ?>/api/drive-storage.php');
        const result = await response.json();
        
        if (result.success && result.data.success) {
            storageInfo = result.data;
            displayStorageInfo(storageInfo);
        } else {
            displayNotAuthenticated();
        }
    } catch (error) {
        console.error('Error loading storage info:', error);
        displayError(error.message);
    }
}

function displayStorageInfo(data) {
    const container = document.getElementById('accountInfoContainer');
    
    const html = `
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> Account lưu trữ file hiện tại</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <h4 class="mb-1">${data.user_name || 'N/A'}</h4>
                            <p class="text-muted mb-0"><i class="fas fa-envelope"></i> ${data.user_email || 'N/A'}</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Dung lượng lưu trữ:</h6>
                    <div class="progress mb-2" style="height: 25px;">
                        <div class="progress-bar ${data.used_percent > 80 ? 'bg-danger' : 'bg-primary'}" 
                             role="progressbar" 
                             style="width: ${data.used_percent}%">
                            ${data.used_percent}%
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span><strong>Đã dùng:</strong> ${data.usage_formatted}</span>
                        <span><strong>Tổng:</strong> ${data.limit_formatted}</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">
                        <small>Còn trống: ${data.available_formatted}</small>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Cấu hình OAuth App</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">OAuth App Account:</th>
                            <td><code>${oauthAppAccount}</code></td>
                        </tr>
                        <tr>
                            <th>Client ID:</th>
                            <td><code>${clientIdPreview}...</code></td>
                        </tr>
                        <tr>
                            <th>Storage Account:</th>
                            <td><strong class="text-success">${data.user_email}</strong></td>
                        </tr>
                        <tr>
                            <th>Trạng thái:</th>
                            <td><span class="badge bg-success">Đã kết nối</span></td>
                        </tr>
                    </table>
                    
                    <div class="alert alert-light mt-3 mb-0">
                        <small>
                            <strong>Giải thích:</strong><br>
                            - <strong>OAuth App Account:</strong> Account tạo OAuth credentials<br>
                            - <strong>Storage Account:</strong> Account thực tế lưu file (có thể khác)
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    document.getElementById('reAuthInstructions').style.display = 'block';
}

function displayNotAuthenticated() {
    const container = document.getElementById('accountInfoContainer');
    
    container.innerHTML = `
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-body text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                    <h4>Chưa kết nối Google Drive</h4>
                    <p class="text-muted">Chưa có account nào được xác thực để lưu trữ file</p>
                    <a href="<?php echo APP_URL; ?>/api/google-auth.php" class="btn btn-primary btn-lg mt-3">
                        <i class="fab fa-google"></i> Kết nối Google Drive
                    </a>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('reAuthInstructions').style.display = 'block';
}

function displayError(message) {
    const container = document.getElementById('accountInfoContainer');
    container.innerHTML = `
        <div class="col-12">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Lỗi: ${message}
            </div>
        </div>
    `;
}

// Delete token
document.addEventListener('DOMContentLoaded', function() {
    loadStorageInfo();
    
    document.getElementById('deleteTokenBtn').addEventListener('click', async function() {
        if (!confirm('Bạn có chắc muốn xóa token hiện tại? Bạn sẽ cần authenticate lại.')) {
            return;
        }
        
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xóa...';
        
        try {
            const response = await fetch('<?php echo APP_URL; ?>/api/google-callback.php?action=delete_token', {
                method: 'POST'
            });
            const result = await response.json();
            
            if (result.success) {
                alert('Đã xóa token thành công! Bạn sẽ được chuyển đến trang xác thực.');
                window.location.href = '<?php echo APP_URL; ?>/api/google-auth.php';
            } else {
                alert('Lỗi: ' + result.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash"></i> Xóa token hiện tại';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Có lỗi xảy ra: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Xóa token hiện tại';
        }
    });
});
</script>

<?php include APP_ROOT . '/views/includes/footer.php'; ?>
