# 🚀 Hướng dẫn cài đặt nhanh

## 1. Yêu cầu

- PHP >= 7.4
- MySQL >= 5.7
- Composer
- Apache/Nginx với mod_rewrite

## 2. Cài đặt

### Bước 1: Tải code

```bash
# Clone hoặc giải nén vào thư mục web server
cd D:\ProjectGoogleDrive\gdrive-manager
```

### Bước 2: Cài đặt dependencies

```bash
composer install
# hoặc
composer require google/apiclient:^2.15
```

### Bước 3: Tạo database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE gdrive_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gdrive_manager;
source database.sql;
```

### Bước 4: Cấu hình

1. Mở `config/config.php`
2. Cập nhật:
   - `APP_URL`
   - `DB_*` (database credentials)
   - `GDRIVE_CLIENT_ID`
   - `GDRIVE_CLIENT_SECRET`
   - `ENCRYPTION_KEY`

### Bước 5: Google Drive API

1. Vào https://console.cloud.google.com/
2. Tạo project mới
3. Enable "Google Drive API"
4. Tạo OAuth 2.0 credentials (Web application)
5. Authorized redirect URIs: `http://localhost/gdrive-manager/api/google-callback.php`
6. Copy Client ID và Client Secret vào `config.php`

### Bước 6: Xác thực Google

Truy cập: `http://localhost/gdrive-manager/api/google-auth.php`

Đăng nhập với: `mystore2018myapp.gmail.com`

### Bước 7: Đăng nhập

URL: `http://localhost/gdrive-manager`

```
Username: admin
Password: admin123
```

## 3. Kiểm tra

✅ Database đã import  
✅ Composer dependencies đã cài  
✅ Config đã cập nhật  
✅ Google Drive authenticated  
✅ Quyền thư mục logs/ và uploads/  

## 4. Troubleshooting

### Lỗi upload:
```bash
# Tăng giới hạn trong php.ini
upload_max_filesize = 100M
post_max_size = 100M
```

### Lỗi quyền:
```bash
chmod -R 777 logs uploads
```

### Token hết hạn:
```bash
rm config/gdrive_token.json
# Xác thực lại tại /api/google-auth.php
```

## 5. Tài khoản test

**Google Account:**
- Email: mystore2018myapp.gmail.com
- Pass: cntt2019

**Admin:**
- Username: admin
- Password: admin123

## 6. Tính năng

✅ Upload/Download/Delete files  
✅ Folder management  
✅ User permissions  
✅ Activity logs  
✅ Search files  
✅ Role-based access control  

## 7. Liên hệ hỗ trợ

Xem chi tiết trong [README.md](README.md)

---

**Chúc bạn sử dụng thành công!** 🎉
