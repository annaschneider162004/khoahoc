<?php
require_once __DIR__ . '/../includes/functions.php';
require_login('student');
$user = current_user();
$courses = query_all('SELECT c.id, c.name FROM enrollments e INNER JOIN courses c ON c.id = e.course_id WHERE e.user_id = ? ORDER BY c.name', [(int) $user['id']]);

if (is_post()) {
    try {
        $title = sanitize_text($_POST['title'] ?? '');
        $description = sanitize_text($_POST['description'] ?? '');
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $privacy = sanitize_text($_POST['privacy'] ?? 'community');

        if ($title === '' || $courseId <= 0) {
            throw new RuntimeException('Vui lòng nhập tiêu đề và chọn khóa học.');
        }

        if (!in_array($privacy, ['public', 'community', 'private'], true)) {
            throw new RuntimeException('Mức riêng tư không hợp lệ.');
        }

        $path = upload_file(
            'video_file',
            'uploads/videos',
            ['mp4', 'mov', 'avi'],
            ['video/mp4', 'video/quicktime', 'video/x-msvideo'],
            200 * 1024 * 1024
        );

        if (!$path) {
            throw new RuntimeException('Vui lòng chọn video tải lên.');
        }

        execute_query(
            'INSERT INTO videos (user_id, course_id, title, description, file_path, mime_type, privacy, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [(int) $user['id'], $courseId, $title, $description, $path, mime_content_type(__DIR__ . '/..' . $path) ?: 'video/mp4', $privacy, 'pending']
        );
        send_role_notification('admin', 'Video mới chờ duyệt', $user['name'] . ' vừa nộp video mới: ' . $title, '/admin/videos.php');
        set_flash('success', 'Video đã được tải lên và đang chờ duyệt.');
        redirect('/student/video-library.php');
    } catch (Throwable $exception) {
        set_flash('error', $exception->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nộp video</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-slate-100">
<div class="flex min-h-screen flex-col lg:flex-row">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 p-6 lg:p-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Upload bài tập</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Nộp video thực hành cho giáo viên</h1>
        </div>
        <?php render_flashes(); ?>
        <div class="rounded-[2rem] bg-white p-8 shadow-lg">
            <form method="post" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Tiêu đề video</label>
                    <input type="text" name="title" value="<?= old('title'); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Mô tả</label>
                    <textarea name="description" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3"><?= old('description'); ?></textarea>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Khóa học</label>
                        <select name="course_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
                            <option value="">Chọn khóa học</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= e((string) $course['id']); ?>"><?= e($course['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Quyền riêng tư</label>
                        <select name="privacy" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                            <option value="community">Cộng đồng học viên</option>
                            <option value="public">Công khai</option>
                            <option value="private">Riêng tư</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Video (mp4/mov/avi, tối đa 200MB)</label>
                    <div class="rounded-[2rem] border-2 border-dashed border-violet-200 bg-violet-50 p-10 text-center">
                        <p class="text-4xl">🎥</p>
                        <p class="mt-3 font-semibold text-primary">Kéo thả hoặc chọn tệp video</p>
                        <input type="file" name="video_file" accept=".mp4,.mov,.avi,video/mp4,video/quicktime,video/x-msvideo" class="mx-auto mt-5 block rounded-2xl border border-slate-200 bg-white px-4 py-3" required>
                    </div>
                </div>
                <button type="submit" class="rounded-full bg-primary px-6 py-3 font-bold text-white shadow-glow">Tải lên video</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
