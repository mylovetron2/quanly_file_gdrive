# Google Drive File Manager

Hệ thống quản lý file upload lên Google Drive với phân quyền người dùng, được xây dựng bằng PHP thuần và MySQL.

## 🚀 Tính năng

### Quản lý File
- ✅ Upload file lên Google Drive
- ✅ Download file từ Google Drive
- ✅ Xóa file trên Google Drive
- ✅ Xem danh sách file với phân trang
- ✅ Tìm kiếm file (Full-text search)
- ✅ Hiển thị metadata file (tên, kích thước, loại, ngày tải)
- ✅ Đếm số lượt download

### Quản lý Thư mục
- ✅ Tạo thư mục trên Google Drive
- ✅ Xóa thư mục
- ✅ Cấu trúc thư mục phân cấp (parent-child)
- ✅ Breadcrumb navigation

### Phân quyền & Bảo mật
- ✅ Hệ thống đăng nhập/đăng xuất
- ✅ Phân quyền dựa trên Role (Super Admin, Admin, Manager, Editor, Viewer)
- ✅ Phân quyền chi tiết cho từng user
- ✅ Hỗ trợ cấp/thu hồi quyền động (upload, download, delete, manage folders, etc.)
- ✅ Session management
- ✅ Password hashing (bcrypt)

### Lịch sử & Giám sát
- ✅ Activity logs (lịch sử hoạt động)
- ✅ Tracking uploads, downloads, deletes
- ✅ Lưu IP address và User Agent

### Giao diện
- ✅ Responsive design (Bootstrap 5)
- ✅ Dashboard với thống kê
- ✅ File icons theo loại file
- ✅ Upload progress bar
- ✅ Breadcrumb navigation
- ✅ Modal dialogs

## 📋 Yêu cầu hệ thống

- PHP >= 7.4
- MySQL >= 5.7 hoặc MariaDB >= 10.2
- Apache/Nginx web server
- Composer (để cài Google API PHP Client)
- Google Drive API credentials

## 🔧 Cài đặt

### Bước 1: Clone/Download project

```bash
git clone <repository-url>
# hoặc download và giải nén vào thư mục web server (htdocs, www, etc.)
```

### Bước 2: Cài đặt Google API PHP Client

```bash
cd gdrive-manager
composer require google/apiclient:^2.15
```

Nếu chưa có Composer, tải tại: https://getcomposer.org/download/

### Bước 3: Tạo Google Drive API Credentials

1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới hoặc chọn project có sẵn
3. Enable Google Drive API:
   - Menu > APIs & Services > Library
   - Tìm "Google Drive API" và click Enable
4. Tạo OAuth 2.0 credentials:
   - Menu > APIs & Services > Credentials
   - Click "Create Credentials" > "OAuth client ID"
   - Chọn "Web application"
   - Thêm Authorized redirect URIs: `http://localhost/gdrive-manager/api/google-callback.php`
   - Click "Create" và download credentials JSON

5. Lưu lại:
   - Client ID
   - Client Secret

### Bước 4: Cấu hình Database

1. Tạo database MySQL:

```sql
CREATE DATABASE gdrive_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import schema:

```bash
mysql -u root -p gdrive_manager < database.sql
```

### Bước 5: Cấu hình ứng dụng

Mở file `config/config.php` và thay đổi các thông tin sau:

```php
// Application URL
define('APP_URL', 'http://localhost/gdrive-manager');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gdrive_manager');
define('DB_USER', 'root');
define('DB_PASS', ''); // Mật khẩu MySQL của bạn

// Google Drive API Configuration
define('GDRIVE_CLIENT_ID', 'YOUR_CLIENT_ID_HERE');
define('GDRIVE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET_HERE');
define('GDRIVE_REDIRECT_URI', APP_URL . '/api/google-callback.php');

// Security - Đổi thành chuỗi ngẫu nhiên 32 ký tự
define('ENCRYPTION_KEY', 'CHANGE_THIS_TO_RANDOM_STRING_32_CHARS');
```

### Bước 6: Cấu hình quyền thư mục

```bash
chmod -R 755 gdrive-manager
chmod -R 777 gdrive-manager/logs
chmod -R 777 gdrive-manager/uploads
```

### Bước 7: Xác thực Google Drive

1. Truy cập: `http://localhost/gdrive-manager/api/google-auth.php`
2. Đăng nhập với tài khoản Google: `mystore2018myapp.gmail.com`
3. Cho phép ứng dụng truy cập Google Drive
4. Sau khi xác thực thành công, token sẽ được lưu tại `config/gdrive_token.json`

> **Lưu ý**: File này cần được tạo thêm - tạo file `api/google-auth.php`:

## 📝 Sử dụng

### Đăng nhập

- URL: `http://localhost/gdrive-manager`
- Tài khoản mặc định:
  - Username: `admin`
  - Password: `admin123`

### Dashboard

Sau khi đăng nhập, bạn sẽ thấy:
- Thống kê tổng quan (files, folders, dung lượng, users)
- Files gần đây
- Hoạt động gần đây
- Quick actions

### Upload File

1. Click "Upload File" trên dashboard hoặc toolbar
2. Chọn file từ máy tính
3. Chọn thư mục đích (tùy chọn)
4. Nhập mô tả (tùy chọn)
5. Click "Upload File"

**Giới hạn**:
- Kích thước tối đa: 100MB (có thể thay đổi trong `config.php`)
- Loại file cho phép: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip, rar, mp4, mp3

### Quản lý Files

