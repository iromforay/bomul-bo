<?php
define('RS_IMG', '/resources/img');
define('RS_CSS', '/resources/css');
define('RS_JS', '/resources/js');
define('RS_TH', '/resources/third_party');
define('FRESH_TIME', date('YmdHis'));
define('Admin_SITE', '');

$base = __DIR__ . '/../app/Views';

if (!function_exists('session')) {
    function session() {
        return new class {
            public function getFlashdata($key) {
                if ($key === 'msg') return '고객 등록에 성공했습니다.';
                return null;
            }
        };
    }
}
if (!function_exists('view')) {
    function view($name, $vars = []) {
        global $base;
        extract($vars);
        ob_start();
        include $base . '/' . $name . '.php';
        return ob_get_clean();
    }
}

function view_part($file, $vars) {
    extract($vars);
    ob_start();
    include $file;
    return ob_get_clean();
}

$common = ['title' => '고객 목록'];
$active = 'customer';

$list = [];
$content = view_part($base . '/Admin/Customer/list.php', ['list' => $list]);
$header = view_part($base . '/common/admin_header.php', ['common' => $common]);
$header_menu = view_part($base . '/common/admin_menu.php', ['active' => $active, 'common' => $common]);
$footer = view_part($base . '/common/admin_footer.php', ['common' => $common]);

include $base . '/layout/admin_default.php';
