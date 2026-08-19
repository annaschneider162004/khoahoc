<?php
declare(strict_types=1);

/**
 * Cấu hình kết nối cơ sở dữ liệu cho cPanel.
 *
 * Bước triển khai trên cPanel:
 * 1. Tạo MySQL Database và MySQL User trong cPanel.
 * 2. Gán toàn quyền cho user đối với database.
 * 3. Điền lại 4 hằng số DB_HOST, DB_NAME, DB_USER, DB_PASS bên dưới.
 * 4. Import file musicofeveryone.sql bằng phpMyAdmin.
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', 'musicofeveryone');
}

if (!defined('DB_USER')) {
    define('DB_USER', '');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra config/db.php.');
}
