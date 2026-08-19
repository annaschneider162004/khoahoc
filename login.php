<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    $user = current_user();
    redirect($user['role'] === 'admin' ? '/admin/dashboard.php' : '/student/dashboard.php');
}

if (is_post()) {
    $email = strtolower(sanitize_text($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $remember = !empty($_POST['remember_me']);

    $user = query_one('SELECT * FROM users WHERE email = ? LIMIT 1', [$email]);
    if (!$user || !password_verify($password, $user['password'])) {
        set_flash('error', 'Email hoặc mật khẩu không đúng.');
    } elseif ((int) $user['locked'] === 1) {
        set_flash('error', 'Tài khoản đã bị khóa.');
    } else {
        login_user($user);
        if ($remember) {
            create_remember_token((int) $user['id']);
        }
        send_notification((int) $user['id'], 'Đăng nhập thành công', 'Chào mừng bạn quay lại với musicofeveryone!', '/student/dashboard.php');
        redirect($user['role'] === 'admin' ? '/admin/dashboard.php' : '/student/dashboard.php');
    }
}

$pageTitle = 'Đăng nhập - musicofeveryone';
require_once __DIR__ . '/includes/header.php';
?>
<section class="mx-auto max-w-5xl px-4 py-16 lg:px-8">
    <div class="grid overflow-hidden rounded-[2rem] bg-white shadow-2xl lg:grid-cols-2">
        <div class="hidden bg-slate-900 p-10 text-white lg:block">
            <h1 class="text-4xl font-black">Chào mừng trở lại với hành trình âm nhạc</h1>
            <p class="mt-5 text-slate-300">Theo dõi bài học, trò chuyện với giáo viên, xem thông báo và khám phá video cộng đồng chỉ trong một nơi.</p>
            <div class="mt-8 grid gap-4">
                <div class="rounded-2xl bg-white/10 p-4">✅ Đồng bộ lịch học và livestream</div>
                <div class="rounded-2xl bg-white/10 p-4">✅ Quản lý chứng chỉ và huy hiệu</div>
                <div class="rounded-2xl bg-white/10 p-4">✅ Nhận thông báo tự động</div>
            </div>
        </div>
        <div class="p-8 lg:p-10">
            <?php render_flashes(); ?>
            <h2 class="text-3xl font-black text-slate-900">Đăng nhập</h2>
            <p class="mt-2 text-sm text-slate-500">Tiếp tục học tập và kết nối với cộng đồng musicofeveryone.</p>
            <form method="post" class="mt-8 space-y-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" value="<?= old('email'); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none ring-primary focus:ring" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Mật khẩu</label>
                    <input type="password" name="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none ring-primary focus:ring" required>
                </div>
                <label class="flex items-center gap-3 text-sm text-slate-600">
                    <input type="checkbox" name="remember_me" value="1" class="rounded border-slate-300 text-primary focus:ring-primary">
                    Ghi nhớ đăng nhập trong 30 ngày
                </label>
                <button type="submit" class="w-full rounded-2xl bg-primary px-6 py-3 font-bold text-white shadow-glow">Đăng nhập</button>
            </form>
            <div class="my-6 grid gap-3 sm:grid-cols-2">
                <button type="button" class="rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700">G tiếp tục với Google</button>
                <button type="button" class="rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700">f tiếp tục với Facebook</button>
            </div>
            <p class="text-sm text-slate-500">Chưa có tài khoản? <a href="/register.php" class="font-semibold text-primary">Đăng ký ngay</a></p>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
