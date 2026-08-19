<?php
require_once __DIR__ . '/includes/functions.php';
$user = current_user();

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    if (!$user) {
        echo json_encode(['unread_count' => 0, 'items' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $items = query_all('SELECT id, title, message, link, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10', [(int) $user['id']]);
    echo json_encode([
        'unread_count' => notification_count((int) $user['id']),
        'items' => array_map(static fn(array $item): array => [
            'id' => (int) $item['id'],
            'title' => $item['title'],
            'message' => $item['message'],
            'link' => $item['link'],
            'is_read' => (bool) $item['is_read'],
            'created_at' => format_datetime_vi($item['created_at']),
        ], $items),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_login();
$pageTitle = 'Thông báo - musicofeveryone';
$items = query_all('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC', [(int) $user['id']]);
execute_query('UPDATE notifications SET is_read = 1 WHERE user_id = ?', [(int) $user['id']]);
require_once __DIR__ . '/includes/header.php';
?>
<section class="mx-auto max-w-5xl px-4 py-12 lg:px-8">
    <div class="mb-8 flex items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Thông báo</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Cập nhật mới nhất dành cho bạn</h1>
        </div>
        <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-semibold text-primary"><?= e((string) count($items)); ?> thông báo</span>
    </div>
    <ul id="notificationList" class="space-y-4">
        <?php foreach ($items as $item): ?>
            <li class="rounded-[2rem] bg-white p-6 shadow-lg">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900"><?= e($item['title']); ?></h2>
                        <p class="mt-2 text-slate-600"><?= e($item['message']); ?></p>
                        <p class="mt-3 text-xs text-slate-400"><?= e(format_datetime_vi($item['created_at'])); ?></p>
                    </div>
                    <?php if (!empty($item['link'])): ?>
                        <a href="<?= e($item['link']); ?>" class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Xem</a>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
