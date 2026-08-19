<?php
require_once __DIR__ . '/functions.php';
$user = current_user();
?>
<aside class="min-h-screen w-full max-w-xs bg-gradient-to-b from-primary to-slate-950 p-6 text-white shadow-2xl">
    <div class="mb-8 flex items-center gap-3">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-2xl">🎼</div>
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-white/70">Quản trị</p>
            <h2 class="text-lg font-bold"><?= e($user['name'] ?? 'Admin'); ?></h2>
        </div>
    </div>
    <nav class="space-y-2 text-sm font-medium">
        <a href="/admin/dashboard.php" class="sidebar-link <?= page_is('/admin/dashboard.php') ? 'active' : ''; ?>">📊 Dashboard</a>
        <a href="/admin/students.php" class="sidebar-link <?= page_is('/admin/students.php') ? 'active' : ''; ?>">👩‍🎓 Học viên</a>
        <a href="/admin/teachers.php" class="sidebar-link <?= page_is('/admin/teachers.php') ? 'active' : ''; ?>">👨‍🏫 Giáo viên</a>
        <a href="/admin/courses.php" class="sidebar-link <?= page_is('/admin/courses.php') ? 'active' : ''; ?>">📚 Khóa học</a>
        <a href="/admin/videos.php" class="sidebar-link <?= page_is('/admin/videos.php') ? 'active' : ''; ?>">🎥 Video</a>
        <a href="/admin/grading.php" class="sidebar-link <?= page_is('/admin/grading.php') ? 'active' : ''; ?>">📝 Chấm điểm</a>
        <a href="/admin/certificates.php" class="sidebar-link <?= page_is('/admin/certificates.php') ? 'active' : ''; ?>">📄 Chứng chỉ</a>
        <a href="/admin/ranking.php" class="sidebar-link <?= page_is('/admin/ranking.php') ? 'active' : ''; ?>">🥇 BXH</a>
        <a href="/admin/livestream.php" class="sidebar-link <?= page_is('/admin/livestream.php') ? 'active' : ''; ?>">📡 Livestream</a>
        <a href="/admin/community.php" class="sidebar-link <?= page_is('/admin/community.php') ? 'active' : ''; ?>">🧑‍🤝‍🧑 Cộng đồng</a>
        <a href="/admin/notifications.php" class="sidebar-link <?= page_is('/admin/notifications.php') ? 'active' : ''; ?>">🔔 Thông báo</a>
        <a href="/admin/settings.php" class="sidebar-link <?= page_is('/admin/settings.php') ? 'active' : ''; ?>">⚙️ Cài đặt</a>
        <a href="/logout.php" class="sidebar-link">↩️ Đăng xuất</a>
    </nav>
</aside>
