<?php
require_once __DIR__ . '/functions.php';
$user = current_user();
?>
<aside class="min-h-screen w-full max-w-xs bg-gradient-to-b from-primary to-slate-900 p-6 text-white shadow-2xl">
    <div class="mb-8 flex items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-2xl">🎵</div>
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-white/70">Học viên</p>
            <h2 class="text-lg font-bold"><?= e($user['name'] ?? 'musicofeveryone'); ?></h2>
        </div>
    </div>
    <nav class="space-y-2 text-sm font-medium">
        <a href="/student/dashboard.php" class="sidebar-link <?= page_is('/student/dashboard.php') ? 'active' : ''; ?>">🏠 Tổng quan</a>
        <a href="/student/upload-video.php" class="sidebar-link <?= page_is('/student/upload-video.php') ? 'active' : ''; ?>">⬆️ Nộp video</a>
        <a href="/student/video-library.php" class="sidebar-link <?= page_is('/student/video-library.php') || page_is('/student/video-detail.php') ? 'active' : ''; ?>">🎬 Thư viện video</a>
        <a href="/student/schedule.php" class="sidebar-link <?= page_is('/student/schedule.php') ? 'active' : ''; ?>">🗓️ Lịch học</a>
        <a href="/student/chat.php" class="sidebar-link <?= page_is('/student/chat.php') ? 'active' : ''; ?>">💬 Chat với giáo viên</a>
        <a href="/student/ranking.php" class="sidebar-link <?= page_is('/student/ranking.php') ? 'active' : ''; ?>">🏆 Bảng xếp hạng</a>
        <a href="/student/profile.php" class="sidebar-link <?= page_is('/student/profile.php') ? 'active' : ''; ?>">👤 Hồ sơ</a>
        <a href="/community.php" class="sidebar-link">🌐 Cộng đồng</a>
        <a href="/logout.php" class="sidebar-link">↩️ Đăng xuất</a>
    </nav>
</aside>
