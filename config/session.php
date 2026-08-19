<?php
declare(strict_types=1);

/**
 * Khởi tạo session một cách an toàn cho môi trường shared hosting (cPanel).
 *
 * Trên một số gói hosting, session.save_path mặc định của PHP không tồn tại
 * hoặc không có quyền ghi, khiến session KHÔNG được lưu giữa các request.
 * Hậu quả: mỗi lần tải trang, csrf_token bị sinh lại khác nhau -> khi submit
 * form sẽ luôn báo "Phiên làm việc đã hết hạn...".
 *
 * Đoạn code dưới đây tự tạo một thư mục session RIÊNG bên trong project
 * (storage/sessions) và ép PHP dùng thư mục đó, để không phụ thuộc vào
 * cấu hình session mặc định của host.
 */

$sessionDirectory = dirname(__DIR__) . '/storage/sessions';

if (!is_dir($sessionDirectory)) {
    @mkdir($sessionDirectory, 0775, true);
}

if (is_dir($sessionDirectory) && is_writable($sessionDirectory)) {
    session_save_path($sessionDirectory);
}

// Cấu hình cookie session tương thích cả HTTP lẫn HTTPS, tránh bị trình
// duyệt từ chối cookie khi thuộc tính "secure" không khớp giao thức thực tế.
$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
