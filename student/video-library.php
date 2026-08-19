<?php
require_once __DIR__ . '/../includes/functions.php';
require_login('student');
$user = current_user();
$search = sanitize_text($_GET['q'] ?? '');
$filter = sanitize_text($_GET['filter'] ?? 'newest');
$orderBy = match ($filter) {
    'trending' => 'v.views DESC, v.created_at DESC',
    'liked' => 'v.likes_count DESC, v.created_at DESC',
    default => 'v.created_at DESC',
};
$sql = "SELECT v.*, c.name AS course_name, u.name AS owner_name
        FROM videos v
        INNER JOIN courses c ON c.id = v.course_id
        INNER JOIN users u ON u.id = v.user_id
        WHERE (v.user_id = ? OR (v.status = ? AND v.privacy IN ('public', 'community')))";
$params = [(int) $user['id'], 'approved'];
if ($search !== '') {
    $sql .= ' AND (v.title LIKE ? OR v.description LIKE ? OR c.name LIKE ?)';
    $term = '%' . $search . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}
$sql .= ' ORDER BY ' . $orderBy;
$videos = query_all($sql, $params);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện video</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-slate-100">
<div class="flex min-h-screen flex-col lg:flex-row">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 p-6 lg:p-8">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Video library</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900">Thư viện video học tập & cộng đồng</h1>
            </div>
            <a href="/student/upload-video.php" class="rounded-full bg-primary px-5 py-3 font-semibold text-white shadow-glow">Nộp video</a>
        </div>
        <form method="get" class="mb-8 grid gap-4 rounded-[2rem] bg-white p-5 shadow-lg md:grid-cols-[1fr,220px,auto]">
            <input type="text" name="q" value="<?= e($search); ?>" class="rounded-2xl border border-slate-200 px-4 py-3" placeholder="Tìm theo tiêu đề, mô tả, khóa học...">
            <select name="filter" class="rounded-2xl border border-slate-200 px-4 py-3">
                <option value="newest" <?= $filter === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                <option value="trending" <?= $filter === 'trending' ? 'selected' : ''; ?>>Trending</option>
                <option value="liked" <?= $filter === 'liked' ? 'selected' : ''; ?>>Được thích nhiều</option>
            </select>
            <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 font-semibold text-white">Lọc</button>
        </form>
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($videos as $video): ?>
                <article class="overflow-hidden rounded-[2rem] bg-white shadow-lg">
                    <div class="flex h-48 items-center justify-center bg-gradient-to-br from-violet-100 to-violet-200 text-6xl">🎬</div>
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-3">
                            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-primary"><?= e($video['course_name']); ?></span>
                            <span class="text-xs text-slate-500"><?= e($video['privacy']); ?></span>
                        </div>
                        <h2 class="mt-4 text-xl font-bold text-slate-900"><?= e($video['title']); ?></h2>
                        <p class="mt-2 line-clamp-2 text-sm text-slate-500"><?= e($video['description']); ?></p>
                        <div class="mt-4 flex items-center justify-between text-sm text-slate-500">
                            <span>👤 <?= e($video['owner_name']); ?></span>
                            <span>❤️ <?= e((string) $video['likes_count']); ?> • 👁️ <?= e((string) $video['views']); ?></span>
                        </div>
                        <a href="/student/video-detail.php?id=<?= e((string) $video['id']); ?>" class="mt-5 inline-flex rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Xem chi tiết</a>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (!$videos): ?>
                <div class="rounded-[2rem] bg-white p-8 text-center text-slate-500 shadow-lg md:col-span-2 xl:col-span-3">Chưa tìm thấy video phù hợp.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
