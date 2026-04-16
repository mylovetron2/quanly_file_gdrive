# ✅ Checklist Setup - Google Drive Manager

Sau khi tải project về, làm theo các bước sau:

## 📋 Bước 1: Chuẩn bị môi trường

- [ ] Đã cài PHP >= 7.4 (kiểm tra: `php -v`)
- [ ] Đã cài MySQL >= 5.7 (kiểm tra: `mysql --version`)
- [ ] Đã cài Composer (kiểm tra: `composer --version`)
- [ ] Đã cài Apache/Nginx với mod_rewrite
- [ ] PHP extensions: `pdo_mysql`, `mbstring`, `json`, `curl`

## 📦 Bước 2: Copy files

- [ ] Copy toàn bộ folder `gdrive-manager` vào thư mục web root
  - Windows XAMPP: `C:\xampp\htdocs\`
  - Windows WAMP: `C:\wamp64\www\`
  - Mac MAMP: `/Applications/MAMP/htdocs/`
  - Linux: `/var/www/html/`

## 🔧 Bước 3: Cài đặt dependencies

```bash
cd gdrive-manager
composer install
```

- [ ] Chạy lệnh trên thành công
- [ ] Folder `vendor/` đã được tạo
- [ ] File `vendor/autoload.php` tồn tại

## 🗄️ Bước 4: Setup database

### Tạo database:
```bash
mysql -u root -p
```

```sql
CREATE DATABASE gdrive_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### Import schema:
```bash
mysql -u root -p gdrive_manager < database.sql
```

### Kiểm tra:
```sql
USE gdrive_manager;
SHOW TABLES;  -- Phải có 11 bảng
SELECT * FROM users;  -- Phải có user admin
```

- [ ] Database `gdrive_manager` đã tạo
- [ ] 11 bảng đã được import
- [ ] User admin tồn tại
- [ ] 5 roles và 18 permissions đã được tạo

## ⚙️ Bước 5: Cấu hình ứng dụng

Mở file `config/config.php` và sửa:

### APP_URL:
```php
define('APP_URL', 'http://localhost/gdrive-manager');
```
- [ ] Đã đổi URL phù hợp

