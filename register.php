<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect('/student/dashboard.php');
}

if (is_post()) {
    $name = sanitize_text($_POST['name'] ?? '');
    $email = strtolower(sanitize_text($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $role = sanitize_text($_POST['role'] ?? 'student');

    if ($name === '' || $email === '' || $password === '') {
        set_flash('error', 'Vui lòng điền đầy đủ thông tin.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Email không hợp lệ.');
    } elseif (!in_array($role, ['student', 'teacher'], true)) {
        set_flash('error', 'Vai trò không hợp lệ.');
    } elseif (query_one('SELECT id FROM users WHERE email = ? LIMIT 1', [$email])) {
        set_flash('error', 'Email đã được sử dụng.');
    } else {
        execute_query(
            'INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())',
            [$name, $email, password_hash($password, PASSWORD_BCRYPT), $role]
        );
        $newUser = query_one('SELECT * FROM users WHERE email = ? LIMIT 1', [$email]);
        if ($newUser) {
            login_user($newUser);
            set_flash('success', 'Đăng ký thành công, chào mừng bạn đến với musicofeveryone!');
            redirect($role === 'teacher' ? '/community.php' : '/student/dashboard.php');
        }
    }
}

$pageTitle = 'Đăng ký - musicofeveryone';
require_once __DIR__ . '/includes/header.php';
?>
<section class="mx-auto max-w-6xl px-4 py-16 lg:px-8">
    <div class="grid overflow-hidden rounded-[2rem] bg-white shadow-2xl lg:grid-cols-2">
        <div class="music-gradient p-10 text-white">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-violet-100">Tham gia ngay</p>
            <h1 class="mt-4 text-4xl font-black">Tạo tài khoản học nhạc hiện đại</h1>
            <p class="mt-5 text-violet-50">Quản lý lộ trình học, nộp video, nhận phản hồi từ giáo viên và kết nối cộng đồng âm nhạc Việt Nam.</p>
            <div class="mt-8 space-y-4 text-sm">
                <div class="rounded-2xl bg-white/10 p-4">🎯 Theo dõi tiến độ từng khóa học</div>
                <div class="rounded-2xl bg-white/10 p-4">📹 Gửi video thực hành nhận chấm điểm</div>
                <div class="rounded-2xl bg-white/10 p-4">💬 Chat trực tiếp với giáo viên</div>
            </div>
        </div>
        <div class="p-8 lg:p-10">
            <?php render_flashes(); ?>
            <form method="post" class="space-y-5">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Họ và tên</label>
                    <input type="text" name="name" value="<?= old('name'); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none ring-primary focus:ring" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" value="<?= old('email'); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none ring-primary focus:ring" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Mật khẩu</label>
                    <input type="password" name="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none ring-primary focus:ring" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Vai trò</label>
                    <select name="role" class="w-full rounded-2xl border border-slate-200 px-4 py-3 outline-none ring-primary focus:ring">
                        <option value="student">Học viên</option>
                        <option value="teacher">Giáo viên</option>
                    </select>
                </div>
                <button type="submit" class="w-full rounded-2xl bg-primary px-6 py-3 font-bold text-white shadow-glow">Đăng ký</button>
            </form>
            <div class="my-6 grid gap-3 sm:grid-cols-2">
                <button type="button" class="rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700">G tiếp tục với Google</button>
                <button type="button" class="rounded-2xl border border-slate-200 px-4 py-3 font-semibold text-slate-700">f tiếp tục với Facebook</button>
            </div>
            <p class="text-sm text-slate-500">Đã có tài khoản? <a href="/login.php" class="font-semibold text-primary">Đăng nhập ngay</a></p>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
