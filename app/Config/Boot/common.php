<?php

/*
 * 환경(development/production/testing)과 무관하게 항상 필요한 앱 공통 상수들.
 * 원래 development.php에만 있었는데, production 환경으로 전환하면 이 파일이 아예 안 실행되어
 * LOG_DIR/SITE/RS_CSS 등이 전부 "Undefined constant"로 터졌다 - 그래서 각 Boot/{env}.php가
 * 공통으로 require하는 파일로 분리했다.
 *
 * loadDotEnv() 이후, 각 Boot/{env}.php 실행 시점에 require되므로 getenv()/env()로 .env 값을
 * 정상적으로 읽을 수 있다(Config/Constants.php는 이보다 먼저 실행되어 .env를 못 읽는다).
 */

$root_path = dirname(__DIR__, 3);

define('ROOT_PATH', $root_path);
define('APP', ROOT_PATH.'/app');
define('VIEW', APP.'/Views');
define('APP_PDS', ROOT_PATH.'/public/pds/');

define('SITE', getenv("site_url"));
define('SITE_PDS', SITE.'/pds/');
define('COMMON_PDS', 'http://api.bomul.ai.kr/pds/');
define('Admin_SITE', SITE.'/Admin');
define('LOGIN_SERVER', 'https://');
define('RS', SITE.'/resources');
define('RS_IMG', SITE.'/resources/img');
define('RS_CSS', SITE.'/resources/css');
define('RS_JS', SITE.'/resources/js');
define('RS_TH', RS.'/third_party');
define('PDS_URL', SITE.'pds/');
define('FRESH_TIME', date("YmdHis"));

define('AES_KEY', getenv("AES_KEY"));
define('LOG_DIR', getenv("LOG_DIR"));

if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'development'));
}
