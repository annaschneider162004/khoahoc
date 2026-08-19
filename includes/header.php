<?php
require_once __DIR__ . '/functions.php';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'musicofeveryone'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6D28D9',
                        secondary: '#7C3AED'
                    },
                    boxShadow: {
                        glow: '0 20px 50px rgba(109, 40, 217, 0.18)'
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-slate-50 text-slate-800">
<header class="sticky top-0 z-40 border-b border-slate-100 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 lg:px-8">
        <a href="/index.php" class="flex items-center gap-3 text-xl font-black text-primary">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg">♪</span>
            <span>musicofeveryone</span>
        </a>
        <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
            <a class="hover:text-primary" href="/index.php">Trang chủ</a>
            <a class="hover:text-primary" href="/community.php">Cộng đồng</a>
            <a class="hover:text-primary" href="/livestream.php">Livestream</a>
            <a class="hover:text-primary" href="/contact.php">Liên hệ</a>
        </nav>
        <div class="flex items-center gap-3">
            <?php if ($user): ?>
                <a href="/notifications.php" class="relative hidden rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 md:inline-flex">
                    Thông báo
                    <span id="notificationBadge" class="ml-2 inline-flex min-w-6 justify-center rounded-full bg-primary px-2 py-0.5 text-xs text-white"><?= notification_count((int) $user['id']); ?></span>
                </a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="/admin/dashboard.php" class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-white shadow-glow">Quản trị</a>
                <?php else: ?>
                    <a href="/student/dashboard.php" class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-white shadow-glow">Bảng điều khiển</a>
                <?php endif; ?>
                <a href="/logout.php" class="rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700">Đăng xuất</a>
            <?php else: ?>
                <a href="/login.php" class="rounded-full border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700">Đăng nhập</a>
                <a href="/register.php" class="rounded-full bg-primary px-5 py-2 text-sm font-semibold text-white shadow-glow">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main>
