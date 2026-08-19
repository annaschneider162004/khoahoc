<?php
require_once __DIR__ . '/../includes/functions.php';
require_login('student');
$user = current_user();

$enrolledCourses = query_all(
    'SELECT c.name, c.category, e.progress, u.name AS teacher_name
     FROM enrollments e
     INNER JOIN courses c ON c.id = e.course_id
     LEFT JOIN users u ON u.id = c.teacher_id
     WHERE e.user_id = ?
     ORDER BY e.created_at DESC',
    [(int) $user['id']]
);
$pendingAssignments = query_all(
    'SELECT a.title, a.due_date, c.name AS course_name
     FROM assignments a
     INNER JOIN courses c ON c.id = a.course_id
     INNER JOIN enrollments e ON e.course_id = c.id
     WHERE e.user_id = ? AND a.due_date >= NOW()
     ORDER BY a.due_date ASC LIMIT 5',
    [(int) $user['id']]
);
$recentVideos = query_all(
    'SELECT v.*, c.name AS course_name FROM videos v
     INNER JOIN courses c ON c.id = v.course_id
     WHERE v.user_id = ?
     ORDER BY v.created_at DESC LIMIT 4',
    [(int) $user['id']]
);
$badges = query_all(
    'SELECT b.name, b.icon FROM user_badges ub INNER JOIN badges b ON b.id = ub.badge_id WHERE ub.user_id = ? ORDER BY ub.awarded_at DESC LIMIT 5',
    [(int) $user['id']]
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển học viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-slate-100 text-slate-800">
<div class="flex min-h-screen flex-col lg:flex-row">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 p-6 lg:p-8">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Dashboard học viên</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">Xin chào, <?= e($user['name']); ?> 👋</h1>
            </div>
            <a href="/student/upload-video.php" class="rounded-full bg-primary px-5 py-3 font-semibold text-white shadow-glow">Nộp video mới</a>
        </div>
        <?php render_flashes(); ?>
        <div class="grid gap-6 md:grid-cols-3">
            <div class="rounded-[2rem] bg-white p-6 shadow-lg"><p class="text-sm text-slate-500">Điểm tích lũy</p><p class="mt-3 text-4xl font-black text-primary"><?= e((string) $user['points']); ?></p></div>
            <div class="rounded-[2rem] bg-white p-6 shadow-lg"><p class="text-sm text-slate-500">Huy hiệu</p><p class="mt-3 text-4xl font-black text-primary"><?= e((string) badge_count((int) $user['id'])); ?></p></div>
            <div class="rounded-[2rem] bg-white p-6 shadow-lg"><p class="text-sm text-slate-500">Thông báo mới</p><p class="mt-3 text-4xl font-black text-primary"><?= e((string) notification_count((int) $user['id'])); ?></p></div>
        </div>

        <div class="mt-8 grid gap-8 xl:grid-cols-[1.1fr,0.9fr]">
            <section class="space-y-6">
                <div class="rounded-[2rem] bg-white p-6 shadow-lg">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-xl font-black text-slate-900">Khóa học đang theo học</h2>
                        <span class="text-sm text-slate-500"><?= e((string) count($enrolledCourses)); ?> khóa</span>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($enrolledCourses as $course): ?>
                            <div class="rounded-2xl border border-slate-100 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold text-slate-900"><?= e($course['name']); ?></h3>
                                        <p class="text-sm text-slate-500"><?= e($course['category']); ?> • GV: <?= e($course['teacher_name'] ?? 'Đang cập nhật'); ?></p>
                                    </div>
                                    <span class="text-sm font-semibold text-primary"><?= e((string) $course['progress']); ?>%</span>
                                </div>
                                <div class="mt-4 h-3 rounded-full bg-slate-100">
                                    <div class="h-3 rounded-full bg-gradient-to-r from-primary to-secondary" style="width: <?= e((string) $course['progress']); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="rounded-[2rem] bg-white p-6 shadow-lg">
                    <h2 class="text-xl font-black text-slate-900">Video thực hành gần đây</h2>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <?php foreach ($recentVideos as $video): ?>
                            <a href="/student/video-detail.php?id=<?= e((string) $video['id']); ?>" class="rounded-2xl border border-slate-100 p-4 transition hover:-translate-y-1">
                                <div class="flex h-28 items-center justify-center rounded-2xl bg-violet-100 text-5xl">🎬</div>
                                <h3 class="mt-4 font-bold text-slate-900"><?= e($video['title']); ?></h3>
                                <p class="mt-1 text-sm text-slate-500"><?= e($video['course_name']); ?> • <?= e($video['status']); ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <section class="space-y-6">
                <div class="rounded-[2rem] bg-white p-6 shadow-lg">
                    <h2 class="text-xl font-black text-slate-900">Bài tập sắp đến hạn</h2>
                    <div class="mt-4 space-y-4">
                        <?php foreach ($pendingAssignments as $assignment): ?>
                            <div class="rounded-2xl bg-amber-50 p-4 text-sm text-amber-800">
                                <p class="font-semibold"><?= e($assignment['title']); ?></p>
                                <p class="mt-1"><?= e($assignment['course_name']); ?> • Hạn: <?= e(format_datetime_vi($assignment['due_date'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$pendingAssignments): ?>
                            <p class="text-sm text-slate-500">Tuyệt vời! Bạn không có bài tập đang chờ.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="rounded-[2rem] music-gradient p-6 text-white shadow-glow">
                    <h2 class="text-xl font-black">Thành tích nổi bật</h2>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <?php foreach ($badges as $badge): ?>
                            <div class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold"><?= e($badge['icon']); ?> <?= e($badge['name']); ?></div>
                        <?php endforeach; ?>
                        <?php if (!$badges): ?>
                            <p class="text-sm text-violet-100">Tiếp tục luyện tập để mở khóa huy hiệu đầu tiên của bạn.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>
