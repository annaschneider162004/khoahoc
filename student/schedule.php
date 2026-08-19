<?php
require_once __DIR__ . '/../includes/functions.php';
require_login('student');
$user = current_user();
$view = sanitize_text($_GET['view'] ?? 'week');
$startInput = sanitize_text($_GET['start'] ?? date('Y-m-d'));
try {
    $startDate = new DateTime($startInput);
} catch (Throwable $exception) {
    $startDate = new DateTime();
}

if ($view === 'week') {
    $startDate->modify('monday this week');
    $endDate = (clone $startDate)->modify('+6 days');
    $rows = query_all(
        'SELECT s.*, c.name AS course_name, u.name AS teacher_name FROM schedules s
         INNER JOIN courses c ON c.id = s.course_id
         INNER JOIN enrollments e ON e.course_id = c.id
         LEFT JOIN users u ON u.id = s.teacher_id
         WHERE e.user_id = ? AND DATE(s.start_time) BETWEEN ? AND ?
         ORDER BY s.start_time ASC',
        [(int) $user['id'], $startDate->format('Y-m-d'), $endDate->format('Y-m-d')]
    );
} else {
    $startDate->modify('first day of this month');
    $endDate = (clone $startDate)->modify('last day of this month');
    $rows = query_all(
        'SELECT s.*, c.name AS course_name, u.name AS teacher_name FROM schedules s
         INNER JOIN courses c ON c.id = s.course_id
         INNER JOIN enrollments e ON e.course_id = c.id
         LEFT JOIN users u ON u.id = s.teacher_id
         WHERE e.user_id = ? AND DATE(s.start_time) BETWEEN ? AND ?
         ORDER BY s.start_time ASC',
        [(int) $user['id'], $startDate->format('Y-m-d'), $endDate->format('Y-m-d')]
    );
}
$previous = (clone $startDate)->modify($view === 'week' ? '-7 days' : '-1 month')->format('Y-m-d');
$next = (clone $startDate)->modify($view === 'week' ? '+7 days' : '+1 month')->format('Y-m-d');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch học</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-slate-100">
<div class="flex min-h-screen flex-col lg:flex-row">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 p-6 lg:p-8">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Lịch học</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900"><?= $view === 'week' ? 'Lịch theo tuần' : 'Lịch theo tháng'; ?></h1>
                <p class="mt-2 text-sm text-slate-500"><?= e(format_datetime_vi($startDate->format('Y-m-d'), 'd/m/Y')); ?> - <?= e(format_datetime_vi($endDate->format('Y-m-d'), 'd/m/Y')); ?></p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="?view=<?= e($view); ?>&start=<?= e($previous); ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">← Trước</a>
                <a href="?view=week&start=<?= e($startDate->format('Y-m-d')); ?>" class="rounded-full <?= $view === 'week' ? 'bg-primary text-white' : 'bg-white text-slate-700'; ?> px-4 py-2 text-sm font-semibold">Tuần</a>
                <a href="?view=month&start=<?= e($startDate->format('Y-m-d')); ?>" class="rounded-full <?= $view === 'month' ? 'bg-primary text-white' : 'bg-white text-slate-700'; ?> px-4 py-2 text-sm font-semibold">Tháng</a>
                <a href="?view=<?= e($view); ?>&start=<?= e($next); ?>" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">Tiếp →</a>
            </div>
        </div>
        <div class="rounded-[2rem] bg-white p-6 shadow-lg">
            <div class="space-y-4">
                <?php foreach ($rows as $row): ?>
                    <div class="rounded-2xl border border-slate-100 p-5">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900"><?= e($row['title']); ?></h2>
                                <p class="mt-1 text-sm text-slate-500"><?= e($row['course_name']); ?> • <?= e($row['subject']); ?></p>
                            </div>
                            <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-semibold text-primary"><?= e(format_datetime_vi($row['start_time'])); ?></span>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">Giáo viên: <?= e($row['teacher_name'] ?? 'Đang cập nhật'); ?> • Phòng: <?= e($row['room'] ?: 'Online'); ?> • Kết thúc: <?= e(format_datetime_vi($row['end_time'])); ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <p class="text-sm text-slate-500">Không có buổi học nào trong giai đoạn đã chọn.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
