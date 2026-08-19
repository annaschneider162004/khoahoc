<?php
require_once __DIR__ . '/../includes/functions.php';
if (current_user() && current_user()['role'] === 'admin') {
    redirect('/admin/dashboard.php');
}

if (is_post()) {
    $email = strtolower(sanitize_text($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $admin = query_one('SELECT * FROM users WHERE email = ? AND role = ? LIMIT 1', [$email, 'admin']);
    if (!$admin || !password_verify($password, $admin['password'])) {
        set_flash('error', 'Thông tin quản trị không chính xác.');
    } else {
        login_user($admin);
        redirect('/admin/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập quản trị</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="music-gradient flex min-h-screen items-center justify-center px-4 py-12">
    <div class="w-full max-w-md rounded-[2rem] bg-white p-8 shadow-2xl">
        <div class="text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-violet-100 text-3xl text-primary">🎼</div>
            <h1 class="mt-5 text-3xl font-black text-slate-900">Admin login</h1>
            <p class="mt-2 text-sm text-slate-500">Đăng nhập để quản lý nền tảng musicofeveryone</p>
        </div>
        <div class="mt-6"><?php render_flashes(); ?></div>
        <form method="post" class="space-y-5">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Email quản trị</label>
                <input type="email" name="email" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Mật khẩu</label>
                <input type="password" name="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
            </div>
            <button type="submit" class="w-full rounded-2xl bg-primary px-6 py-3 font-bold text-white shadow-glow">Đăng nhập quản trị</button>
        </form>
    </div>
</body>
</html>
