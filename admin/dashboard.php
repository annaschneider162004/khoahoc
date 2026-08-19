<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$counts = [
    'students' => (int) (query_one('SELECT COUNT(*) AS total FROM users WHERE role = ?', ['student'])['total'] ?? 0),
    'teachers' => (int) (query_one('SELECT COUNT(*) AS total FROM users WHERE role = ?', ['teacher'])['total'] ?? 0),
    'courses' => (int) (query_one('SELECT COUNT(*) AS total FROM courses')['total'] ?? 0),
    'videos' => (int) (query_one('SELECT COUNT(*) AS total FROM videos')['total'] ?? 0),
];
$videosByCourse = query_all('SELECT c.name, COUNT(v.id) AS total FROM courses c LEFT JOIN videos v ON v.course_id = c.id GROUP BY c.id, c.name ORDER BY c.name');
$pointsByStudent = query_all('SELECT name, points FROM users WHERE role = ? ORDER BY points DESC LIMIT 6', ['student']);
$activities = query_all(
    '(SELECT CONCAT(u.name, " đã tải video ", v.title) AS activity, v.created_at AS created_at FROM videos v INNER JOIN users u ON u.id = v.user_id)
     UNION ALL
     (SELECT CONCAT(u.name, " đã đăng bài cộng đồng") AS activity, p.created_at AS created_at FROM community_posts p INNER JOIN users u ON u.id = p.user_id)
     ORDER BY created_at DESC LIMIT 8'
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-slate-100">
<div class="flex min-h-screen flex-col lg:flex-row">
    <?php require __DIR__ . '/../includes/admin_sidebar.php'; ?>
    <div class="flex-1 p-6 lg:p-8">
        <div class="mb-8 flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Quản trị tổng quan</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">Dashboard hệ thống</h1>
            </div>
        </div>
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[2rem] bg-white p-6 shadow-lg"><p class="text-sm text-slate-500">Tổng học viên</p><p class="mt-3 text-4xl font-black text-primary"><?= e((string) $counts['students']); ?></p></div>
            <div class="rounded-[2rem] bg-white p-6 shadow-lg"><p class="text-sm text-slate-500">Tổng giáo viên</p><p class="mt-3 text-4xl font-black text-primary"><?= e((string) $counts['teachers']); ?></p></div>
            <div class="rounded-[2rem] bg-white p-6 shadow-lg"><p class="text-sm text-slate-500">Khóa học</p><p class="mt-3 text-4xl font-black text-primary"><?= e((string) $counts['courses']); ?></p></div>
            <div class="rounded-[2rem] bg-white p-6 shadow-lg"><p class="text-sm text-slate-500">Video</p><p class="mt-3 text-4xl font-black text-primary"><?= e((string) $counts['videos']); ?></p></div>
        </div>
        <div class="mt-8 grid gap-8 xl:grid-cols-2">
            <div class="rounded-[2rem] bg-white p-6 shadow-lg">
                <h2 class="text-xl font-black text-slate-900">Video theo khóa học</h2>
                <canvas id="courseChart" class="mt-6"></canvas>
            </div>
            <div class="rounded-[2rem] bg-white p-6 shadow-lg">
                <h2 class="text-xl font-black text-slate-900">Điểm học viên nổi bật</h2>
                <canvas id="pointsChart" class="mt-6"></canvas>
            </div>
        </div>
        <div class="mt-8 rounded-[2rem] bg-white p-6 shadow-lg">
            <h2 class="text-xl font-black text-slate-900">Hoạt động gần đây</h2>
            <div class="mt-5 space-y-4">
                <?php foreach ($activities as $activity): ?>
                    <div class="rounded-2xl border border-slate-100 p-4 text-sm text-slate-600">
                        <p class="font-semibold text-slate-900"><?= e($activity['activity']); ?></p>
                        <p class="mt-1 text-xs text-slate-400"><?= e(format_datetime_vi($activity['created_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<script>
const courseCtx = document.getElementById('courseChart');
new Chart(courseCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($videosByCourse, 'name'), JSON_UNESCAPED_UNICODE); ?>,
        datasets: [{
            label: 'Số video',
            data: <?= json_encode(array_map('intval', array_column($videosByCourse, 'total'))); ?>,
            backgroundColor: '#7C3AED',
            borderRadius: 14
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
const pointsCtx = document.getElementById('pointsChart');
new Chart(pointsCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($pointsByStudent, 'name'), JSON_UNESCAPED_UNICODE); ?>,
        datasets: [{
            label: 'Điểm tích lũy',
            data: <?= json_encode(array_map('intval', array_column($pointsByStudent, 'points'))); ?>,
            borderColor: '#6D28D9',
            backgroundColor: 'rgba(109, 40, 217, 0.15)',
            fill: true,
            tension: 0.35
        }]
    },
    options: { responsive: true }
});
</script>
</body>
</html>
