<?php
require_once __DIR__ . '/../includes/functions.php';
require_login('student');
$user = current_user();
$teachers = student_teachers((int) $user['id']);
$receiverId = (int) ($_GET['receiver_id'] ?? ($teachers[0]['id'] ?? 0));
$receiver = $receiverId ? query_one('SELECT id, name FROM users WHERE id = ? AND role = ? LIMIT 1', [$receiverId, 'teacher']) : null;

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    if (!$receiver) {
        echo json_encode(['current_user_id' => (int) $user['id'], 'messages' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $messages = query_all(
        'SELECT id, sender_id, receiver_id, message, created_at
         FROM chat_messages
         WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
         ORDER BY created_at ASC',
        [(int) $user['id'], $receiverId, $receiverId, (int) $user['id']]
    );
    execute_query('UPDATE chat_messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?', [(int) $user['id'], $receiverId]);
    echo json_encode([
        'current_user_id' => (int) $user['id'],
        'messages' => array_map(static fn(array $message): array => [
            'id' => (int) $message['id'],
            'sender_id' => (int) $message['sender_id'],
            'receiver_id' => (int) $message['receiver_id'],
            'message' => e($message['message']),
            'created_at' => format_datetime_vi($message['created_at']),
        ], $messages),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (is_post() && $receiver) {
    $message = sanitize_text($_POST['message'] ?? '');
    if ($message !== '') {
        execute_query('INSERT INTO chat_messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())', [(int) $user['id'], $receiverId, $message]);
        send_notification($receiverId, 'Tin nhắn mới', $user['name'] . ' vừa gửi cho bạn một tin nhắn.', '/student/chat.php?receiver_id=' . $user['id']);
    }
    redirect('/student/chat.php?receiver_id=' . $receiverId);
}

$messages = $receiver ? query_all(
    'SELECT * FROM chat_messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC',
    [(int) $user['id'], $receiverId, $receiverId, (int) $user['id']]
) : [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat với giáo viên</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-slate-100">
<div class="flex min-h-screen flex-col lg:flex-row">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="flex-1 p-6 lg:p-8">
        <div class="grid gap-8 xl:grid-cols-[0.38fr,0.62fr]">
            <aside class="rounded-[2rem] bg-white p-6 shadow-lg">
                <h1 class="text-2xl font-black text-slate-900">Giáo viên của bạn</h1>
                <div class="mt-5 space-y-3">
                    <?php foreach ($teachers as $teacher): ?>
                        <a href="/student/chat.php?receiver_id=<?= e((string) $teacher['id']); ?>" class="flex items-center gap-3 rounded-2xl <?= (int) $teacher['id'] === $receiverId ? 'bg-violet-50 text-primary' : 'bg-slate-50 text-slate-700'; ?> px-4 py-3 font-semibold">
                            <span class="text-2xl">🎼</span>
                            <span><?= e($teacher['name']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>
            <section class="rounded-[2rem] bg-white p-6 shadow-lg">
                <div class="border-b border-slate-100 pb-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-primary">Chat 1-1</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900"><?= e($receiver['name'] ?? 'Chưa có giáo viên'); ?></h2>
                </div>
                <div id="chatMessages" data-chat-poll data-chat-receiver="<?= e((string) $receiverId); ?>" class="custom-scrollbar mt-6 flex h-[420px] flex-col gap-4 overflow-y-auto rounded-[1.5rem] bg-slate-50 p-4">
                    <?php foreach ($messages as $message): ?>
                        <div class="flex <?= (int) $message['sender_id'] === (int) $user['id'] ? 'justify-end' : 'justify-start'; ?>">
                            <div class="max-w-xl rounded-2xl px-4 py-3 text-sm shadow <?= (int) $message['sender_id'] === (int) $user['id'] ? 'bg-primary text-white' : 'bg-white text-slate-700'; ?>">
                                <p><?= e($message['message']); ?></p>
                                <p class="mt-2 text-[11px] <?= (int) $message['sender_id'] === (int) $user['id'] ? 'text-violet-100' : 'text-slate-400'; ?>"><?= e(format_datetime_vi($message['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($receiver): ?>
                    <form method="post" class="mt-5 flex gap-3">
                        <input type="text" name="message" class="flex-1 rounded-2xl border border-slate-200 px-4 py-3" placeholder="Nhập tin nhắn...">
                        <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Gửi</button>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>
