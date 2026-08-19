<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (is_post()) {
    $action = sanitize_text($_POST['action'] ?? 'save');
    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $data = [sanitize_text($_POST['title'] ?? ''), sanitize_text($_POST['session_date'] ?? ''), sanitize_text($_POST['stream_url'] ?? ''), sanitize_text($_POST['description'] ?? ''), sanitize_text($_POST['status'] ?? 'upcoming')];
        if ($id > 0) {
            execute_query('UPDATE livestreams SET title = ?, session_date = ?, stream_url = ?, description = ?, status = ? WHERE id = ?', [...$data, $id]);
        } else {
            execute_query('INSERT INTO livestreams (title, session_date, stream_url, description, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())', $data);
        }
        set_flash('success', 'Đã lưu phiên livestream.');
    }
    if ($action === 'delete') {
        execute_query('DELETE FROM livestreams WHERE id = ?', [(int) $_POST['id']]);
        set_flash('success', 'Đã xóa livestream.');
    }
    redirect('/admin/livestream.php');
}
$editId = (int) ($_GET['edit'] ?? 0);
$editing = $editId ? query_one('SELECT * FROM livestreams WHERE id = ? LIMIT 1', [$editId]) : null;
$items = query_all('SELECT * FROM livestreams ORDER BY session_date DESC');
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Quản lý livestream</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/style.css"></head>
<body class="bg-slate-100"><div class="flex min-h-screen flex-col lg:flex-row"><?php require __DIR__ . '/../includes/admin_sidebar.php'; ?><div class="flex-1 p-6 lg:p-8"><?php render_flashes(); ?><div class="grid gap-8 xl:grid-cols-[0.9fr,1.1fr]"><section class="rounded-[2rem] bg-white p-6 shadow-lg"><h1 class="text-2xl font-black text-slate-900"><?= $editing ? 'Cập nhật livestream' : 'Tạo livestream'; ?></h1><form method="post" class="mt-6 space-y-4"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string) ($editing['id'] ?? 0)); ?>"><input type="text" name="title" value="<?= e($editing['title'] ?? ''); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Tiêu đề" required><input type="datetime-local" name="session_date" value="<?= !empty($editing['session_date']) ? e(date('Y-m-d\TH:i', strtotime($editing['session_date']))) : ''; ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required><input type="url" name="stream_url" value="<?= e($editing['stream_url'] ?? ''); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Link YouTube / HLS" required><textarea name="description" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Mô tả"><?= e($editing['description'] ?? ''); ?></textarea><select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3"><?php foreach (['upcoming','live','ended'] as $status): ?><option value="<?= e($status); ?>" <?= ($editing['status'] ?? 'upcoming') === $status ? 'selected' : ''; ?>><?= e($status); ?></option><?php endforeach; ?></select><button type="submit" class="rounded-full bg-primary px-5 py-3 font-semibold text-white">Lưu lịch livestream</button></form></section><section class="rounded-[2rem] bg-white p-6 shadow-lg"><h2 class="text-2xl font-black text-slate-900">Danh sách livestream</h2><div class="mt-5 space-y-4"><?php foreach ($items as $item): ?><div class="rounded-2xl border border-slate-100 p-4"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-primary inline-block"><?= e($item['status']); ?></p><h3 class="mt-3 text-lg font-bold text-slate-900"><?= e($item['title']); ?></h3><p class="mt-2 text-sm text-slate-500"><?= e(format_datetime_vi($item['session_date'])); ?></p></div><div class="flex flex-wrap gap-2"><a href="?edit=<?= e((string) $item['id']); ?>" class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">Sửa</a><form method="post" onsubmit="return confirm('Xóa livestream này?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string) $item['id']); ?>"><button type="submit" class="rounded-full bg-rose-100 px-3 py-1 font-semibold text-rose-700">Xóa</button></form></div></div></div><?php endforeach; ?></div></section></div></div></div></body></html>
