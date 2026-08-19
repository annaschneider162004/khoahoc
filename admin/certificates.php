<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (is_post()) {
    [$userId, $courseId] = array_pad(array_map('intval', explode('|', (string) ($_POST['eligibility'] ?? ''))), 2, 0);
    $eligibility = query_one(
        'SELECT e.user_id, e.course_id, u.name AS student_name, c.name AS course_name
         FROM enrollments e
         INNER JOIN users u ON u.id = e.user_id
         INNER JOIN courses c ON c.id = e.course_id
         WHERE e.user_id = ? AND e.course_id = ? AND e.progress >= 80
         LIMIT 1',
        [$userId, $courseId]
    );

    if ($eligibility) {
        $certificateCode = 'MOE-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $fileName = 'certificate_' . strtolower($certificateCode) . '.pdf';
        $filePath = dirname(__DIR__) . '/uploads/certificates/' . $fileName;
        generate_certificate_pdf($eligibility['student_name'], $eligibility['course_name'], date('d/m/Y'), $certificateCode, $filePath);
        $publicPath = certificate_public_path($fileName);
        execute_query('INSERT INTO certificates (user_id, course_id, certificate_code, pdf_path, issued_by, issued_at) VALUES (?, ?, ?, ?, ?, NOW())', [$userId, $courseId, $certificateCode, $publicPath, (int) current_user()['id']]);
        send_notification($userId, 'Bạn vừa nhận chứng chỉ mới', 'Chứng chỉ khóa ' . $eligibility['course_name'] . ' đã sẵn sàng để tải.', '/student/profile.php');
        set_flash('success', 'Đã cấp chứng chỉ thành công.');
    } else {
        set_flash('error', 'Cặp học viên / khóa học không hợp lệ hoặc chưa đủ điều kiện cấp chứng chỉ.');
    }
    redirect('/admin/certificates.php');
}
$eligible = query_all('SELECT e.user_id, e.course_id, u.name AS student_name, c.name AS course_name, e.progress FROM enrollments e INNER JOIN users u ON u.id = e.user_id INNER JOIN courses c ON c.id = e.course_id WHERE e.progress >= 80 ORDER BY u.name');
$history = query_all('SELECT cert.*, u.name AS student_name, c.name AS course_name FROM certificates cert INNER JOIN users u ON u.id = cert.user_id INNER JOIN courses c ON c.id = cert.course_id ORDER BY cert.issued_at DESC');
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Chứng chỉ</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/style.css"></head>
<body class="bg-slate-100"><div class="flex min-h-screen flex-col lg:flex-row"><?php require __DIR__ . '/../includes/admin_sidebar.php'; ?><div class="flex-1 p-6 lg:p-8"><?php render_flashes(); ?><div class="grid gap-8 xl:grid-cols-[0.9fr,1.1fr]"><section class="rounded-[2rem] bg-white p-6 shadow-lg"><h1 class="text-2xl font-black text-slate-900">Cấp chứng chỉ</h1><form method="post" class="mt-6 space-y-4"><select name="eligibility" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required><option value="">Chọn học viên đủ điều kiện</option><?php foreach ($eligible as $item): ?><option value="<?= e($item['user_id'] . '|' . $item['course_id']); ?>"><?= e($item['student_name']); ?> - <?= e($item['course_name']); ?> (<?= e((string) $item['progress']); ?>%)</option><?php endforeach; ?></select><button type="submit" class="rounded-full bg-primary px-5 py-3 font-semibold text-white">Phát hành chứng chỉ</button></form></section><section class="rounded-[2rem] bg-white p-6 shadow-lg"><h2 class="text-2xl font-black text-slate-900">Lịch sử chứng chỉ</h2><div class="mt-5 space-y-4"><?php foreach ($history as $certificate): ?><div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 p-4"><div><p class="font-semibold text-slate-900"><?= e($certificate['student_name']); ?> • <?= e($certificate['course_name']); ?></p><p class="text-sm text-slate-500">Mã <?= e($certificate['certificate_code']); ?> • <?= e(format_datetime_vi($certificate['issued_at'])); ?></p></div><a href="<?= e($certificate['pdf_path']); ?>" class="rounded-full bg-violet-50 px-4 py-2 text-sm font-semibold text-primary" target="_blank" rel="noreferrer">Xem PDF</a></div><?php endforeach; ?></div></section></div></div></div></body></html>
