<?php

/*
 |--------------------------------------------------------------------------
 | ERROR DISPLAY
 |--------------------------------------------------------------------------
 | Don't show ANY in production environments. Instead, let the system catch
 | it and display a generic error message.
 |
 | If you set 'display_errors' to '1', CI4's detailed error report will show.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
// If you want to suppress more types of errors.
// error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

/*
 |--------------------------------------------------------------------------
 | DEBUG MODE
 |--------------------------------------------------------------------------
 | Debug mode is an experimental flag that can allow changes throughout
 | the system. It's not widely used currently, and may not survive
 | release of the framework.
 */
defined('CI_DEBUG') || define('CI_DEBUG', false);

require_once __DIR__ . '/common.php';

// SITE는 도메인별로 갈린다 - ad.bomul.ai.kr이면 site_ad.php, 그 외(www/apex)면 site_www.php.
$host = $_SERVER['HTTP_HOST'] ?? '';
require_once __DIR__ . '/' . ($host === 'ad.bomul.ai.kr' ? 'site_ad.php' : 'site_www.php');
require_once __DIR__ . '/site_common.php';
