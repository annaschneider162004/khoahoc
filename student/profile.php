<?php
require_once __DIR__ . '/../includes/functions.php';
require_login('student');
$user = current_user();

if (is_post()) {
    try {
        if (!empty($_FILES['avatar']['name'])) {
            $avatarPath = upload_file('avatar', 'uploads/avatars', ['jpg', 'jpeg', 'png', 'webp'], ['image/jpeg', 'image/png', 'image/webp'], 5 * 1024 * 1024);
            if ($avatarPath) {
                execute_query('UPDATE users SET avatar_path = ?, updated_at = NOW() WHERE id = ?', [$avatarPath, (int) $user['id']]);
                set_flash('success', 'Ảnh đại diện đã được cập nhật.');
                redirect('/student/profile.php');
            }
        }
    } catch (Throwable $exception) {
        set_flash('error', $exception->getMessage());
    }
}
$user = query_one('SELECT * FROM users WHERE id = ? LIMIT 1', [(int) $user['id']]) ?? $user;
$progressRows = query_all(
    'SELECT c.id, c.name, e.progress FROM enrollments e INNER JOIN courses c ON c.id = e.course_id WHERE e.user_id = ? ORDER BY c.name',
    [(int) $user['id']]
);
$certificates = query_all(
    'SELECT cert.*, c.name AS course_name FROM certificates cert INNER JOIN courses c ON c.id = cert.course_id WHERE cert.user_id = ? ORDER BY cert.issued_at DESC',
    [(int) $user['id']]
);
$videos = query_all('SELECT id, title, status, created_at FROM videos WHERE user_id = ? ORDER BY created_at DESC', [(int) $user['id']]);
$badges = query_all('SELECT b.* FROM user_badges ub INNER JOIN badges b ON b.id = ub.badge_id WHERE ub.user_id = ? ORDER BY ub.awarded_at DESC', [(int) $user['id']]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ học viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-slate-100">
<div class="flex min-h-screen flex-col lg:flex-row">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 p-6 lg:p-8">
        <?php render_flashes(); ?>
        <div class="grid gap-8 xl:grid-cols-[0.9fr,1.1fr]">
            <section class="rounded-[2rem] bg-white p-8 shadow-lg">
                <div class="text-center">
                    <div class="mx-auto flex h-28 w-28 items-center justify-center overflow-hidden rounded-[2rem] bg-violet-100 text-5xl text-primary">
                        <?php if (!empty($user['avatar_path'])): ?>
                            <img src="<?= e($user['avatar_path']); ?>" alt="Avatar" class="h-full w-full object-cover">
                        <?php else: ?>
                            🎵
                        <?php endif; ?>
                    </div>
                    <h1 class="mt-5 text-3xl font-black text-slate-900"><?= e($user['name']); ?></h1>
                    <p class="mt-2 text-slate-500"><?= e($user['email']); ?></p>
                    <p class="mt-4 rounded-full bg-violet-50 px-4 py-2 text-sm font-semibold text-primary"><?= e((string) $user['points']); ?> điểm • <?= e((string) badge_count((int) $user['id'])); ?> huy hiệu</p>
                </div>
                <form method="post" enctype="multipart/form-data" class="mt-8 space-y-4">
                    <label class="block text-sm font-semibold text-slate-700">Cập nhật ảnh đại diện</label>
                    <input type="file" name="avatar" accept="image/*" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                    <button type="submit" class="rounded-full bg-primary px-5 py-3 font-semibold text-white">Tải ảnh mới</button>
                </form>
                <div class="mt-8">
                    <h2 class="text-xl font-black text-slate-900">Huy hiệu của bạn</h2>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <?php foreach ($badges as $badge): ?>
                            <span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700"><?= e($badge['icon']); ?> <?= e($badge['name']); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <section class="space-y-6">
                <div class="rounded-[2rem] bg-white p-6 shadow-lg">
                    <h2 class="text-xl font-black text-slate-900">Tiến độ theo khóa học</h2>
                    <div class="mt-5 space-y-4">
                        <?php foreach ($progressRows as $progress): ?>
                            <div>
                                <div class="flex items-center justify-between gap-4 text-sm font-semibold text-slate-700">
                                    <span><?= e($progress['name']); ?></span>
                                    <span><?= e((string) $progress['progress']); ?>%</span>
                                </div>
                                <div class="mt-3 h-3 rounded-full bg-slate-100">
                                    <div class="h-3 rounded-full bg-gradient-to-r from-primary to-secondary" style="width: <?= e((string) $progress['progress']); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="rounded-[2rem] bg-white p-6 shadow-lg">
                    <h2 class="text-xl font-black text-slate-900">Chứng chỉ</h2>
                    <div class="mt-5 space-y-4">
                        <?php foreach ($certificates as $certificate): ?>
                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 p-4">
                                <div>
                                    <p class="font-semibold text-slate-900"><?= e($certificate['course_name']); ?></p>
                                    <p class="text-sm text-slate-500">Mã: <?= e($certificate['certificate_code']); ?> • <?= e(format_datetime_vi($certificate['issued_at'], 'd/m/Y')); ?></p>
                                </div>
                                <a href="/student/certificate-download.php?id=<?= e((string) $certificate['id']); ?>" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Tải PDF</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="rounded-[2rem] bg-white p-6 shadow-lg">
                    <h2 class="text-xl font-black text-slate-900">Video đã tải lên</h2>
                    <div class="mt-5 space-y-3">
                        <?php foreach ($videos as $video): ?>
                            <a href="/student/video-detail.php?id=<?= e((string) $video['id']); ?>" class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 p-4 transition hover:bg-slate-50">
                                <div>
                                    <p class="font-semibold text-slate-900"><?= e($video['title']); ?></p>
                                    <p class="text-sm text-slate-500"><?= e(format_datetime_vi($video['created_at'])); ?></p>
                                </div>
                                <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-primary"><?= e($video['status']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
</body>
</html>
