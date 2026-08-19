<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$keys = [
    'contact_phone' => 'Hotline',
    'contact_email' => 'Email liên hệ',
    'contact_address' => 'Địa chỉ',
    'working_hours' => 'Giờ làm việc',
    'map_embed_url' => 'Google Maps embed URL',
    'map_lat' => 'Google Maps Latitude',
    'map_lng' => 'Google Maps Longitude',
    'facebook_url' => 'Facebook URL',
    'youtube_url' => 'YouTube URL',
    'tiktok_url' => 'TikTok URL',
];
if (is_post()) {
    foreach ($keys as $key => $label) {
        $value = sanitize_text($_POST[$key] ?? '');
        execute_query('INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()', [$key, $value]);
    }
    set_flash('success', 'Đã cập nhật cấu hình website.');
    redirect('/admin/settings.php');
}
$settings = fetch_settings();
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Cài đặt</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="/assets/css/style.css"></head>
<body class="bg-slate-100"><div class="flex min-h-screen flex-col lg:flex-row"><?php require __DIR__ . '/../includes/admin_sidebar.php'; ?><div class="flex-1 p-6 lg:p-8"><?php render_flashes(); ?><div class="rounded-[2rem] bg-white p-6 shadow-lg"><h1 class="text-3xl font-black text-slate-900">Cài đặt hệ thống</h1><form method="post" class="mt-6 grid gap-5 md:grid-cols-2"><?php foreach ($keys as $key => $label): ?><div class="<?= $key === 'map_embed_url' || $key === 'contact_address' ? 'md:col-span-2' : ''; ?>"><label class="mb-2 block text-sm font-semibold text-slate-700"><?= e($label); ?></label><?php if ($key === 'map_embed_url' || $key === 'contact_address'): ?><textarea name="<?= e($key); ?>" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3"><?= e($settings[$key] ?? ''); ?></textarea><?php else: ?><input type="text" name="<?= e($key); ?>" value="<?= e($settings[$key] ?? ''); ?>" class="w-full rounded-2xl border border-slate-200 px-4 py-3"><?php endif; ?></div><?php endforeach; ?><div class="md:col-span-2"><button type="submit" class="rounded-full bg-primary px-6 py-3 font-semibold text-white">Lưu cài đặt</button></div></form></div></div></div></body></html>
