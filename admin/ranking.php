<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (is_post()) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $bonus = (int) ($_POST['bonus_points'] ?? 0);
    execute_query('UPDATE users SET points = points + ? WHERE id = ? AND role = ?', [$bonus, $userId, 'student']);
    recalculate_rankings();
    set_flash('success', 'Đã cập nhật điểm thưởng.');
    redirect('/admin/ranking.php');
}
$rows = query_all('SELECT r.*, u.name FROM rankings r INNER JOIN users u ON u.id = r.user_id WHERE r.course_id IS NULL ORDER BY r.points DESC, r.badges_count DESC');
$students = query_all('SELECT id, name FROM users WHERE role = ? ORDER BY name', ['student']);
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Quản lý bảng xếp hạng</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/style.css"></head>
<body class="bg-slate-100"><div class="flex min-h-screen flex-col lg:flex-row"><?php require __DIR__ . '/../includes/admin_sidebar.php'; ?><div class="flex-1 p-6 lg:p-8"><?php render_flashes(); ?><div class="grid gap-8 xl:grid-cols-[0.9fr,1.1fr]"><section class="rounded-[2rem] bg-white p-6 shadow-lg"><h1 class="text-2xl font-black text-slate-900">Thêm điểm thưởng</h1><form method="post" class="mt-6 space-y-4"><select name="user_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required><option value="">Chọn học viên</option><?php foreach ($students as $student): ?><option value="<?= e((string) $student['id']); ?>"><?= e($student['name']); ?></option><?php endforeach; ?></select><input type="number" name="bonus_points" class="w-full rounded-2xl border border-slate-200 px-4 py-3" value="10" required><button type="submit" class="rounded-full bg-primary px-5 py-3 font-semibold text-white">Cộng điểm</button></form></section><section class="rounded-[2rem] bg-white p-6 shadow-lg"><h2 class="text-2xl font-black text-slate-900">BXH toàn hệ thống</h2><div class="mt-5 overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-violet-50 text-primary"><tr><th class="px-4 py-3">Hạng</th><th class="px-4 py-3">Học viên</th><th class="px-4 py-3">Điểm</th><th class="px-4 py-3">Huy hiệu</th></tr></thead><tbody><?php foreach ($rows as $index => $row): ?><tr class="border-t border-slate-100"><td class="px-4 py-3 font-bold text-slate-900"><?= e((string) ($index + 1)); ?></td><td class="px-4 py-3"><?= e($row['name']); ?></td><td class="px-4 py-3"><?= e((string) $row['points']); ?></td><td class="px-4 py-3"><?= e((string) $row['badges_count']); ?></td></tr><?php endforeach; ?></tbody></table></div></section></div></div></div></body></html>
