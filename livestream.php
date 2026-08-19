<?php
$pageTitle = 'Livestream - musicofeveryone';
require_once __DIR__ . '/includes/header.php';

$sessions = query_all('SELECT * FROM livestreams ORDER BY session_date ASC');
$selectedId = (int) ($_GET['id'] ?? ($sessions[0]['id'] ?? 0));
$selected = query_one('SELECT * FROM livestreams WHERE id = ? LIMIT 1', [$selectedId]) ?? ($sessions[0] ?? null);
$embedUrl = $selected ? youtube_embed_url((string) $selected['stream_url']) : '';
?>
<section class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Lịch phát trực tiếp</p>
        <h1 class="mt-3 text-4xl font-black text-slate-900">Livestream học nhạc cùng giáo viên</h1>
    </div>
    <div class="grid gap-8 lg:grid-cols-[1.2fr,0.8fr]">
        <div class="overflow-hidden rounded-[2rem] bg-white p-6 shadow-lg">
            <?php if ($selected): ?>
                <div class="aspect-video overflow-hidden rounded-[1.5rem] bg-slate-900">
                    <?php if (str_contains((string) $selected['stream_url'], '.m3u8')): ?>
                        <video id="livePlayer" controls class="h-full w-full"></video>
                        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const video = document.getElementById('livePlayer');
                                const source = <?= json_encode($selected['stream_url']); ?>;
                                if (window.Hls && Hls.isSupported()) {
                                    const hls = new Hls();
                                    hls.loadSource(source);
                                    hls.attachMedia(video);
                                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                                    video.src = source;
                                }
                            });
                        </script>
                    <?php else: ?>
                        <iframe src="<?= e($embedUrl); ?>" class="h-full w-full" allowfullscreen></iframe>
                    <?php endif; ?>
                </div>
                <div class="mt-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full <?= $selected['status'] === 'live' ? 'bg-rose-100 text-rose-600' : 'bg-violet-100 text-primary'; ?> px-3 py-1 text-xs font-semibold uppercase"><?= e($selected['status']); ?></span>
                        <span class="text-sm text-slate-500"><?= e(format_datetime_vi($selected['session_date'])); ?></span>
                    </div>
                    <h2 class="mt-3 text-2xl font-black text-slate-900"><?= e($selected['title']); ?></h2>
                    <p class="mt-3 text-slate-600"><?= e($selected['description']); ?></p>
                </div>
            <?php else: ?>
                <p class="text-slate-500">Chưa có livestream nào.</p>
            <?php endif; ?>
        </div>
        <div class="space-y-4">
            <?php foreach ($sessions as $session): ?>
                <a href="/livestream.php?id=<?= e((string) $session['id']); ?>" class="block rounded-[2rem] bg-white p-5 shadow-lg transition hover:-translate-y-1">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-primary"><?= e($session['status']); ?></p>
                            <h3 class="mt-2 text-lg font-bold text-slate-900"><?= e($session['title']); ?></h3>
                            <p class="mt-2 text-sm text-slate-500"><?= e(format_datetime_vi($session['session_date'])); ?></p>
                        </div>
                        <div class="text-4xl">📡</div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
