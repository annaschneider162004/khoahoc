<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (is_post()) {
    $action = sanitize_text($_POST['action'] ?? 'save_course');
    try {
        if ($action === 'save_course') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = sanitize_text($_POST['name'] ?? '');
            $description = sanitize_text($_POST['description'] ?? '');
            $cover = sanitize_text($_POST['cover_image'] ?? '');
            $category = sanitize_text($_POST['category'] ?? 'Piano');
            $teacherId = (int) ($_POST['teacher_id'] ?? 0);
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            if ($id > 0) {
                execute_query('UPDATE courses SET name = ?, slug = ?, description = ?, cover_image = ?, category = ?, teacher_id = ? WHERE id = ?', [$name, $slug, $description, $cover, $category, $teacherId ?: null, $id]);
            } else {
                execute_query('INSERT INTO courses (name, slug, description, cover_image, category, teacher_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())', [$name, $slug, $description, $cover, $category, $teacherId ?: null]);
            }
            set_flash('success', 'Đã lưu khóa học.');
        }
        if ($action === 'delete_course') {
            execute_query('DELETE FROM courses WHERE id = ?', [(int) $_POST['id']]);
            set_flash('success', 'Đã xóa khóa học.');
        }
        if ($action === 'add_lesson') {
            execute_query('INSERT INTO lessons (course_id, title, description, lesson_date, video_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())', [(int) $_POST['course_id'], sanitize_text($_POST['lesson_title'] ?? ''), sanitize_text($_POST['lesson_description'] ?? ''), sanitize_text($_POST['lesson_date'] ?? '') ?: null, sanitize_text($_POST['video_url'] ?? '') ?: null]);
            set_flash('success', 'Đã thêm bài học.');
        }
        if ($action === 'delete_lesson') {
            execute_query('DELETE FROM lessons WHERE id = ?', [(int) $_POST['lesson_id']]);
            set_flash('success', 'Đã xóa bài học.');
        }
    } catch (Throwable $exception) {
        set_flash('error', $exception->getMessage());
    }
    redirect('/admin/courses.php' . (!empty($_POST['course_id']) ? '?course=' . (int) $_POST['course_id'] : ''));
}
$teachers = query_all('SELECT id, name FROM users WHERE role = ? ORDER BY name', ['teacher']);
$courseId = (int) ($_GET['course'] ?? 0);
$editId = (int) ($_GET['edit'] ?? 0);
$editing = $editId ? query_one('SELECT * FROM courses WHERE id = ? LIMIT 1', [$editId]) : null;
if ($courseId <= 0) { $courseId = $editing['id'] ?? 0; }
$courses = query_all('SELECT c.*, u.name AS teacher_name FROM courses c LEFT JOIN users u ON u.id = c.teacher_id ORDER BY c.created_at DESC');
$lessons = $courseId ? query_all('SELECT * FROM lessons WHERE course_id = ? ORDER BY lesson_date ASC, created_at DESC', [$courseId]) : [];
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Quản lý khóa học</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/style.css"></head>
<body class="bg-slate-100"><div class="flex min-h-screen flex-col lg:flex-row"><?php require __DIR__ . '/../includes/admin_sidebar.php'; ?><div class="flex-1 p-6 lg:p-8"><?php render_flashes(); ?><div class="grid gap-8 xl:grid-cols-[0.95fr,1.05fr]"><section class="space-y-6"><div class="rounded-[2rem] bg-white p-6 shadow-lg"><h1 class="text-2xl font-black text-slate-900"><?= $editing ? 'Cập nhật khóa học' : 'Tạo khóa học'; ?></h1><form method="post" class="mt-6 space-y-4"><input type="hidden" name="action" value="save_course"><input type="hidden" name="id" value="<?= e((string) ($editing['id'] ?? 0)); ?>"><input type="text" name="name" value="<?= e($editing['name'] ?? ''); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Tên khóa học" required><textarea name="description" class="w-full rounded-2xl border border-slate-200 px-4 py-3" rows="4" placeholder="Mô tả"><?= e($editing['description'] ?? ''); ?></textarea><div class="grid gap-4 md:grid-cols-2"><input type="text" name="cover_image" value="<?= e($editing['cover_image'] ?? '🎵'); ?>" class="rounded-2xl border border-slate-200 px-4 py-3" placeholder="Cover image / emoji"><select name="category" class="rounded-2xl border border-slate-200 px-4 py-3"><?php foreach (['Piano','Guitar','Thanh nhạc','Violin'] as $category): ?><option value="<?= e($category); ?>" <?= ($editing['category'] ?? '') === $category ? 'selected' : ''; ?>><?= e($category); ?></option><?php endforeach; ?></select></div><select name="teacher_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3"><option value="0">Chọn giáo viên phụ trách</option><?php foreach ($teachers as $teacher): ?><option value="<?= e((string) $teacher['id']); ?>" <?= (int) ($editing['teacher_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : ''; ?>><?= e($teacher['name']); ?></option><?php endforeach; ?></select><button type="submit" class="rounded-full bg-primary px-5 py-3 font-semibold text-white">Lưu khóa học</button></form></div><div class="rounded-[2rem] bg-white p-6 shadow-lg"><h2 class="text-2xl font-black text-slate-900">Quản lý bài học</h2><?php if ($courseId > 0): ?><form method="post" class="mt-5 space-y-4"><input type="hidden" name="action" value="add_lesson"><input type="hidden" name="course_id" value="<?= e((string) $courseId); ?>"><input type="text" name="lesson_title" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Tên bài học" required><textarea name="lesson_description" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Mô tả"></textarea><div class="grid gap-4 md:grid-cols-2"><input type="datetime-local" name="lesson_date" class="rounded-2xl border border-slate-200 px-4 py-3"><input type="url" name="video_url" class="rounded-2xl border border-slate-200 px-4 py-3" placeholder="Link video"></div><button type="submit" class="rounded-full bg-slate-900 px-5 py-3 font-semibold text-white">Thêm bài học</button></form><div class="mt-5 space-y-3"><?php foreach ($lessons as $lesson): ?><div class="rounded-2xl border border-slate-100 p-4"><div class="flex items-center justify-between gap-4"><div><p class="font-semibold text-slate-900"><?= e($lesson['title']); ?></p><p class="text-sm text-slate-500"><?= e($lesson['description']); ?></p></div><form method="post" onsubmit="return confirm('Xóa bài học?')"><input type="hidden" name="action" value="delete_lesson"><input type="hidden" name="lesson_id" value="<?= e((string) $lesson['id']); ?>"><button type="submit" class="rounded-full bg-rose-100 px-3 py-1 text-sm font-semibold text-rose-700">Xóa</button></form></div></div><?php endforeach; ?></div><?php else: ?><p class="mt-4 text-sm text-slate-500">Chọn một khóa học để quản lý bài học.</p><?php endif; ?></div></section><section class="rounded-[2rem] bg-white p-6 shadow-lg"><h2 class="text-2xl font-black text-slate-900">Danh sách khóa học</h2><div class="mt-5 space-y-4"><?php foreach ($courses as $course): ?><div class="rounded-2xl border border-slate-100 p-5"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-primary inline-block"><?= e($course['category']); ?></p><h3 class="mt-3 text-xl font-bold text-slate-900"><?= e($course['name']); ?></h3><p class="mt-2 text-sm text-slate-500"><?= e($course['description']); ?></p><p class="mt-3 text-sm text-slate-500">Giáo viên: <?= e($course['teacher_name'] ?? 'Chưa gán'); ?></p></div><div class="flex flex-wrap gap-2"><a href="?edit=<?= e((string) $course['id']); ?>" class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700">Sửa</a><a href="?course=<?= e((string) $course['id']); ?>" class="rounded-full bg-violet-100 px-3 py-1 font-semibold text-primary">Bài học</a><form method="post" onsubmit="return confirm('Xóa khóa học này?')"><input type="hidden" name="action" value="delete_course"><input type="hidden" name="id" value="<?= e((string) $course['id']); ?>"><button type="submit" class="rounded-full bg-rose-100 px-3 py-1 font-semibold text-rose-700">Xóa</button></form></div></div></div><?php endforeach; ?></div></section></div></div></div></body></html>
