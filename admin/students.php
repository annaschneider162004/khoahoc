<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if (is_post()) {
    $action = sanitize_text($_POST['action'] ?? 'save');
    try {
        if ($action === 'save') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = sanitize_text($_POST['name'] ?? '');
            $email = strtolower(sanitize_text($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $points = (int) ($_POST['points'] ?? 0);
            if ($id > 0) {
                if ($password !== '') {
                    execute_query('UPDATE users SET name = ?, email = ?, password = ?, points = ?, updated_at = NOW() WHERE id = ? AND role = ?', [$name, $email, password_hash($password, PASSWORD_BCRYPT), $points, $id, 'student']);
                } else {
                    execute_query('UPDATE users SET name = ?, email = ?, points = ?, updated_at = NOW() WHERE id = ? AND role = ?', [$name, $email, $points, $id, 'student']);
                }
            } else {
                execute_query('INSERT INTO users (name, email, password, role, points, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())', [$name, $email, password_hash($password ?: 'hocvien123', PASSWORD_BCRYPT), 'student', $points]);
            }
            recalculate_rankings();
            set_flash('success', 'Đã lưu thông tin học viên.');
        }
        if ($action === 'delete') {
            execute_query('DELETE FROM users WHERE id = ? AND role = ?', [(int) $_POST['id'], 'student']);
            set_flash('success', 'Đã xóa học viên.');
        }
        if ($action === 'toggle_lock') {
            execute_query('UPDATE users SET locked = ? WHERE id = ? AND role = ?', [(int) $_POST['locked'], (int) $_POST['id'], 'student']);
            set_flash('success', 'Đã cập nhật trạng thái tài khoản.');
        }
    } catch (Throwable $exception) {
        set_flash('error', $exception->getMessage());
    }
    redirect('/admin/students.php');
}
$editId = (int) ($_GET['edit'] ?? 0);
$editing = $editId ? query_one('SELECT * FROM users WHERE id = ? AND role = ? LIMIT 1', [$editId, 'student']) : null;
$students = query_all('SELECT * FROM users WHERE role = ? ORDER BY created_at DESC', ['student']);
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Quản lý học viên</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/style.css"></head>
<body class="bg-slate-100"><div class="flex min-h-screen flex-col lg:flex-row"><?php require __DIR__ . '/../includes/admin_sidebar.php'; ?><div class="flex-1 p-6 lg:p-8"><?php render_flashes(); ?>
<div class="grid gap-8 xl:grid-cols-[0.9fr,1.1fr]">
<section class="rounded-[2rem] bg-white p-6 shadow-lg"><h1 class="text-2xl font-black text-slate-900"><?= $editing ? 'Cập nhật học viên' : 'Thêm học viên'; ?></h1><form method="post" class="mt-6 space-y-4"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string) ($editing['id'] ?? 0)); ?>"><input type="text" name="name" value="<?= e($editing['name'] ?? ''); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Họ tên" required><input type="email" name="email" value="<?= e($editing['email'] ?? ''); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Email" required><input type="password" name="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Mật khẩu <?= $editing ? '(để trống nếu giữ nguyên)' : ''; ?>" <?= $editing ? '' : 'required'; ?>><input type="number" name="points" value="<?= e((string) ($editing['points'] ?? 0)); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Điểm"><button type="submit" class="rounded-full bg-primary px-5 py-3 font-semibold text-white">Lưu học viên</button></form></section>
<section class="rounded-[2rem] bg-white p-6 shadow-lg"><h2 class="text-2xl font-black text-slate-900">Danh sách học viên</h2><div class="mt-5 overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-violet-50 text-primary"><tr><th class="px-4 py-3">Tên</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Điểm</th><th class="px-4 py-3">Trạng thái</th><th class="px-4 py-3">Thao tác</th></tr></thead><tbody><?php foreach ($students as $student): ?><tr class="border-t border-slate-100"><td class="px-4 py-3 font-semibold text-slate-900"><?= e($student['name']); ?></td><td class="px-4 py-3"><?= e($student['email']); ?></td><td class="px-4 py-3"><?= e((string) $student['points']); ?></td><td class="px-4 py-3"><?= (int) $student['locked'] === 1 ? 'Khóa' : 'Hoạt động'; ?></td><td class="px-4 py-3"><div class="flex flex-wrap gap-2"><a href="?edit=<?= e((string) $student['id']); ?>" class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">Sửa</a><form method="post"><input type="hidden" name="action" value="toggle_lock"><input type="hidden" name="id" value="<?= e((string) $student['id']); ?>"><input type="hidden" name="locked" value="<?= (int) $student['locked'] === 1 ? '0' : '1'; ?>"><button type="submit" class="rounded-full bg-amber-100 px-3 py-1 font-semibold text-amber-700"><?= (int) $student['locked'] === 1 ? 'Mở khóa' : 'Khóa'; ?></button></form><form method="post" onsubmit="return confirm('Xóa học viên này?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string) $student['id']); ?>"><button type="submit" class="rounded-full bg-rose-100 px-3 py-1 font-semibold text-rose-700">Xóa</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></section>
</div></div></div></body></html>
