# Tính năng: Hiển thị dung lượng Google Drive

## Mô tả
Hiển thị thông tin dung lượng Google Drive trực tiếp trên Dashboard, bao gồm:
- Tổng dung lượng
- Đã sử dụng
- Còn trống
- Phân tích chi tiết (trong Drive, thùng rác)
- Progress bar với màu cảnh báo

## Yêu cầu

### Google API Scopes
Tính năng này yêu cầu 2 scopes:
1. `drive.file` - Quản lý files (đã có sẵn)
2. `drive.metadata.readonly` - Đọc thông tin metadata (MỚI THÊM)

### Cần làm gì?

#### Lần đầu tiên hoặc khi chưa kết nối:
1. Vào: `/views/dashboard/reauthorize-drive.php`
2. Click nút "Kết nối với Google Drive"
3. Đăng nhập tài khoản Google
4. Cấp quyền cho ứng dụng
5. Quay lại Dashboard để xem thông tin

#### Nếu đã kết nối trước đây:
- Cần **kết nối lại** vì scope đã thay đổi
- Xóa file `/config/gdrive_token.json` (hoặc vào trang reauthorize)
- Làm theo các bước ở trên

## Files liên quan

### Backend
- `includes/GoogleDriveAPI.php` - Thêm method `getStorageQuota()`
- `api/drive-storage.php` - API endpoint mới
- `config/config.php` - Cập nhật GDRIVE_SCOPE

### Frontend
- `views/dashboard/index.php` - Widget hiển thị dung lượng
- `views/dashboard/reauthorize-drive.php` - Trang kết nối lại

### Testing
- `test-storage.php` - File test debug (root folder)

## Cách test

### 1. Test API trực tiếp:
```
Truy cập: /test-storage.php
```

### 2. Test trên Dashboard:
```
1. Login vào hệ thống
2. Vào Dashboard
3. Scroll xuống phần "Dung lượng Google Drive"
4. Mở Console (F12) để xem log
```

### 3. Kiểm tra lỗi:
- Nếu báo "Chưa kết nối": Click link để kết nối lại
- Nếu báo "Lỗi kết nối": Kiểm tra Console log
- Nếu báo "Insufficient scope": Cần re-authorize

## Troubleshooting

### Lỗi: "Chưa xác thực với Google Drive"
**Nguyên nhân:** Chưa có access token
**Giải pháp:** Vào `/views/dashboard/reauthorize-drive.php` để kết nối

### Lỗi: "Insufficient authentication scopes"
**Nguyên nhân:** Token cũ không có scope mới
**Giải pháp:** 
1. Xóa `/config/gdrive_token.json`
2. Vào `/views/dashboard/reauthorize-drive.php`
3. Authorize lại

### Lỗi: "Invalid JSON response" hoặc "Lỗi kết nối server"
**Nguyên nhân:** PHP error hoặc vendor chưa cài
**Giải pháp:**
1. Kiểm tra PHP error log
2. Chạy `composer install` nếu chưa có vendor
3. Kiểm tra `/test-storage.php` để debug

### Widget hiển thị "Đang tải..." mãi không load
**Nguyên nhân:** JavaScript error hoặc API không response
**Giải pháp:**
1. Mở Console (F12) xem lỗi
2. Kiểm tra Network tab xem API có được gọi không
3. Test trực tiếp: `/api/drive-storage.php`

## Cấu trúc Response

### Success Response:
```json
{
  "success": true,
  "message": "Lấy thông tin dung lượng thành công",
  "data": {
    "success": true,
    "limit": 16106127360,
    "limit_formatted": "15 GB",
    "usage": 8053063680,
    "usage_formatted": "7.5 GB",
    "usage_in_drive": 7000000000,
    "usage_in_drive_formatted": "6.52 GB",
    "usage_in_trash": 1053063680,
    "usage_in_trash_formatted": "1 GB",
    "available": 8053063680,
    "available_formatted": "7.5 GB",
    "used_percent": 50,
    "user_email": "user@gmail.com",
    "user_name": "User Name"
  }
}
```

### Error Response:
```json
{
  "success": false,
  "message": "Chưa kết nối với Google Drive. Vui lòng upload file để kết nối.",
  "data": {
    "success": false,
    "authenticated": false
  }
}
```

## Security Notes
- App chỉ có quyền đọc metadata (read-only)
- Không thể sửa/xóa files của user
- Chỉ quản lý files do chính app tạo ra (scope drive.file)
- Token được lưu local tại server (/config/gdrive_token.json)

## Todo / Future Enhancements
- [ ] Cache storage quota (refresh mỗi 5 phút)
- [ ] Hiển thị biểu đồ xu hướng sử dụng
- [ ] Cảnh báo khi sắp hết dung lượng (>90%)
- [ ] Export báo cáo sử dụng dung lượng
- [ ] So sánh dung lượng app sử dụng vs tổng Drive

## Support
Nếu có vấn đề, kiểm tra:
1. PHP error log: `/var/log/php_errors.log` hoặc server error log
2. Browser console: F12 > Console tab
3. Test file: `/test-storage.php`
4. API direct: `/api/drive-storage.php`
