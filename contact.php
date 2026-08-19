<?php
$pageTitle = 'Liên hệ - musicofeveryone';
require_once __DIR__ . '/includes/header.php';
?>
<section class="mx-auto max-w-7xl px-4 py-16 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-[1.1fr,0.9fr]">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Liên hệ & bản đồ</p>
            <h1 class="mt-3 text-4xl font-black text-slate-900">Chúng tôi luôn sẵn sàng đồng hành cùng bạn</h1>
            <p class="mt-4 max-w-2xl text-slate-600">Tư vấn khóa học, hỗ trợ kỹ thuật, định hướng lộ trình học nhạc phù hợp với độ tuổi và mục tiêu của bạn.</p>
            <div class="mt-8 overflow-hidden rounded-[2rem] border border-slate-200 shadow-lg">
                <iframe src="<?= e(build_map_embed()); ?>" class="h-[420px] w-full" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
        <div class="space-y-6">
            <div class="rounded-[2rem] bg-white p-8 shadow-lg">
                <h2 class="text-2xl font-black text-slate-900">Thông tin liên hệ</h2>
                <div class="mt-6 space-y-5 text-sm text-slate-600">
                    <p>📍 <span class="font-semibold text-slate-900">Địa chỉ:</span> <?= e(setting('contact_address', '12 Nguyễn Huệ, Quận 1, TP.HCM')); ?></p>
                    <p>📞 <span class="font-semibold text-slate-900">Hotline:</span> <?= e(setting('contact_phone', '0909 123 456')); ?></p>
                    <p>✉️ <span class="font-semibold text-slate-900">Email:</span> <?= e(setting('contact_email', 'hello@musicofeveryone.vn')); ?></p>
                    <p>⏰ <span class="font-semibold text-slate-900">Giờ làm việc:</span> <?= e(setting('working_hours', '08:00 - 20:00 | Thứ 2 - Chủ nhật')); ?></p>
                </div>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="<?= e(setting('facebook_url', '#')); ?>" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Facebook</a>
                    <a href="<?= e(setting('youtube_url', '#')); ?>" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">YouTube</a>
                    <a href="<?= e(setting('tiktok_url', '#')); ?>" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">TikTok</a>
                </div>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode(setting('contact_address', 'TP.HCM')); ?>" target="_blank" rel="noreferrer" class="mt-8 inline-flex rounded-full bg-primary px-6 py-3 font-bold text-white shadow-glow">Chỉ đường đến trung tâm</a>
            </div>
            <div class="rounded-[2rem] music-gradient p-8 text-white shadow-glow">
                <h3 class="text-2xl font-black">Đặt lịch tư vấn miễn phí</h3>
                <p class="mt-3 text-violet-100">Gọi ngay hoặc gửi email để được tư vấn khóa học phù hợp cho trẻ em, thiếu niên và người lớn.</p>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
