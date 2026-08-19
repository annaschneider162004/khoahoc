<?php
require_once __DIR__ . '/includes/functions.php';
logout_user();
set_flash('success', 'Bạn đã đăng xuất thành công.');
redirect('/login.php');
