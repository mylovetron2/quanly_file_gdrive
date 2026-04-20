# Hướng dẫn chuyển đổi Account lưu trữ Google Drive

## Tình huống hiện tại

Ứng dụng hiện đang lưu file vào Google Drive của account **mystore2018myapp@gmail.com** (account cài đặt OAuth App).

## Yêu cầu

Muốn chuyển sang account khác để lưu file, nhưng **vẫn giữ nguyên cấu hình OAuth App** trong mystore2018myapp@gmail.com.

## Giải pháp

### Phân biệt 2 loại account:

1. **OAuth App Account** (mystore2018myapp@gmail.com)
   - Là account đăng ký ứng dụng OAuth trên Google Cloud Console
   - Cấu hình Client ID, Client Secret
   - **KHÔNG THAY ĐỔI**

2. **Storage Account** (có thể là account khác)
   - Là account thực sự lưu trữ file trên Google Drive
   - **CÓ THỂ THAY ĐỔI** bằng cách re-authenticate

## Các bước thực hiện

### Bước 1: Truy cập trang quản lý

1. Đăng nhập với quyền Admin
2. Vào menu: **Admin → Google Drive Account**
3. Xem account hiện tại đang lưu file

### Bước 2: Chuẩn bị account mới

1. Tạo hoặc sử dụng account Google khác (ví dụ: **newstorage@gmail.com**)
2. Đảm bảo account có đủ dung lượng trống

### Bước 3: Chuyển đổi account

#### Cách 1: Dùng giao diện (Khuyến nghị)

1. Trong trang **Admin → Google Drive Account**
2. Click nút **"Xóa token hiện tại"**
3. Xác nhận xóa
4. Hệ thống sẽ chuyển đến trang xác thực
5. Click **"Login with Google"**
6. **QUAN TRỌNG**: Chọn account mới bạn muốn dùng (newstorage@gmail.com)
7. Cho phép các quyền truy cập
8. Hoàn tất!

#### Cách 2: Xóa thủ công

1. Xóa file: `config/gdrive_token.json`
2. Truy cập: `https://your-domain.com/api/google-auth.php`
3. Click "Login with Google"
4. Chọn account mới (newstorage@gmail.com)
5. Hoàn tất!

### Bước 4: Kiểm tra

1. Quay lại trang **Admin → Google Drive Account**
2. Xác nhận **Storage Account** đã chuyển sang account mới
3. Thử upload 1 file test
4. Kiểm tra file có xuất hiện trong Drive của account mới

## Lưu ý quan trọng

### ✅ Điều được giữ nguyên:
- OAuth App vẫn trong mystore2018myapp@gmail.com
- Client ID, Client Secret không đổi
- Người dùng đăng nhập app vẫn bình thường
- Quyền và cấu hình không thay đổi

### ⚠️ Điều cần lưu ý:
- **File cũ vẫn nằm trong account cũ** (mystore2018myapp@gmail.com)
- Nếu muốn di chuyển file cũ sang account mới:
  - Phải share folder từ account cũ sang account mới
  - Hoặc copy/move thủ công trên Google Drive
- **File mới sẽ lưu vào account mới** (newstorage@gmail.com)

### 🔒 Bảo mật:
- Token được lưu trong file `config/gdrive_token.json`
- Đảm bảo file này không public
- Backup token trước khi xóa (nếu cần)

## Quản lý file cũ

### Option 1: Di chuyển file (Khuyến nghị)

```
1. Đăng nhập vào mystore2018myapp@gmail.com
2. Vào Google Drive
3. Chọn tất cả file/folder cần chuyển
4. Right-click → Share → Add newstorage@gmail.com (Editor)
5. Đăng nhập newstorage@gmail.com
6. Vào "Shared with me"
7. Select file → Right-click → "Make a copy" hoặc "Move"
```

### Option 2: Giữ nguyên file cũ

- File cũ vẫn trong mystore2018myapp@gmail.com
- File mới trong newstorage@gmail.com
- Cần quản lý 2 account riêng

## Kiểm tra thông tin

### Xem account hiện tại:
```
Admin → Google Drive Account
```

Hiển thị:
- **Storage Account Email**: Account đang lưu file
- **Storage Used**: Dung lượng đã dùng
- **OAuth App Account**: Account cài đặt (không đổi)

### Xem trong Dashboard:
- Mục "Drive Storage" hiển thị dung lượng của Storage Account

## Troubleshooting

### Lỗi: "Token expired"
- Chỉ cần re-authenticate lại
- Token tự động refresh nếu có refresh_token

### Lỗi: "Insufficient permissions"
- Re-authenticate và cho đủ quyền:
  - Google Drive API
  - Google Drive File API

### File upload không thành công
- Kiểm tra dung lượng Drive còn trống
- Kiểm tra quyền của Storage Account
- Xem log lỗi trong `logs/` folder

## Tóm tắt

```
TRƯỚC:
- OAuth App: mystore2018myapp@gmail.com
- Storage: mystore2018myapp@gmail.com (same account)

SAU:
- OAuth App: mystore2018myapp@gmail.com (không đổi)
- Storage: newstorage@gmail.com (account mới)
```

## Hỗ trợ

Nếu cần hỗ trợ, check:
1. File log: `logs/` folder
2. Browser console khi upload
3. Trang Admin → Google Drive Account
