<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (is_post()) {
    $videoId = (int) ($_POST['video_id'] ?? 0);
    $action = sanitize_text($_POST['action'] ?? 'approve');
    $feedback = sanitize_text($_POST['feedback_reason'] ?? '');
    if ($action === 'approve') {
        execute_query('UPDATE videos SET status = ?, feedback_reason = ?, updated_at = NOW() WHERE id = ?', ['approved', $feedback, $videoId]);
        $video = query_one('SELECT v.title, v.user_id FROM videos v WHERE v.id = ? LIMIT 1', [$videoId]);
        if ($video) send_notification((int) $video['user_id'], 'Video đã được duyệt', 'Video "' . $video['title'] . '" đã được phê duyệt.', '/student/video-detail.php?id=' . $videoId);
        set_flash('success', 'Đã duyệt video.');
    }
    if ($action === 'reject') {
        execute_query('UPDATE videos SET status = ?, feedback_reason = ?, updated_at = NOW() WHERE id = ?', ['rejected', $feedback, $videoId]);
        $video = query_one('SELECT v.title, v.user_id FROM videos v WHERE v.id = ? LIMIT 1', [$videoId]);
        if ($video) send_notification((int) $video['user_id'], 'Video cần chỉnh sửa', 'Video "' . $video['title'] . '" bị từ chối: ' . $feedback, '/student/video-library.php');
        set_flash('warning', 'Video đã bị từ chối.');
    }
    redirect('/admin/videos.php');
}
$videos = query_all('SELECT v.*, u.name AS student_name, c.name AS course_name FROM videos v INNER JOIN users u ON u.id = v.user_id INNER JOIN courses c ON c.id = v.course_id ORDER BY FIELD(v.status, "pending", "rejected", "approved"), v.created_at DESC');
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Duyệt video</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/style.css"></head>
<body class="bg-slate-100"><div class="flex min-h-screen flex-col lg:flex-row"><?php require __DIR__ . '/../includes/admin_sidebar.php'; ?><div class="flex-1 p-6 lg:p-8"><?php render_flashes(); ?><div class="rounded-[2rem] bg-white p-6 shadow-lg"><h1 class="text-3xl font-black text-slate-900">Hàng chờ duyệt video</h1><div class="mt-6 space-y-5"><?php foreach ($videos as $video): ?><div class="rounded-[1.8rem] border border-slate-100 p-5"><div class="grid gap-6 xl:grid-cols-[1fr,320px]"><div><div class="flex flex-wrap items-center gap-3"><span class="rounded-full <?= $video['status'] === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($video['status'] === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700'); ?> px-3 py-1 text-xs font-semibold"><?= e($video['status']); ?></span><span class="text-sm text-slate-500"><?= e($video['student_name']); ?> • <?= e($video['course_name']); ?></span></div><h2 class="mt-3 text-xl font-bold text-slate-900"><?= e($video['title']); ?></h2><p class="mt-2 text-sm text-slate-600"><?= e($video['description']); ?></p><video controls class="mt-4 w-full rounded-2xl bg-slate-900"><source src="<?= e($video['file_path']); ?>" type="<?= e($video['mime_type']); ?>"></video><?php if (!empty($video['feedback_reason'])): ?><p class="mt-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">Phản hồi: <?= e($video['feedback_reason']); ?></p><?php endif; ?></div><div><form method="post" class="space-y-4"><input type="hidden" name="video_id" value="<?= e((string) $video['id']); ?>"><textarea name="feedback_reason" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Lý do / góp ý cho học viên"><?= e($video['feedback_reason']); ?></textarea><div class="grid gap-3"><button type="submit" name="action" value="approve" class="rounded-2xl bg-emerald-500 px-5 py-3 font-semibold text-white">Duyệt video</button><button type="submit" name="action" value="reject" class="rounded-2xl bg-rose-500 px-5 py-3 font-semibold text-white">Từ chối</button></div></form></div></div></div><?php endforeach; ?></div></div></div></div></body></html>
