<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

function db(): PDO
{
    global $pdo;
    return $pdo;
}

function base_url(string $path = ''): string
{
    return $path;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * CSRF token dựa trên cookie (double-submit cookie pattern).
 *
 * Không phụ thuộc vào PHP session được lưu hay không - một số gói hosting
 * (do open_basedir hoặc quyền hệ thống bị giới hạn) không lưu được session
 * giữa các request, khiến cơ chế CSRF dựa vào $_SESSION luôn báo hết hạn.
 * Bằng cách lưu token vào cookie riêng (không phải cookie session), token
 * sẽ tồn tại ổn định giữa các request bất kể cấu hình session của host.
 */
function csrf_token(): string
{
    if (!empty($_COOKIE['csrf_token']) && is_string($_COOKIE['csrf_token']) && strlen($_COOKIE['csrf_token']) === 64) {
        return $_COOKIE['csrf_token'];
    }

    $token = bin2hex(random_bytes(32));

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    );

    setcookie('csrf_token', $token, [
        'expires' => time() + 60 * 60 * 4,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => false,
        'samesite' => 'Lax',
    ]);

    $_COOKIE['csrf_token'] = $token;

    return $token;
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_or_fail(): void
{
    if (!is_post()) {
        return;
    }

    $formToken = (string) ($_POST['csrf_token'] ?? '');
    $cookieToken = (string) ($_COOKIE['csrf_token'] ?? '');

    if ($formToken === '' || $cookieToken === '' || !hash_equals($cookieToken, $formToken)) {
        http_response_code(419);
        exit('Phiên làm việc đã hết hạn hoặc yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.');
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function render_flashes(): void
{
    foreach (get_flashes() as $flash) {
        $classes = $flash['type'] === 'success'
            ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
            : ($flash['type'] === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-rose-50 border-rose-200 text-rose-700');
        echo '<div class="mb-4 rounded-2xl border px-4 py-3 text-sm shadow-sm ' . e($classes) . '">' . e($flash['message']) . '</div>';
    }
}

function query_all(string $sql, array $params = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function query_one(string $sql, array $params = []): ?array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch();
    return $row ?: null;
}

function execute_query(string $sql, array $params = []): bool
{
    $statement = db()->prepare($sql);
    return $statement->execute($params);
}

function current_user(): ?array
{
    static $user = null;

    if ($user !== null) {
        return $user;
    }

    if (!empty($_SESSION['user_id'])) {
        $user = query_one('SELECT * FROM users WHERE id = ? LIMIT 1', [$_SESSION['user_id']]);
        return $user;
    }

    if (!empty($_COOKIE['remember_token'])) {
        $rememberToken = hash('sha256', $_COOKIE['remember_token']);
        $user = query_one('SELECT * FROM users WHERE remember_token = ? LIMIT 1', [$rememberToken]);
        if ($user) {
            login_user($user, true);
            return $user;
        }
    }

    return null;
}

function login_user(array $user, bool $fromRemember = false): void
{
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_role'] = $user['role'];

    if (!$fromRemember) {
        regenerate_user_session();
    }
}

function regenerate_user_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function logout_user(): void
{
    if (!empty($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function require_login(?string $role = null): void
{
    $user = current_user();
    if (!$user) {
        set_flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('/login.php');
    }

    if ((int) ($user['locked'] ?? 0) === 1) {
        logout_user();
        set_flash('error', 'Tài khoản của bạn đang bị khóa.');
        redirect('/login.php');
    }

    if ($role !== null && $user['role'] !== $role) {
        http_response_code(403);
        exit('Bạn không có quyền truy cập trang này.');
    }
}

function require_admin(): void
{
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        set_flash('error', 'Chỉ quản trị viên mới có thể truy cập.');
        redirect('/admin/login.php');
    }
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function sanitize_text(?string $value): string
{
    return trim((string) $value);
}

function format_datetime_vi(?string $value, string $format = 'd/m/Y H:i'): string
{
    if (!$value) {
        return 'Chưa cập nhật';
    }

    try {
        return (new DateTime($value))->format($format);
    } catch (Throwable $exception) {
        return $value;
    }
}

function fetch_settings(): array
{
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    $rows = query_all('SELECT setting_key, setting_value FROM settings');
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function setting(string $key, string $default = ''): string
{
    $settings = fetch_settings();
    return $settings[$key] ?? $default;
}

function ensure_directory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

function upload_file(string $field, string $relativeDirectory, array $allowedExtensions, array $allowedMimeTypes, int $maxBytes): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Tải tệp thất bại.');
    }

    if ($_FILES[$field]['size'] > $maxBytes) {
        throw new RuntimeException('Tệp vượt quá dung lượng cho phép.');
    }

    $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Định dạng tệp không hợp lệ.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($_FILES[$field]['tmp_name']);
    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        throw new RuntimeException('Loại MIME không được hỗ trợ.');
    }

    $destinationDirectory = dirname(__DIR__) . '/' . trim($relativeDirectory, '/');
    ensure_directory($destinationDirectory);

    $fileName = uniqid('media_', true) . '.' . $extension;
    $destinationPath = $destinationDirectory . '/' . $fileName;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $destinationPath)) {
        throw new RuntimeException('Không thể lưu tệp tải lên.');
    }

    return '/' . trim($relativeDirectory, '/') . '/' . $fileName;
}

function create_remember_token(int $userId): void
{
    $token = bin2hex(random_bytes(32));
    execute_query('UPDATE users SET remember_token = ? WHERE id = ?', [hash('sha256', $token), $userId]);
    setcookie('remember_token', $token, time() + 86400 * 30, '/', '', true, true);
}

function badge_count(int $userId): int
{
    $row = query_one('SELECT COUNT(*) AS total FROM user_badges WHERE user_id = ?', [$userId]);
    return (int) ($row['total'] ?? 0);
}

function notification_count(int $userId): int
{
    $row = query_one('SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]);
    return (int) ($row['total'] ?? 0);
}

function send_notification(?int $userId, string $title, string $message, string $link = '', ?string $targetRole = null): void
{
    execute_query(
        'INSERT INTO notifications (user_id, title, message, link, target_role, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
        [$userId, $title, $message, $link, $targetRole]
    );
}

function send_role_notification(string $role, string $title, string $message, string $link = ''): void
{
    $users = query_all('SELECT id FROM users WHERE role = ? AND locked = 0', [$role]);
    foreach ($users as $user) {
        send_notification((int) $user['id'], $title, $message, $link, $role);
    }
}

function recalculate_rankings(): void
{
    $users = query_all(
        'SELECT u.id, u.points, COUNT(ub.id) AS badges_count FROM users u
         LEFT JOIN user_badges ub ON ub.user_id = u.id
         WHERE u.role = ?
         GROUP BY u.id, u.points',
        ['student']
    );

    execute_query('DELETE FROM rankings');

    foreach ($users as $user) {
        execute_query(
            'INSERT INTO rankings (user_id, course_id, points, badges_count, updated_at) VALUES (?, NULL, ?, ?, NOW())',
            [(int) $user['id'], (int) $user['points'], (int) $user['badges_count']]
        );

        $courseRows = query_all(
            'SELECT e.course_id, ROUND(COALESCE(e.progress, 0) * 10 + COALESCE(AVG(g.score), 0), 0) AS course_points,
                    COUNT(DISTINCT ub.id) AS badges_count
             FROM enrollments e
             LEFT JOIN grades g ON g.student_id = e.user_id
             LEFT JOIN videos v ON v.id = g.video_id AND v.course_id = e.course_id
             LEFT JOIN user_badges ub ON ub.user_id = e.user_id
             WHERE e.user_id = ?
             GROUP BY e.course_id, e.progress',
            [(int) $user['id']]
        );

        foreach ($courseRows as $courseRow) {
            execute_query(
                'INSERT INTO rankings (user_id, course_id, points, badges_count, updated_at) VALUES (?, ?, ?, ?, NOW())',
                [(int) $user['id'], (int) $courseRow['course_id'], (int) $courseRow['course_points'], (int) $courseRow['badges_count']]
            );
        }
    }
}

function youtube_embed_url(string $url): string
{
    if (preg_match('~(?:youtu\.be/|v=)([A-Za-z0-9_-]{6,})~', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    return $url;
}

function build_map_embed(string $fallback = ''): string
{
    $embedUrl = setting('map_embed_url');
    if ($embedUrl !== '') {
        return $embedUrl;
    }

    $lat = setting('map_lat');
    $lng = setting('map_lng');
    if ($lat !== '' && $lng !== '') {
        return 'https://www.google.com/maps?q=' . rawurlencode($lat . ',' . $lng) . '&output=embed';
    }

    return $fallback ?: 'https://www.google.com/maps?q=Hồ%20Chí%20Minh&output=embed';
}

function generate_certificate_pdf(string $studentName, string $courseName, string $issuedDate, string $certificateCode, string $outputPath): void
{
    $pdf = new FPDF();
    $pdf->AddPage('L');
    $pdf->SetFont('Helvetica', 'B', 24);
    $pdf->SetTextColor(109, 40, 217);
    $pdf->Cell(0, 25, 'MUSIC OF EVERYONE', 0, 1, 'C');
    $pdf->SetFont('Helvetica', '', 16);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->Cell(0, 12, 'CHỨNG NHẬN HOÀN THÀNH KHÓA HỌC', 0, 1, 'C');
    $pdf->Ln(8);
    $pdf->SetFont('Helvetica', '', 14);
    $pdf->Cell(0, 10, 'Trân trọng trao cho học viên:', 0, 1, 'C');
    $pdf->SetFont('Helvetica', 'B', 22);
    $pdf->SetTextColor(124, 58, 237);
    $pdf->Cell(0, 15, $studentName, 0, 1, 'C');
    $pdf->SetFont('Helvetica', '', 14);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->MultiCell(0, 10, 'Đã hoàn thành xuất sắc khóa học ' . $courseName . ' tại nền tảng Music Of Everyone với tinh thần học tập bền bỉ và niềm đam mê âm nhạc chân chính.');
    $pdf->Ln(8);
    $pdf->Cell(0, 10, 'Ngày cấp: ' . $issuedDate . '   |   Mã chứng nhận: ' . $certificateCode, 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Helvetica', 'I', 12);
    $pdf->Cell(0, 10, 'Giám đốc học thuật - musicofeveryone.vn', 0, 1, 'C');
    $pdf->Output('F', $outputPath);
}

function certificate_public_path(string $fileName): string
{
    return '/uploads/certificates/' . $fileName;
}

function student_teachers(int $studentId): array
{
    return query_all(
        'SELECT DISTINCT u.* FROM users u
         INNER JOIN courses c ON c.teacher_id = u.id
         INNER JOIN enrollments e ON e.course_id = c.id
         WHERE e.user_id = ? AND u.role = ?
         ORDER BY u.name',
        [$studentId, 'teacher']
    );
}

function page_is(string $needle): bool
{
    return str_contains($_SERVER['SCRIPT_NAME'] ?? '', $needle);
}

function inject_csrf_tokens(string $buffer): string
{
    if (stripos($buffer, '<form') === false) {
        return $buffer;
    }

    return (string) preg_replace_callback(
        '~<form\b([^>]*)method=(["\'])post\2([^>]*)>~i',
        static function (array $matches): string {
            $tag = $matches[0];
            if (stripos($tag, 'csrf_token') !== false) {
                return $tag;
            }

            return $tag . csrf_field();
        },
        $buffer
    );
}

if (PHP_SAPI !== 'cli') {
    verify_csrf_or_fail();
    if (ob_get_level() === 0) {
        ob_start('inject_csrf_tokens');
    }
}
