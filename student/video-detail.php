<?php
require_once __DIR__ . '/../includes/functions.php';
require_login('student');
$user = current_user();
$videoId = (int) ($_GET['id'] ?? 0);

if (is_post()) {
    $action = sanitize_text($_POST['action'] ?? '');
    if ($action === 'like') {
        $exists = query_one('SELECT id FROM video_likes WHERE video_id = ? AND user_id = ? LIMIT 1', [$videoId, (int) $user['id']]);
        if ($exists) {
            execute_query('DELETE FROM video_likes WHERE video_id = ? AND user_id = ?', [$videoId, (int) $user['id']]);
            execute_query('UPDATE videos SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?', [$videoId]);
        } else {
            execute_query('INSERT INTO video_likes (video_id, user_id, created_at) VALUES (?, ?, NOW())', [$videoId, (int) $user['id']]);
            execute_query('UPDATE videos SET likes_count = likes_count + 1 WHERE id = ?', [$videoId]);
        }
        redirect('/student/video-detail.php?id=' . $videoId);
    }

    if ($action === 'comment') {
        $comment = sanitize_text($_POST['comment'] ?? '');
        if ($comment !== '') {
            execute_query('INSERT INTO video_comments (video_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())', [$videoId, (int) $user['id'], $comment]);
            set_flash('success', 'Đã gửi bình luận.');
        }
        redirect('/student/video-detail.php?id=' . $videoId);
    }
}

$video = query_one(
    'SELECT v.*, c.name AS course_name, u.name AS owner_name
     FROM videos v
     INNER JOIN courses c ON c.id = v.course_id
     INNER JOIN users u ON u.id = v.user_id
     WHERE v.id = ? LIMIT 1',
    [$videoId]
);
if (!$video) {
    exit('Video không tồn tại.');
}
if ($video['privacy'] === 'private' && (int) $video['user_id'] !== (int) $user['id']) {
    exit('Bạn không có quyền xem video này.');
}
execute_query('UPDATE videos SET views = views + 1 WHERE id = ?', [$videoId]);
$comments = query_all(
    'SELECT vc.*, u.name, u.role FROM video_comments vc INNER JOIN users u ON u.id = vc.user_id WHERE vc.video_id = ? ORDER BY vc.created_at DESC',
    [$videoId]
);
$liked = query_one('SELECT id FROM video_likes WHERE video_id = ? AND user_id = ? LIMIT 1', [$videoId, (int) $user['id']]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($video['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-slate-100">
<div class="flex min-h-screen flex-col lg:flex-row">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 p-6 lg:p-8">
        <?php render_flashes(); ?>
        <div class="grid gap-8 xl:grid-cols-[1.2fr,0.8fr]">
            <section class="rounded-[2rem] bg-white p-6 shadow-lg">
                <video controls class="w-full rounded-[1.5rem] bg-slate-900">
                    <source src="<?= e($video['file_path']); ?>" type="<?= e($video['mime_type']); ?>">
                </video>
                <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-black text-slate-900"><?= e($video['title']); ?></h1>
                        <p class="mt-2 text-sm text-slate-500"><?= e($video['course_name']); ?> • Bởi <?= e($video['owner_name']); ?></p>
                    </div>
                    <div class="flex flex-wrap gap-3 text-sm text-slate-500">
                        <span class="rounded-full bg-slate-100 px-4 py-2">👁️ <?= e((string) ($video['views'] + 1)); ?></span>
                        <span class="rounded-full bg-slate-100 px-4 py-2">❤️ <?= e((string) $video['likes_count']); ?></span>
                    </div>
                </div>
                <p class="mt-5 whitespace-pre-line text-slate-600"><?= e($video['description']); ?></p>
                <form method="post" class="mt-6">
                    <input type="hidden" name="action" value="like">
                    <button type="submit" class="rounded-full <?= $liked ? 'bg-primary text-white' : 'bg-violet-50 text-primary'; ?> px-5 py-3 font-semibold"><?= $liked ? 'Đã thích' : 'Thích video'; ?></button>
                </form>
            </section>
            <section class="rounded-[2rem] bg-white p-6 shadow-lg">
                <h2 class="text-xl font-black text-slate-900">Bình luận</h2>
                <form method="post" class="mt-5 flex gap-3">
                    <input type="hidden" name="action" value="comment">
                    <input type="text" name="comment" class="flex-1 rounded-2xl border border-slate-200 px-4 py-3" placeholder="Viết phản hồi của bạn...">
                    <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Gửi</button>
                </form>
                <div class="mt-6 space-y-4">
                    <?php foreach ($comments as $comment): ?>
                        <div class="rounded-2xl <?= in_array($comment['role'], ['teacher', 'admin'], true) ? 'border border-violet-200 bg-violet-50' : 'bg-slate-50'; ?> p-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-semibold text-slate-900"><?= e($comment['name']); ?> <?= in_array($comment['role'], ['teacher', 'admin'], true) ? '<span class="ml-2 rounded-full bg-primary px-2 py-1 text-xs text-white">Giáo viên</span>' : ''; ?></p>
                                <p class="text-xs text-slate-400"><?= e(format_datetime_vi($comment['created_at'])); ?></p>
                            </div>
                            <p class="mt-3 text-sm text-slate-600"><?= e($comment['comment']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>
</div>
</body>
</html>
