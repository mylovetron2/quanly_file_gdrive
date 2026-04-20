<?php
/**
 * Google Drive Authentication
 * Handles OAuth2 authentication flow
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/config.php';

try {
    require_once APP_ROOT . '/vendor/autoload.php';
    
    $client = new Google_Client();
    $client->setClientId(GDRIVE_CLIENT_ID);
    $client->setClientSecret(GDRIVE_CLIENT_SECRET);
    $client->setRedirectUri(GDRIVE_REDIRECT_URI);
    $client->setScopes([GDRIVE_SCOPE]);
    $client->setAccessType(GDRIVE_ACCESS_TYPE);
    $client->setPrompt('consent');
    
    // Check if we have a code parameter (callback from Google)
    if (isset($_GET['code'])) {
        // Exchange authorization code for access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            die("Error: " . $token['error_description']);
        }
        
        // Save token to file
        $tokenFile = APP_ROOT . '/config/gdrive_token.json';
        file_put_contents($tokenFile, json_encode($token));
        
        echo "<h2>✓ Authentication Successful!</h2>";
        echo "<p>Token has been saved. You can now use the Quản lý file XSCTBDVL.</p>";
        echo "<p><a href='" . APP_URL . "'>Go to Application</a></p>";
        
    } else {
        // Generate authorization URL
        $authUrl = $client->createAuthUrl();
        
        echo "<!DOCTYPE html>";
        echo "<html lang='vi'>";
        echo "<head>";
        echo "<meta charset='UTF-8'>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        echo "<title>Google Drive Authentication</title>";
        echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
        echo "</head>";
        echo "<body class='bg-light'>";
        echo "<div class='container mt-5'>";
        echo "<div class='row justify-content-center'>";
        echo "<div class='col-md-6'>";
        echo "<div class='card'>";
        echo "<div class='card-body text-center p-5'>";
        echo "<h1 class='mb-4'><i class='fab fa-google-drive fa-3x text-primary'></i></h1>";
        echo "<h3>Google Drive Authentication</h3>";
        echo "<p class='text-muted'>Xác thực với Google Drive để lưu trữ file</p>";
        echo "<div class='alert alert-info mt-3'>";
        echo "<strong><i class='fas fa-info-circle'></i> Lưu ý quan trọng:</strong>";
        echo "<ul class='mb-0 mt-2 text-start'>";
        echo "<li><strong>OAuth App Setup:</strong> " . GOOGLE_ACCOUNT_EMAIL . " (không đổi)</li>";
        echo "<li><strong>File Storage Account:</strong> Chọn account khi click Login (có thể khác)</li>";
        echo "</ul>";
        echo "</div>";
        echo "<a href='" . $authUrl . "' class='btn btn-primary btn-lg mt-3'>";
        echo "<i class='fab fa-google me-2'></i>Login with Google";
        echo "</a>";
        echo "<p class='small text-muted mt-3'>Khi click Login, hãy chọn <strong>account bạn muốn dùng để lưu file</strong></p>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>";
        echo "</body>";
        echo "</html>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Please make sure:</p>";
    echo "<ul>";
    echo "<li>Composer dependencies are installed: <code>composer require google/apiclient:^2.15</code></li>";
    echo "<li>Google API credentials are configured in config.php</li>";
    echo "<li>Google Drive API is enabled in Google Cloud Console</li>";
    echo "</ul>";
}
?>
