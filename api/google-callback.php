<?php
/**
 * Google OAuth2 Callback Handler
 * This file handles the redirect from Google after authentication
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/includes/Helper.php';

// Handle delete token request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'delete_token') {
    header('Content-Type: application/json');
    
    try {
        $tokenFile = APP_ROOT . '/config/gdrive_token.json';
        
        if (file_exists($tokenFile)) {
            unlink($tokenFile);
            Helper::jsonResponse(['success' => true, 'message' => 'Token deleted successfully']);
        } else {
            Helper::jsonResponse(['success' => false, 'message' => 'Token file not found']);
        }
    } catch (Exception $e) {
        Helper::jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
    exit;
}

try {
    require_once APP_ROOT . '/vendor/autoload.php';
    
    $client = new Google_Client();
    $client->setClientId(GDRIVE_CLIENT_ID);
    $client->setClientSecret(GDRIVE_CLIENT_SECRET);
    $client->setRedirectUri(GDRIVE_REDIRECT_URI);
    
    if (isset($_GET['code'])) {
        // Exchange authorization code for access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            throw new Exception($token['error_description']);
        }
        
        // Save token to file
        $tokenFile = APP_ROOT . '/config/gdrive_token.json';
        file_put_contents($tokenFile, json_encode($token));
        
        // Success page
        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Authentication Successful</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center p-5">
                                <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                                <h2 class="mb-3">Xác thực thành công!</h2>
                                <p class="text-muted mb-4">
                                    Google Drive đã được kết nối với ứng dụng.<br>
                                    Token đã được lưu an toàn.
                                </p>
                                <a href="<?php echo APP_URL; ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-arrow-right me-2"></i>Đi đến ứng dụng
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        throw new Exception('No authorization code received');
    }
    
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Authentication Error</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-danger">
                        <div class="card-body text-center p-5">
                            <i class="fas fa-exclamation-triangle fa-5x text-danger mb-4"></i>
                            <h2 class="mb-3">Xác thực thất bại!</h2>
                            <p class="text-danger mb-4">
                                <?php echo htmlspecialchars($e->getMessage()); ?>
                            </p>
                            <a href="<?php echo APP_URL; ?>/api/google-auth.php" class="btn btn-primary">
                                <i class="fas fa-redo me-2"></i>Thử lại
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
