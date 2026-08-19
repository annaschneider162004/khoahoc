<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (is_post()) {
    $videoId = (int) ($_POST['video_id'] ?? 0);
    $studentId = (int) ($_POST['student_id'] ?? 0);
    $score = max(0, min(100, (int) ($_POST['score'] ?? 0)));
    $comment = sanitize_text($_POST['comment'] ?? '');
    $existing = query_one('SELECT id FROM grades WHERE video_id = ? LIMIT 1', [$videoId]);
    if ($existing) {
        $currentGrade = query_one('SELECT score FROM grades WHERE id = ? LIMIT 1', [(int) $existing['id']]);
        execute_query('UPDATE grades SET score = ?, comment = ?, teacher_id = ?, updated_at = NOW() WHERE id = ?', [$score, $comment, (int) current_user()['id'], (int) $existing['id']]);
        $delta = $score - (int) ($currentGrade['score'] ?? 0);
        if ($delta !== 0) {
            execute_query('UPDATE users SET points = points + ? WHERE id = ?', [$delta, $studentId]);
        }
    } else {
        execute_query('INSERT INTO grades (video_id, student_id, teacher_id, score, comment, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())', [$videoId, $studentId, (int) current_user()['id'], $score, $comment]);
        execute_query('UPDATE users SET points = points + ? WHERE id = ?', [$score, $studentId]);
    }
    if ($score >= 90) {
        $badge = query_one('SELECT id FROM badges WHERE points_required <= ? ORDER BY points_required DESC LIMIT 1', [$score]);
        if ($badge && !query_one('SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ? LIMIT 1', [$studentId, (int) $badge['id']])) {
            execute_query('INSERT INTO user_badges (user_id, badge_id, awarded_at) VALUES (?, ?, NOW())', [$studentId, (int) $badge['id']]);
        }
    }
    recalculate_rankings();
    send_notification($studentId, 'Video đã được chấm điểm', 'Bạn vừa nhận được điểm ' . $score . ' cho video thực hành.', '/student/video-detail.php?id=' . $videoId);
    set_flash('success', 'Đã lưu kết quả chấm điểm.');
    redirect('/admin/grading.php');
}
$videos = query_all('SELECT v.id, v.title, v.user_id, u.name AS student_name, c.name AS course_name, COALESCE(g.score, -1) AS current_score, g.comment FROM videos v INNER JOIN users u ON u.id = v.user_id INNER JOIN courses c ON c.id = v.course_id LEFT JOIN grades g ON g.video_id = v.id WHERE v.status = ? ORDER BY v.created_at DESC', ['approved']);
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Chấm điểm video</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/style.css"></head>
<body class="bg-slate-100"><div class="flex min-h-screen flex-col lg:flex-row"><?php require __DIR__ . '/../includes/admin_sidebar.php'; ?><div class="flex-1 p-6 lg:p-8"><?php render_flashes(); ?><div class="rounded-[2rem] bg-white p-6 shadow-lg"><h1 class="text-3xl font-black text-slate-900">Chấm điểm video học viên</h1><div class="mt-6 space-y-5"><?php foreach ($videos as $video): ?><form method="post" class="rounded-[1.8rem] border border-slate-100 p-5"><input type="hidden" name="video_id" value="<?= e((string) $video['id']); ?>"><input type="hidden" name="student_id" value="<?= e((string) $video['user_id']); ?>"><div class="grid gap-6 xl:grid-cols-[1fr,360px]"><div><h2 class="text-xl font-bold text-slate-900"><?= e($video['title']); ?></h2><p class="mt-2 text-sm text-slate-500"><?= e($video['student_name']); ?> • <?= e($video['course_name']); ?></p><p class="mt-3 text-sm text-slate-600">Điểm hiện tại: <?= $video['current_score'] >= 0 ? e((string) $video['current_score']) : 'Chưa chấm'; ?></p></div><div class="space-y-4"><input type="number" name="score" min="0" max="100" value="<?= $video['current_score'] >= 0 ? e((string) $video['current_score']) : '80'; ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required><textarea name="comment" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Nhận xét cho học viên"><?= e($video['comment'] ?? ''); ?></textarea><button type="submit" class="rounded-2xl bg-primary px-5 py-3 font-semibold text-white">Lưu điểm</button></div></div></form><?php endforeach; ?></div></div></div></div></body></html>
