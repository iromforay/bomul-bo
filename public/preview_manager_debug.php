<?php
// Temporary standalone harness to preview the Manager CRUD screens without booting CodeIgniter/DB.
// Not part of the app - delete after visual check.

define('RS_IMG', '/resources/img');
define('RS_CSS', '/resources/css');
define('RS_JS', '/resources/js');
define('RS_TH', '/resources/third_party');
define('FRESH_TIME', date('YmdHis'));
define('SHOP_CODE', 'n/a');
define('Admin_SITE', '');

$common = ['title' => '관리자 관리'];
$active = 'setup';

// list.php calls session()->getFlashdata() - CI4 isn't booted here, so stub it.
if (!function_exists('session')) {
    function session() {
        return new class {
            public function getFlashdata($key) { return null; }
        };
    }
}

function view_part($file, $vars) {
    extract($vars);
    ob_start();
    include $file;
    return ob_get_clean();
}

$base = __DIR__ . '/../app/Views';

$page = $_GET['page'] ?? 'list';

if ($page === 'list') {
    $list = [
        ['manager_no' => 2, 'manager_id' => 'foray', 'manager_name' => '박포레이', 'use_yn' => 'Y', 'reg_date' => '2026-08-01 10:00:00'],
        ['manager_no' => 1, 'manager_id' => 'admin', 'manager_name' => '관리자', 'use_yn' => 'N', 'reg_date' => '2026-07-20 09:00:00'],
    ];
    $content = view_part($base . '/Admin/Manager/list.php', ['list' => $list]);
} elseif ($page === 'write') {
    $content = view_part($base . '/Admin/Manager/write.php', []);
} else {
    $row = ['manager_no' => 2, 'manager_id' => 'foray', 'manager_name' => '박포레이', 'use_yn' => 'Y'];
    $content = view_part($base . '/Admin/Manager/modify.php', ['row' => $row]);
}

$header = view_part($base . '/common/admin_header.php', ['common' => $common]);
$header_menu = view_part($base . '/common/admin_menu.php', ['active' => $active, 'common' => $common]);
$footer = view_part($base . '/common/admin_footer.php', ['common' => $common]);

include $base . '/layout/admin_default.php';
