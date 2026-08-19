<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (is_post()) {
    $target = sanitize_text($_POST['target_role'] ?? 'student');
    $title = sanitize_text($_POST['title'] ?? '');
    $message = sanitize_text($_POST['message'] ?? '');
    $link = sanitize_text($_POST['link'] ?? '');
    $roles = $target === 'all' ? ['student', 'teacher', 'admin'] : [$target];
    foreach ($roles as $role) {
        send_role_notification($role, $title, $message, $link);
    }
    set_flash('success', 'Thông báo hệ thống đã được gửi.');
    redirect('/admin/notifications.php');
}
$history = query_all('SELECT n.*, u.name FROM notifications n LEFT JOIN users u ON u.id = n.user_id ORDER BY n.created_at DESC LIMIT 30');
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Thông báo hệ thống</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/style.css"></head>
<body class="bg-slate-100"><div class="flex min-h-screen flex-col lg:flex-row"><?php require __DIR__ . '/../includes/admin_sidebar.php'; ?><div class="flex-1 p-6 lg:p-8"><?php render_flashes(); ?><div class="grid gap-8 xl:grid-cols-[0.9fr,1.1fr]"><section class="rounded-[2rem] bg-white p-6 shadow-lg"><h1 class="text-2xl font-black text-slate-900">Gửi thông báo</h1><form method="post" class="mt-6 space-y-4"><select name="target_role" class="w-full rounded-2xl border border-slate-200 px-4 py-3"><option value="student">Học viên</option><option value="teacher">Giáo viên</option><option value="admin">Admin</option><option value="all">Toàn hệ thống</option></select><input type="text" name="title" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Tiêu đề" required><textarea name="message" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Nội dung" required></textarea><input type="text" name="link" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Liên kết điều hướng (tùy chọn)"><button type="submit" class="rounded-full bg-primary px-5 py-3 font-semibold text-white">Gửi thông báo</button></form></section><section class="rounded-[2rem] bg-white p-6 shadow-lg"><h2 class="text-2xl font-black text-slate-900">Lịch sử gần đây</h2><div class="mt-5 space-y-4"><?php foreach ($history as $item): ?><div class="rounded-2xl border border-slate-100 p-4"><p class="font-semibold text-slate-900"><?= e($item['title']); ?> <?= $item['name'] ? '• ' . e($item['name']) : ''; ?></p><p class="mt-2 text-sm text-slate-600"><?= e($item['message']); ?></p><p class="mt-2 text-xs text-slate-400"><?= e(format_datetime_vi($item['created_at'])); ?></p></div><?php endforeach; ?></div></section></div></div></div></body></html>