### Database:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gdrive_manager');
define('DB_USER', 'root');
define('DB_PASS', 'YOUR_MYSQL_PASSWORD');
```
- [ ] DB_HOST đúng
- [ ] DB_NAME = 'gdrive_manager'
- [ ] DB_USER đúng
- [ ] DB_PASS đã điền

### Security:
```php
define('ENCRYPTION_KEY', 'abc123xyz789...'); // 32 ký tự ngẫu nhiên
```
- [ ] Đã đổi ENCRYPTION_KEY thành chuỗi ngẫu nhiên

## 🔑 Bước 6: Setup Google Drive API

### A. Tạo Google Cloud Project:
- [ ] Truy cập https://console.cloud.google.com/
- [ ] Tạo project mới (hoặc chọn project có sẵn)
- [ ] Ghi lại Project ID

### B. Enable API:
- [ ] Menu > APIs & Services > Library
- [ ] Tìm "Google Drive API"
- [ ] Click "Enable"

### C. Create Credentials:
- [ ] Menu > APIs & Services > Credentials
- [ ] Click "Create Credentials" > "OAuth client ID"
- [ ] Chọn "Web application"
- [ ] Application name: "Google Drive Manager"
- [ ] Authorized redirect URIs:
  ```
  http://localhost/gdrive-manager/api/google-callback.php
  ```
- [ ] Click "Create"
- [ ] Download JSON (hoặc copy Client ID và Client Secret)

### D. Cập nhật config.php:
```php
define('GDRIVE_CLIENT_ID', 'YOUR_CLIENT_ID_HERE');
define('GDRIVE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET_HERE');
```
- [ ] GDRIVE_CLIENT_ID đã điền
- [ ] GDRIVE_CLIENT_SECRET đã điền

## 📁 Bước 7: Phân quyền thư mục

### Windows:
- [ ] Không cần làm gì (tự động có quyền)

### Linux/Mac:
```bash
chmod -R 755 gdrive-manager
chmod -R 777 gdrive-manager/logs
chmod -R 777 gdrive-manager/uploads
```
- [ ] Đã chạy lệnh trên

## 🔐 Bước 8: Xác thực Google Drive

1. Mở browser và truy cập:
   ```
   http://localhost/gdrive-manager/api/google-auth.php
   ```

2. Click "Login with Google"

3. Đăng nhập với tài khoản:
   ```
   Email: mystore2018myapp.gmail.com
   Password: cntt2019
   ```

4. Cho phép quyền truy cập

5. Kiểm tra file token đã tạo:
   - [ ] File `config/gdrive_token.json` đã tồn tại
   - [ ] File có nội dung JSON với access_token

## 🚀 Bước 9: Kiểm tra hoạt động

### A. Truy cập ứng dụng:
```
http://localhost/gdrive-manager
```
- [ ] Trang login hiển thị đúng

### B. Đăng nhập:
```
Username: admin
Password: admin123
```
- [ ] Đăng nhập thành công
- [ ] Redirect đến dashboard

### C. Test các tính năng:

**Dashboard:**
- [ ] Hiển thị statistics (files, folders, users, storage)
- [ ] Hiển thị recent files
- [ ] Hiển thị recent activity

**Upload file:**
- [ ] Click "Upload File"
- [ ] Chọn file test (< 100MB)
- [ ] Upload thành công
- [ ] File xuất hiện trong danh sách

**Download file:**
- [ ] Click icon download trên file vừa upload
- [ ] File download về máy thành công

**Folder:**
- [ ] Click "Tạo Thư mục"
- [ ] Nhập tên, submit
- [ ] Folder xuất hiện trong list

**Search:**
- [ ] Nhập từ khóa vào search box
- [ ] Kết quả tìm kiếm có file phù hợp

**Admin:**
- [ ] Menu > Admin > Quản lý người dùng
- [ ] Danh sách users hiển thị
- [ ] Có thể xem activity logs

## 🐛 Troubleshooting

### Lỗi "Connection failed":
- [ ] Kiểm tra MySQL service đang chạy
- [ ] Kiểm tra DB credentials trong config.php
- [ ] Test connection: `php -r "new PDO('mysql:host=localhost;dbname=gdrive_manager', 'root', 'password');"`

### Lỗi "Failed to initialize Google Drive API":
- [ ] Kiểm tra vendor/ folder đã có Google API Client
- [ ] Kiểm tra GDRIVE_CLIENT_ID và SECRET đã đúng
- [ ] Kiểm tra đã enable Google Drive API

### Lỗi "Class 'Google_Client' not found":
- [ ] Chạy lại: `composer require google/apiclient:^2.15`
- [ ] Kiểm tra file vendor/autoload.php tồn tại

### Lỗi "File size exceeds limit":
- [ ] Mở php.ini
- [ ] Tăng: `upload_max_filesize = 100M`
- [ ] Tăng: `post_max_size = 100M`
- [ ] Restart Apache/Nginx

### Lỗi "Permission denied" (Linux):
- [ ] `sudo chmod -R 777 gdrive-manager/logs`
- [ ] `sudo chmod -R 777 gdrive-manager/uploads`

### Token hết hạn:
- [ ] Xóa file: `config/gdrive_token.json`
- [ ] Truy cập lại: `/api/google-auth.php`
- [ ] Xác thực lại

## ✨ Bước 10: Bảo mật (Production)

Nếu deploy lên server thật:

- [ ] Đổi mật khẩu admin ngay lập tức
- [ ] Đổi ENCRYPTION_KEY
- [ ] Bật HTTPS
- [ ] Cập nhật APP_URL thành domain thật
- [ ] Cập nhật Authorized redirect URIs trong Google Console
- [ ] Set `DEBUG_MODE = false` trong config.php
- [ ] Set `ENVIRONMENT = 'production'`
- [ ] Giới hạn quyền folder: chmod 755 (không dùng 777)
- [ ] Backup database định kỳ
- [ ] Setup monitoring & logging

## 📞 Cần hỗ trợ?

1. Đọc README.md
2. Đọc INSTALL.md
3. Kiểm tra logs/: `gdrive-manager/logs/app_YYYY-MM-DD.log`
4. Bật DEBUG_MODE trong config.php để xem chi tiết lỗi

## 🎉 Hoàn tất!

Nếu tất cả checkbox đã check ✅, congratulations! 

Project của bạn đã sẵn sàng sử dụng! 🚀

---

**Next Steps:**
- Tạo thêm users
- Upload files test
- Thử các tính năng khác
- Customize theo nhu cầu

**Have fun!** 😊
