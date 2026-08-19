<?php
require_once __DIR__ . '/../includes/functions.php';
require_login('student');
$courseId = (int) ($_GET['course_id'] ?? 0);
$courses = query_all('SELECT id, name FROM courses ORDER BY name');
$sql = 'SELECT r.points, r.badges_count, u.name, c.name AS course_name
        FROM rankings r
        INNER JOIN users u ON u.id = r.user_id
        LEFT JOIN courses c ON c.id = r.course_id
        WHERE r.course_id IS NULL';
$params = [];
if ($courseId > 0) {
    $sql = 'SELECT r.points, r.badges_count, u.name, c.name AS course_name
            FROM rankings r
            INNER JOIN users u ON u.id = r.user_id
            INNER JOIN courses c ON c.id = r.course_id
            WHERE r.course_id = ?';
    $params[] = $courseId;
}
$sql .= ' ORDER BY r.points DESC, r.badges_count DESC LIMIT 20';
$rows = query_all($sql, $params);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng xếp hạng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-slate-100">
<div class="flex min-h-screen flex-col lg:flex-row">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 p-6 lg:p-8">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Leaderboard</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">Bảng xếp hạng học viên</h1>
            </div>
            <form method="get">
                <select name="course_id" onchange="this.form.submit()" class="rounded-full border border-slate-200 bg-white px-4 py-3 text-sm font-semibold">
                    <option value="0">Toàn nền tảng</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= e((string) $course['id']); ?>" <?= $courseId === (int) $course['id'] ? 'selected' : ''; ?>><?= e($course['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="overflow-hidden rounded-[2rem] bg-white shadow-lg">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-violet-50 text-primary">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Hạng</th>
                        <th class="px-6 py-4 font-semibold">Học viên</th>
                        <th class="px-6 py-4 font-semibold">Khóa học</th>
                        <th class="px-6 py-4 font-semibold">Điểm</th>
                        <th class="px-6 py-4 font-semibold">Huy hiệu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $index => $row): ?>
                        <tr class="border-t border-slate-100">
                            <td class="px-6 py-4 font-bold text-slate-900"><?= e((string) ($index + 1)); ?></td>
                            <td class="px-6 py-4"><?= e($row['name']); ?></td>
                            <td class="px-6 py-4"><?= e($row['course_name'] ?? 'Toàn nền tảng'); ?></td>
                            <td class="px-6 py-4 text-primary"><?= e((string) $row['points']); ?></td>
                            <td class="px-6 py-4"><?= e((string) $row['badges_count']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
