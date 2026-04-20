# 🔄 Chuyển đổi Storage Account - Quick Guide

## Hiện trạng
- **Đăng nhập app**: xxxx@gmail.com (hoặc username/password)
- **OAuth App**: mystore2018myapp@gmail.com
- **Lưu file**: mystore2018myapp@gmail.com ❌

## Mục tiêu
- **Đăng nhập app**: xxxx@gmail.com (không đổi)
- **OAuth App**: mystore2018myapp@gmail.com (không đổi)
- **Lưu file**: account-moi@gmail.com ✅

---

## ⚡ Thực hiện ngay (3 bước)

### Bước 1: Truy cập trang quản lý
```
http://your-domain.com/views/admin/drive-account.php
```
- Đăng nhập với quyền Admin
- Xem account hiện tại đang lưu file

### Bước 2: Xóa token hiện tại
- Click nút **"Xóa token hiện tại"**
- Xác nhận

### Bước 3: Authenticate với account mới
- Hệ thống tự động chuyển đến trang Google Auth
- Click **"Login with Google"**
- **QUAN TRỌNG**: Chọn account mới (account-moi@gmail.com)
- Cho phép quyền truy cập
- Hoàn tất!

---

## 🎯 Kết quả

### Sau khi hoàn tất:
1. File mới upload sẽ lưu vào **account-moi@gmail.com**
2. OAuth App vẫn trong **mystore2018myapp@gmail.com**
3. User vẫn đăng nhập app bình thường

### Kiểm tra:
- Vào: Admin → Google Drive Account
- **Storage Account Email** phải hiển thị: account-moi@gmail.com
- Thử upload 1 file test
- Check trong Google Drive của account-moi@gmail.com

---

## ❓ File cũ đâu rồi?

File cũ **vẫn nằm trong mystore2018myapp@gmail.com**

### Option 1: Di chuyển file
1. Đăng nhập mystore2018myapp@gmail.com (Drive)
2. Share folder/file với account-moi@gmail.com (Editor)
3. Đăng nhập account-moi@gmail.com
4. Vào "Shared with me" → Copy hoặc Move

### Option 2: Giữ nguyên
- File cũ: mystore2018myapp@gmail.com
- File mới: account-moi@gmail.com
- Quản lý riêng

---

## 🔧 Troubleshooting

### Token expired?
→ Re-authenticate lại (Bước 2-3)

### File upload lỗi?
→ Check dung lượng Drive còn trống

### Không thấy menu Admin?
→ Cần quyền Admin để truy cập

---

## 📚 Chi tiết
Xem: `docs/CHANGE_STORAGE_ACCOUNT.md`
