<?php
$pageTitle = 'musicofeveryone - Học nhạc online truyền cảm hứng';
require_once __DIR__ . '/includes/header.php';

$featuredCourses = query_all(
    'SELECT c.*, u.name AS teacher_name,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS student_total
     FROM courses c
     LEFT JOIN users u ON u.id = c.teacher_id
     ORDER BY c.id ASC
     LIMIT 4'
);
$stats = [
    'students' => (int) (query_one('SELECT COUNT(*) AS total FROM users WHERE role = ?', ['student'])['total'] ?? 0),
    'teachers' => (int) (query_one('SELECT COUNT(*) AS total FROM users WHERE role = ?', ['teacher'])['total'] ?? 0),
    'courses' => (int) (query_one('SELECT COUNT(*) AS total FROM courses')['total'] ?? 0),
    'videos' => (int) (query_one('SELECT COUNT(*) AS total FROM videos WHERE status = ?', ['approved'])['total'] ?? 0),
];
$testimonials = query_all(
    'SELECT u.name, u.avatar_path, c.name AS course_name, g.comment, g.score
     FROM grades g
     INNER JOIN users u ON u.id = g.student_id
     INNER JOIN videos v ON v.id = g.video_id
     INNER JOIN courses c ON c.id = v.course_id
     WHERE g.comment <> ""
     ORDER BY g.created_at DESC
     LIMIT 3'
);
?>
<section class="relative overflow-hidden music-gradient note-float">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 lg:grid-cols-2 lg:px-8 lg:py-24">
        <div class="text-white fade-in-up">
            <span class="inline-flex rounded-full bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur">🎼 Nền tảng học nhạc trực tuyến dành cho mọi lứa tuổi</span>
            <h1 class="mt-6 text-4xl font-black leading-tight md:text-6xl">Khơi nguồn cảm hứng âm nhạc cùng <span class="text-violet-200">musicofeveryone</span></h1>
            <p class="mt-6 max-w-2xl text-lg text-violet-50">Học piano, guitar, thanh nhạc và violin với giáo viên giàu kinh nghiệm, nộp video nhận phản hồi cá nhân và tham gia cộng đồng âm nhạc sôi động mỗi ngày.</p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="/register.php" class="rounded-full bg-white px-6 py-3 font-bold text-primary shadow-xl">Bắt đầu miễn phí</a>
                <a href="/livestream.php" class="rounded-full border border-white/40 px-6 py-3 font-bold text-white backdrop-blur">Xem lịch livestream</a>
            </div>
            <div class="mt-10 grid max-w-xl grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur"><p class="text-3xl">🎹</p><p class="mt-2 text-sm">Piano</p></div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur"><p class="text-3xl">🎸</p><p class="mt-2 text-sm">Guitar</p></div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur"><p class="text-3xl">🎤</p><p class="mt-2 text-sm">Thanh nhạc</p></div>
                <div class="rounded-2xl bg-white/10 p-4 backdrop-blur"><p class="text-3xl">🎻</p><p class="mt-2 text-sm">Violin</p></div>
            </div>
        </div>
        <div class="relative">
            <div class="rounded-[2rem] bg-white/15 p-4 shadow-2xl backdrop-blur-xl">
                <div class="rounded-[1.75rem] bg-white p-6 shadow-glow">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl bg-violet-50 p-6 text-center text-primary"><div class="text-6xl">🎹</div><p class="mt-3 text-lg font-bold">Lộ trình thực chiến</p></div>
                        <div class="rounded-3xl bg-slate-900 p-6 text-center text-white"><div class="text-6xl">🎸</div><p class="mt-3 text-lg font-bold">Phản hồi video</p></div>
                        <div class="rounded-3xl bg-primary p-6 text-center text-white"><div class="text-6xl">🎤</div><p class="mt-3 text-lg font-bold">Livestream trực tiếp</p></div>
                        <div class="rounded-3xl bg-violet-100 p-6 text-center text-primary"><div class="text-6xl">🎻</div><p class="mt-3 text-lg font-bold">Cộng đồng nâng đỡ</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="mb-10 flex items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Khóa học nổi bật</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">Lộ trình học nhạc hiện đại, dễ theo dõi</h2>
        </div>
        <a href="/register.php" class="rounded-full border border-primary px-5 py-2 font-semibold text-primary">Tham gia ngay</a>
    </div>
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($featuredCourses as $course): ?>
            <article class="card-hover overflow-hidden rounded-[2rem] bg-white shadow-lg">
                <div class="music-gradient flex h-44 items-center justify-center text-7xl text-white">
                    <?= e(match ($course['category']) {
                        'Piano' => '🎹',
                        'Guitar' => '🎸',
                        'Thanh nhạc' => '🎤',
                        'Violin' => '🎻',
                        default => '🎵'
                    }); ?>
                </div>
                <div class="p-6">
                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-primary"><?= e($course['category']); ?></span>
                    <h3 class="mt-4 text-xl font-bold text-slate-900"><?= e($course['name']); ?></h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600"><?= e($course['description']); ?></p>
                    <div class="mt-6 flex items-center justify-between text-sm text-slate-500">
                        <span>👩‍🏫 <?= e($course['teacher_name'] ?? 'Đang cập nhật'); ?></span>
                        <span><?= e((string) $course['student_total']); ?> học viên</span>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-4 lg:px-8">
    <div class="grid gap-6 rounded-[2rem] bg-slate-900 px-8 py-10 text-white shadow-2xl md:grid-cols-4">
        <div><p class="text-4xl font-black"><?= e((string) $stats['students']); ?>+</p><p class="mt-2 text-sm text-slate-300">Học viên đang học</p></div>
        <div><p class="text-4xl font-black"><?= e((string) $stats['teachers']); ?>+</p><p class="mt-2 text-sm text-slate-300">Giáo viên đồng hành</p></div>
        <div><p class="text-4xl font-black"><?= e((string) $stats['courses']); ?></p><p class="mt-2 text-sm text-slate-300">Khóa học chuyên sâu</p></div>
        <div><p class="text-4xl font-black"><?= e((string) $stats['videos']); ?>+</p><p class="mt-2 text-sm text-slate-300">Video thực hành</p></div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="grid gap-6 lg:grid-cols-3">
        <?php foreach ($testimonials as $testimonial): ?>
            <article class="rounded-[2rem] bg-white p-6 shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-2xl text-primary">🎵</div>
                    <div>
                        <h3 class="font-bold text-slate-900"><?= e($testimonial['name']); ?></h3>
                        <p class="text-sm text-slate-500"><?= e($testimonial['course_name']); ?> • <?= e((string) $testimonial['score']); ?>/100</p>
                    </div>
                </div>
                <p class="mt-5 text-sm leading-7 text-slate-600">“<?= e($testimonial['comment']); ?>”</p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 pb-20 lg:px-8">
    <div class="rounded-[2.2rem] music-gradient p-10 text-white shadow-glow">
        <div class="grid items-center gap-8 lg:grid-cols-[1.5fr,1fr]">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-violet-100">Sẵn sàng bứt phá?</p>
                <h2 class="mt-3 text-3xl font-black md:text-4xl">Tham gia musicofeveryone để nhận lộ trình học, lịch livestream và phản hồi cá nhân hóa.</h2>
            </div>
            <div class="flex flex-wrap justify-start gap-4 lg:justify-end">
                <a href="/register.php" class="rounded-full bg-white px-6 py-3 font-bold text-primary">Tạo tài khoản</a>
                <a href="/contact.php" class="rounded-full border border-white/30 px-6 py-3 font-bold text-white">Liên hệ tư vấn</a>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
