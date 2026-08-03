<?php

/*
 |--------------------------------------------------------------------------
 | ERROR DISPLAY
 |--------------------------------------------------------------------------
 | In development, we want to show as many errors as possible to help
 | make sure they don't make it to production. And save us hours of
 | painful debugging.
 |
 | If you set 'display_errors' to '1', CI4's detailed error report will show.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

/*
 |--------------------------------------------------------------------------
 | DEBUG BACKTRACES
 |--------------------------------------------------------------------------
 | If true, this constant will tell the error screens to display debug
 | backtraces along with the other error information. If you would
 | prefer to not see this, set this value to false.
 */
defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', true);

/*
 |--------------------------------------------------------------------------
 | DEBUG MODE
 |--------------------------------------------------------------------------
 | Debug mode is an experimental flag that can allow changes throughout
 | the system. This will control whether Kint is loaded, and a few other
 | items. It can always be used within your own application too.
 */
defined('CI_DEBUG') || define('CI_DEBUG', true);
$root_path = str_replace("/app/Config/Boot/development.php","",__FILE__);


define('ROOT_PATH', $root_path);
define('APP', ROOT_PATH.'/app');
define('VIEW', APP.'/Views');
define('APP_PDS', ROOT_PATH.'/public/pds/');


//define('ATH_SYS_SESSION', ROOT_PATH . '/temp/session');
//define('ATH_LOG', '/home/eduline/log/help');
//define('ATH_SYS_LOG', ROOT_PATH . '/temp/log/');
//define('ATH_SYS_PDS', ROOT_PATH . '/public_html/pds');
//define('SITE',getenv("site_url"));
define('SITE',getenv("site_url"));
define('SITE_PDS', SITE.'/pds/');
define('COMMON_PDS', 'http://api.bomul.co.kr/pds/');
define('Admin_SITE', SITE.'/Admin');
define('LOGIN_SERVER', 'https://');
define('RS', SITE.'/resources');
define('RS_IMG', SITE.'/resources/img');
define('RS_CSS', SITE.'/resources/css');
define('RS_JS', SITE.'/resources/js');
define('RS_TH', RS.'/third_party');
define('PDS_URL', SITE.'pds/');
define('FRESH_TIME', date("YmdHis"));

define('AES_KEY',getenv("AES_KEY"));
define('LOG_DIR',getenv("LOG_DIR"));
if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'development'));
}
//define('ENVIRONMENT',getenv('CI_ENVIRONMENT','development'));
