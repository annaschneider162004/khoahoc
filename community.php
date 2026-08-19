<?php
require_once __DIR__ . '/includes/functions.php';
$user = current_user();

if (is_post()) {
    if (!$user) {
        set_flash('error', 'Vui lòng đăng nhập để tham gia cộng đồng.');
        redirect('/login.php');
    }

    $action = sanitize_text($_POST['action'] ?? 'create');

    try {
        if ($action === 'create') {
            $content = sanitize_text($_POST['content'] ?? '');
            $postType = sanitize_text($_POST['post_type'] ?? 'text');
            $emoji = sanitize_text($_POST['emoji'] ?? '🎵');
            $mediaPath = null;
            if (!empty($_FILES['media']['name'])) {
                $mediaPath = upload_file(
                    'media',
                    'uploads/community',
                    ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'webm'],
                    ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime', 'video/webm'],
                    30 * 1024 * 1024
                );
            }

            if ($content === '' && !$mediaPath) {
                throw new RuntimeException('Vui lòng nhập nội dung hoặc tải media.');
            }

            execute_query(
                'INSERT INTO community_posts (user_id, content, post_type, media_path, emoji, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
                [(int) $user['id'], $content, $postType, $mediaPath, $emoji]
            );
            set_flash('success', 'Bài viết đã được đăng lên cộng đồng.');
        }

        if ($action === 'like') {
            $postId = (int) ($_POST['post_id'] ?? 0);
            $exists = query_one('SELECT id FROM community_likes WHERE post_id = ? AND user_id = ? LIMIT 1', [$postId, (int) $user['id']]);
            if ($exists) {
                execute_query('DELETE FROM community_likes WHERE post_id = ? AND user_id = ?', [$postId, (int) $user['id']]);
            } else {
                execute_query('INSERT INTO community_likes (post_id, user_id, created_at) VALUES (?, ?, NOW())', [$postId, (int) $user['id']]);
            }
        }

        if ($action === 'comment') {
            $postId = (int) ($_POST['post_id'] ?? 0);
            $comment = sanitize_text($_POST['comment'] ?? '');
            if ($comment !== '') {
                execute_query('INSERT INTO community_comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())', [$postId, (int) $user['id'], $comment]);
            }
        }

        if ($action === 'share') {
            $postId = (int) ($_POST['post_id'] ?? 0);
            execute_query('UPDATE community_posts SET share_count = share_count + 1 WHERE id = ?', [$postId]);
            set_flash('success', 'Đã ghi nhận lượt chia sẻ của bạn.');
        }
    } catch (Throwable $exception) {
        set_flash('error', $exception->getMessage());
    }

    redirect('/community.php');
}

