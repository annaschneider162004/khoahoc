<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (is_post()) {
    execute_query('DELETE FROM community_posts WHERE id = ?', [(int) $_POST['post_id']]);
    set_flash('success', 'Đã xóa bài viết vi phạm.');
    redirect('/admin/community.php');
}
$posts = query_all('SELECT p.*, u.name FROM community_posts p INNER JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC');
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Điều phối cộng đồng</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/style.css"></head>
<body class="bg-slate-100"><div class="flex min-h-screen flex-col lg:flex-row"><?php require __DIR__ . '/../includes/admin_sidebar.php'; ?><div class="flex-1 p-6 lg:p-8"><?php render_flashes(); ?><div class="rounded-[2rem] bg-white p-6 shadow-lg"><h1 class="text-3xl font-black text-slate-900">Kiểm duyệt bài viết cộng đồng</h1><div class="mt-6 space-y-4"><?php foreach ($posts as $post): ?><div class="rounded-2xl border border-slate-100 p-5"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="font-semibold text-slate-900"><?= e($post['name']); ?></p><p class="mt-2 text-sm whitespace-pre-line text-slate-600"><?= e($post['content']); ?></p><p class="mt-3 text-xs text-slate-400"><?= e(format_datetime_vi($post['created_at'])); ?></p></div><form method="post" onsubmit="return confirm('Xóa bài viết này?')"><input type="hidden" name="post_id" value="<?= e((string) $post['id']); ?>"><button type="submit" class="rounded-full bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-700">Xóa bài viết</button></form></div></div><?php endforeach; ?></div></div></div></div></body></html>
