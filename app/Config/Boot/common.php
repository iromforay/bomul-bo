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

// "//host" 형태(프로토콜 상대경로)는 CI4의 Config\App::$baseURL 검증을 통과 못 한다
// ("is not a valid URL" ConfigException) - 스킴을 붙여줘야 하는데, https로 고정해버리면 80포트(http)로
// 들어온 요청에서도 CSS/JS/이미지가 전부 https://를 물어서 그 도메인 443이 안 살아있으면 리소스가
// 통째로 깨진다. 그래서 지금 요청이 실제로 http/https 중 뭔지 감지해서 그 스킴을 그대로 따라간다.
// site_ad.php/site_www.php에서 SITE 정의할 때 사용.
function bomul_current_scheme(): string
{
    $https = $_SERVER['HTTPS'] ?? '';
    if ($https !== '' && strtolower($https) !== 'off') {
        return 'https';
    }
    // nginx 등 리버스 프록시 뒤에서 SSL을 종료하는 구성 대비
    if (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
        return 'https';
    }
    return 'http';
}

function bomul_normalize_site_url(string $raw): string
{
    if (preg_match('#^https?://#i', $raw)) {
        return $raw; // 이미 스킴이 명시돼 있으면 .env 값을 그대로 존중
    }
    if (str_starts_with($raw, '//')) {
        return bomul_current_scheme() . ':' . $raw;
    }
    return $raw;
}

$root_path = dirname(__DIR__, 3);

define('ROOT_PATH', $root_path);
define('APP', ROOT_PATH.'/app');
define('VIEW', APP.'/Views');
define('APP_PDS', ROOT_PATH.'/public/pds/');

// SITE(및 RS_CSS 등 파생 상수)는 여기서 정의하지 않는다 - www/ad가 코드베이스 하나를 같이 쓰는데
// 도메인마다 값이 달라야 해서, Boot/{env}.php가 site_ad.php/site_www.php 중 하나를 골라
// require한 뒤 site_common.php로 파생 상수를 채우는 방식으로 분리했다 (site_ad.php/site_www.php/
// site_common.php 참고).
//
// Admin 백오피스는 어느 도메인(www/ad)에서 실행되든 항상 같은 고정 주소를 가리켜야 한다
// (예: www에서 "관리자 로그인" 링크를 걸 때도 ad.bomul.ai.kr로 가야 함) - 그래서 SITE와 달리
// 도메인 분기 없이 .env의 ad_site를 그대로 고정 사용.
define('Admin_SITE', bomul_normalize_site_url(getenv('ad_site')));

// bo.bomul.ai.kr(중개사 로그인) - ad에서 만든 자동로그인 session_token을 이 주소로 GET 전송한다.
define('BO_SITE', bomul_normalize_site_url(getenv('bo_site')));

define('AES_KEY', getenv("AES_KEY"));
define('LOG_DIR', getenv("LOG_DIR"));

if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'development'));
}