$posts = query_all(
    'SELECT p.*, u.name, u.avatar_path,
            (SELECT COUNT(*) FROM community_likes cl WHERE cl.post_id = p.id) AS like_total,
            (SELECT COUNT(*) FROM community_comments cc WHERE cc.post_id = p.id) AS comment_total,
            EXISTS(SELECT 1 FROM community_likes cl2 WHERE cl2.post_id = p.id AND cl2.user_id = ?) AS liked_by_me
     FROM community_posts p
     INNER JOIN users u ON u.id = p.user_id
     ORDER BY p.created_at DESC',
    [(int) ($user['id'] ?? 0)]
);
$commentsByPost = [];
$comments = query_all(
    'SELECT cc.*, u.name FROM community_comments cc INNER JOIN users u ON u.id = cc.user_id ORDER BY cc.created_at DESC LIMIT 50'
);
foreach ($comments as $comment) {
    $commentsByPost[$comment['post_id']][] = $comment;
}
$friends = $user ? query_all(
    'SELECT u.name FROM friendships f INNER JOIN users u ON u.id = f.friend_id WHERE f.user_id = ? AND f.status = ? ORDER BY u.name LIMIT 8',
    [(int) $user['id'], 'accepted']
) : [];
$pageTitle = 'Cộng đồng - musicofeveryone';
require_once __DIR__ . '/includes/header.php';
?>
<section class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
    <?php render_flashes(); ?>
    <div class="grid gap-8 lg:grid-cols-[1.2fr,0.5fr]">
        <div class="space-y-6">
            <div class="rounded-[2rem] bg-white p-6 shadow-lg">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Cộng đồng học viên</p>
                        <h1 class="mt-2 text-3xl font-black text-slate-900">Bảng tin mini-Facebook dành cho người yêu âm nhạc</h1>
                    </div>
                </div>
                <?php if ($user): ?>
                    <form method="post" enctype="multipart/form-data" class="mt-6 space-y-4">
                        <input type="hidden" name="action" value="create">
                        <textarea name="content" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none ring-primary focus:ring" placeholder="Hôm nay bạn đang luyện bài nào?"></textarea>
                        <div class="grid gap-4 md:grid-cols-3">
                            <select name="post_type" class="rounded-2xl border border-slate-200 px-4 py-3">
                                <option value="text">Bài viết văn bản</option>
                                <option value="image">Ảnh</option>
                                <option value="video">Video</option>
                                <option value="emoji">Emoji / cảm xúc</option>
                            </select>
                            <input type="text" name="emoji" value="🎵" class="rounded-2xl border border-slate-200 px-4 py-3" placeholder="Emoji cảm xúc">
                            <input type="file" name="media" accept="image/*,video/*" class="rounded-2xl border border-dashed border-slate-300 px-4 py-3">
                        </div>
                        <button type="submit" class="rounded-full bg-primary px-6 py-3 font-bold text-white shadow-glow">Đăng bài</button>
                    </form>
                <?php else: ?>
                    <div class="mt-6 rounded-2xl bg-violet-50 p-5 text-sm text-primary">Đăng nhập để viết bài, thích và bình luận trong cộng đồng.</div>
                <?php endif; ?>
            </div>

            <?php foreach ($posts as $post): ?>
                <article class="rounded-[2rem] bg-white p-6 shadow-lg">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-xl text-primary">🎶</div>
                            <div>
                                <h3 class="font-bold text-slate-900"><?= e($post['name']); ?></h3>
                                <p class="text-sm text-slate-500"><?= e(format_datetime_vi($post['created_at'])); ?></p>
                            </div>
                        </div>
                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-primary"><?= e(strtoupper($post['post_type'])); ?></span>
                    </div>
                    <div class="mt-5 space-y-4">
                        <p class="whitespace-pre-line text-slate-700"><?= e($post['content']); ?></p>
                        <?php if (!empty($post['emoji'])): ?>
                            <div class="text-4xl"><?= e($post['emoji']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($post['media_path'])): ?>
                            <?php if (str_contains((string) $post['media_path'], '.mp4') || str_contains((string) $post['media_path'], '.mov') || str_contains((string) $post['media_path'], '.webm')): ?>
                                <video controls class="w-full rounded-[1.5rem] bg-slate-900"><source src="<?= e($post['media_path']); ?>"></video>
                            <?php else: ?>
                                <img src="<?= e($post['media_path']); ?>" alt="Bài viết cộng đồng" class="w-full rounded-[1.5rem] object-cover">
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-3 text-sm">
                        <span class="rounded-full bg-slate-100 px-4 py-2 text-slate-600">👍 <?= e((string) $post['like_total']); ?></span>
                        <span class="rounded-full bg-slate-100 px-4 py-2 text-slate-600">💬 <?= e((string) $post['comment_total']); ?></span>
                        <span class="rounded-full bg-slate-100 px-4 py-2 text-slate-600">🔁 <?= e((string) $post['share_count']); ?></span>
                    </div>
                    <?php if ($user): ?>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <form method="post">
                                <input type="hidden" name="action" value="like">
                                <input type="hidden" name="post_id" value="<?= e((string) $post['id']); ?>">
                                <button type="submit" class="rounded-full <?= (int) $post['liked_by_me'] === 1 ? 'bg-primary text-white' : 'bg-violet-50 text-primary'; ?> px-4 py-2 text-sm font-semibold">Thích</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="action" value="share">
                                <input type="hidden" name="post_id" value="<?= e((string) $post['id']); ?>">
                                <button type="submit" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Chia sẻ</button>
                            </form>
                        </div>
                        <form method="post" class="mt-4 flex gap-3">
                            <input type="hidden" name="action" value="comment">
                            <input type="hidden" name="post_id" value="<?= e((string) $post['id']); ?>">
                            <input type="text" name="comment" class="flex-1 rounded-2xl border border-slate-200 px-4 py-3" placeholder="Viết bình luận...">
                            <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Gửi</button>
                        </form>
                    <?php endif; ?>
                    <div class="mt-4 space-y-3">
                        <?php foreach ($commentsByPost[$post['id']] ?? [] as $comment): ?>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                <span class="font-semibold text-slate-900"><?= e($comment['name']); ?>:</span>
                                <?= e($comment['comment']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <aside class="space-y-6">
            <div class="rounded-[2rem] bg-white p-6 shadow-lg">
                <h2 class="text-xl font-black text-slate-900">Bạn bè đang online</h2>
                <div class="mt-5 space-y-4">
                    <?php foreach ($friends as $friend): ?>
                        <div class="flex items-center gap-3">
                            <span class="pulse-dot flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-xl">🎼</span>
                            <div>
                                <p class="font-semibold text-slate-800"><?= e($friend['name']); ?></p>
                                <p class="text-xs text-emerald-600">Đang hoạt động</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$friends): ?>
                        <p class="text-sm text-slate-500">Kết bạn trong cộng đồng để xem ai đang trực tuyến.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="rounded-[2rem] music-gradient p-6 text-white shadow-glow">
                <h3 class="text-xl font-black">Mẹo hôm nay</h3>
                <p class="mt-3 text-sm text-violet-100">Hãy quay video ngắn 60 giây sau mỗi buổi luyện tập để giáo viên nhận xét chính xác hơn.</p>
            </div>
        </aside>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
