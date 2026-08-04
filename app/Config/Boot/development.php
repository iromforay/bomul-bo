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

// Admin_SITE/LOG_DIR 등 도메인 무관 공통 상수는 common.php 한 곳에서만 정의한다 - 예전엔 여기 따로
// 복붙돼 있었는데 common.php 쪽 값과 어긋나기 시작해서(COMMON_PDS/Admin_SITE 등) 헷갈렸다.
require_once __DIR__ . '/common.php';

// SITE는 도메인별로 갈린다 - ad.bomul.ai.kr이면 site_ad.php, 그 외(www/apex)면 site_www.php.
$host = $_SERVER['HTTP_HOST'] ?? '';
require_once __DIR__ . '/' . ($host === 'ad.bomul.ai.kr' ? 'site_ad.php' : 'site_www.php');
require_once __DIR__ . '/site_common.php';
