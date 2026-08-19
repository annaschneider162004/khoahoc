<?php
require_once __DIR__ . '/../includes/functions.php';
require_login('student');
$user = current_user();
$certificateId = (int) ($_GET['id'] ?? 0);
$courseId = (int) ($_GET['course_id'] ?? 0);

if ($certificateId > 0) {
    $certificate = query_one(
        'SELECT cert.*, c.name AS course_name FROM certificates cert INNER JOIN courses c ON c.id = cert.course_id WHERE cert.id = ? AND cert.user_id = ? LIMIT 1',
        [$certificateId, (int) $user['id']]
    );
} else {
    $certificate = query_one(
        'SELECT cert.*, c.name AS course_name FROM certificates cert INNER JOIN courses c ON c.id = cert.course_id WHERE cert.course_id = ? AND cert.user_id = ? LIMIT 1',
        [$courseId, (int) $user['id']]
    );
}

if (!$certificate) {
    exit('Không tìm thấy chứng chỉ phù hợp.');
}

$filePath = $certificate['pdf_path'] ? dirname(__DIR__) . $certificate['pdf_path'] : '';
if (!$certificate['pdf_path'] || !file_exists($filePath)) {
    $fileName = 'certificate_' . $certificate['certificate_code'] . '.pdf';
    $filePath = dirname(__DIR__) . '/uploads/certificates/' . $fileName;
    generate_certificate_pdf($user['name'], $certificate['course_name'], format_datetime_vi($certificate['issued_at'], 'd/m/Y'), $certificate['certificate_code'], $filePath);
    $publicPath = certificate_public_path($fileName);
    execute_query('UPDATE certificates SET pdf_path = ? WHERE id = ?', [$publicPath, (int) $certificate['id']]);
}

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
readfile($filePath);
exit;
