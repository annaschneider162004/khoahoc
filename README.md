# musicofeveryone

Website học nhạc online viết bằng PHP procedural + MySQL, giao diện Tailwind CSS CDN, phù hợp triển khai trên cPanel.

## Tính năng chính
- Trang chủ hiện đại theo tông tím trắng
- Đăng ký / đăng nhập / ghi nhớ đăng nhập
- Dashboard học viên, upload video, thư viện video, chat, lịch học, hồ sơ, bảng xếp hạng
- Khu vực quản trị: học viên, giáo viên, khóa học, video, chấm điểm, chứng chỉ, livestream, cộng đồng, thông báo, cài đặt
- Sinh PDF chứng chỉ bằng lớp FPDF tối giản đi kèm

## Hướng dẫn triển khai trên cPanel
1. **Tạo MySQL database** trong cPanel và tạo MySQL user.
2. **Import `musicofeveryone.sql`** bằng phpMyAdmin vào database vừa tạo.
3. **Sửa `config/db.php`** và điền đúng `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
4. **Upload toàn bộ source code** vào thư mục `public_html` hoặc thư mục domain/subdomain tương ứng.
5. **Cấp quyền ghi** cho thư mục `uploads/` và các thư mục con (`uploads/videos`, `uploads/avatars`, `uploads/certificates`, `uploads/community`).
6. **Truy cập website** và đăng nhập bằng tài khoản quản trị mẫu:
   - Email: `admin@musicofeveryone.vn`
   - Mật khẩu: `admin123`

## Tài khoản mẫu khác
- Học viên:
  - `minhanh@musicofeveryone.vn` / `hocvien123`
  - `lananh@musicofeveryone.vn` / `hocvien123`
  - `tuananh@musicofeveryone.vn` / `hocvien123`
- Giáo viên:
  - `huong@musicofeveryone.vn` / `giaovien123`
  - `thayminh@musicofeveryone.vn` / `giaovien123`

## Ghi chú
- OAuth Google/Facebook hiện là giao diện minh họa (UI only).
- Các video mẫu trong SQL đang dùng đường dẫn demo; sau khi triển khai, học viên có thể tải video thật lên thư mục `uploads/videos/`.
- Thư mục `uploads/` đã có `.htaccess` để chặn thực thi file PHP.