- Xem danh sách: Menu > Files
- Download: Click icon download
- Delete: Click icon trash (yêu cầu quyền `file.delete`)
- Search: Sử dụng search box trên navbar

### Quản lý Thư mục

- Tạo thư mục: Click "Tạo Thư mục" > Nhập tên
- Xóa thư mục: Click icon trash trên thư mục (chỉ khi rỗng)
- Navigate: Click vào tên thư mục

### Phân quyền (Admin only)

1. Menu > Admin > Quản lý người dùng
2. Chọn user > Click "Phân quyền"
3. Chọn các quyền muốn cấp:
   - `file.upload` - Upload files
   - `file.download` - Download files
   - `file.delete` - Delete files
   - `file.view` - View file list
   - `file.edit` - Edit file info
   - `file.share` - Share files
   - `folder.create` - Create folders
   - `folder.delete` - Delete folders
   - `folder.manage` - Manage folders
   - `user.*` - User management
   - `system.*` - System settings

## 🗂️ Cấu trúc thư mục

```
gdrive-manager/
├── api/                    # API endpoints
│   ├── file-upload.php
│   ├── file-download.php
│   ├── file-delete.php
│   ├── folder-create.php
│   ├── folder-delete.php
│   ├── logout.php
│   └── google-auth.php     # (cần tạo thêm)
├── assets/                 # Static files
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── config/                 # Configuration files
│   ├── config.php          # Main config
│   ├── database.php        # Database class
│   └── gdrive_token.json   # Google token (auto-generated)
├── includes/               # PHP classes
│   ├── Auth.php
│   ├── Permission.php
│   ├── GoogleDriveAPI.php
│   ├── FileManager.php
│   ├── FolderManager.php
│   └── Helper.php
├── logs/                   # Application logs
├── uploads/                # Temporary upload directory
│   └── temp/
├── views/                  # View files
│   ├── admin/              # Admin pages
│   ├── auth/               # Authentication pages
│   │   └── login.php
│   ├── dashboard/          # Dashboard pages
│   │   ├── index.php
│   │   ├── files.php
│   │   └── upload.php
│   └── includes/           # Shared view components
│       ├── header.php
│       ├── footer.php
│       └── navbar.php
├── vendor/                 # Composer dependencies
├── database.sql            # Database schema
├── index.php               # Entry point
├── composer.json
└── README.md
```

## 🔐 Bảo mật

### Đã áp dụng:
- Password hashing với bcrypt
- Prepared statements (SQL injection prevention)
- Input sanitization (XSS prevention)
- Session management
- CSRF token support (trong Helper class)
- Permission-based access control
- Activity logging

### Khuyến nghị:
- Đổi mật khẩu admin mặc định ngay sau khi cài đặt
- Sử dụng HTTPS trong production
- Backup database định kỳ
- Giới hạn số lần đăng nhập thất bại
- Enable CSRF protection cho tất cả forms

## 👥 Roles & Permissions

### Roles mặc định:

1. **Super Admin** (ID: 1)
   - Toàn quyền hệ thống
   - Không thể bị xóa

2. **Admin** (ID: 2)
   - Quản lý files, folders, users
   - Xem logs
   - Phân quyền users

3. **Manager** (ID: 3)
   - Quản lý files và folders
   - Xem danh sách users

4. **Editor** (ID: 4)
   - Upload, edit, delete files
   - Tạo folders

5. **Viewer** (ID: 5)
   - Chỉ xem và download files

### Thêm user mới:

```sql
INSERT INTO users (username, email, password, full_name, role_id, status) 
VALUES (
    'newuser', 
    'newuser@example.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'New User Name',
    5, -- Viewer role
    'active'
);
```

## 🐛 Troubleshooting

### Lỗi: "Failed to initialize Google Drive API"
- Kiểm tra Google API credentials
- Đảm bảo đã enable Google Drive API
- Kiểm tra redirect URI trong Google Console

### Lỗi: "Database connection failed"
- Kiểm tra thông tin database trong `config.php`
- Đảm bảo MySQL service đang chạy
- Kiểm tra user có quyền truy cập database

### Lỗi: "File size exceeds limit"
- Tăng `upload_max_filesize` và `post_max_size` trong `php.ini`
- Restart web server sau khi thay đổi

### lỗi: "Permission denied"
- Kiểm tra quyền thư mục `logs/` và `uploads/`
- Run: `chmod -R 777 logs uploads`

### Token hết hạn
- Xóa file `config/gdrive_token.json`
- Truy cập lại `api/google-auth.php` để xác thực mới

## 📊 Database Schema

### Các bảng chính:
- `users` - Người dùng
- `roles` - Vai trò
- `permissions` - Quyền
- `role_permissions` - Quyền của role
- `user_permissions` - Quyền riêng của user
- `files` - Metadata file
- `folders` - Thư mục
- `shared_links` - Links chia sẻ
- `activity_logs` - Lịch sử hoạt động
- `sessions` - Session management

## 📧 Thông tin liên hệ

- Google Account: mystore2018myapp.gmail.com
- Password: cntt2019 (chỉ dùng cho testing)

## 📜 License

MIT License - Free to use and modify

## 🙏 Credits

- [Bootstrap 5](https://getbootstrap.com/)
- [Font Awesome](https://fontawesome.com/)
- [Google API PHP Client](https://github.com/googleapis/google-api-php-client)
- [jQuery](https://jquery.com/)

---

**Developed with ❤️ using PHP & MySQL**

**Phiên bản**: 1.0.0  
**Ngày phát hành**: 16/04/2026
