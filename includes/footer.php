<?php require_once __DIR__ . '/functions.php'; ?>
</main>
<footer class="mt-16 bg-slate-950 text-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 md:grid-cols-4 lg:px-8">
        <div>
            <h3 class="mb-4 text-lg font-bold">musicofeveryone</h3>
            <p class="text-sm text-slate-300">Nền tảng học nhạc trực tuyến hiện đại dành cho học viên Việt Nam yêu piano, guitar, thanh nhạc và violin.</p>
        </div>
        <div>
            <h4 class="mb-4 font-semibold">Khám phá</h4>
            <ul class="space-y-2 text-sm text-slate-300">
                <li><a href="/index.php" class="hover:text-white">Trang chủ</a></li>
                <li><a href="/community.php" class="hover:text-white">Cộng đồng</a></li>
                <li><a href="/livestream.php" class="hover:text-white">Lịch livestream</a></li>
                <li><a href="/contact.php" class="hover:text-white">Liên hệ</a></li>
            </ul>
        </div>
        <div>
            <h4 class="mb-4 font-semibold">Kết nối</h4>
            <ul class="space-y-2 text-sm text-slate-300">
                <li><a href="<?= e(setting('facebook_url', '#')); ?>" target="_blank" rel="noreferrer">Facebook</a></li>
                <li><a href="<?= e(setting('youtube_url', '#')); ?>" target="_blank" rel="noreferrer">YouTube</a></li>
                <li><a href="<?= e(setting('tiktok_url', '#')); ?>" target="_blank" rel="noreferrer">TikTok</a></li>
                <li><?= e(setting('contact_phone', '0900 000 000')); ?></li>
            </ul>
        </div>
        <div>
            <h4 class="mb-4 font-semibold">Bản quyền</h4>
            <p class="text-sm text-slate-300">© <?= date('Y'); ?> musicofeveryone. Tất cả quyền được bảo lưu.</p>
            <p class="mt-3 text-sm text-slate-300">Địa chỉ: <?= e(setting('contact_address', 'TP. Hồ Chí Minh')); ?></p>
        </div>
    </div>
</footer>
<script src="/assets/js/main.js"></script>
</body>
</html>
