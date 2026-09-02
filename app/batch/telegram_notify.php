<?php

/*
 * 텔레그램 알림 배치(#38) - 회원가입/서비스신청/Q&A 신규 등록을 텔레그램으로 알린다.
 * batch.php가 09~18시 사이에만 매분 require한다.
 *
 * 조회 구간을 시간대별로 다르게 잡아서 "업무시간엔 즉시, 그 외엔 다음날 9시에 몰아서"
 * 요구사항을 커서/큐 테이블 없이 그때그때 계산한다:
 *   - 09시 정각(딱 한 번, i==='00'일 때만): 전날 18시 ~ 당일 9시 누적분을 한번에 캐치업
 *   - 그 외 09~18시(09시 01~59분 포함, 매분): 직전 1분치만 - 매분 반복 호출되니 사실상 즉시 알림
 *     (09시 정각에 캐치업으로 이미 to=09:00까지 처리했으므로 09:01 첫 폴링은 자연히 09:00~09:01만 봄)
 *
 * CI4 부팅 방식은 queue_push.php와 동일(bootConsole()).
 */

define('FCPATH', __DIR__ . '/../../public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Boot.php';

require $paths->systemDirectory . '/Config/DotEnv.php';
(new \CodeIgniter\Config\DotEnv($paths->envDirectory ?? $paths->appDirectory . '/../'))->load();
if (!defined('ENVIRONMENT')) {
    $env = $_ENV['CI_ENVIRONMENT'] ?? $_SERVER['CI_ENVIRONMENT'] ?? getenv('CI_ENVIRONMENT') ?: 'production';
    define('ENVIRONMENT', $env);
}

\CodeIgniter\Boot::bootConsole($paths);

date_default_timezone_set("Asia/Seoul");

$h = (int) date('H');
$i = date('i');

if ($h === 9 && $i === '00') {
    $from = date('Y-m-d 18:00:00', strtotime('-1 day'));
    $to   = date('Y-m-d 09:00:00');
} else {
    $to   = date('Y-m-d H:i:00');
    $from = date('Y-m-d H:i:00', strtotime('-1 minute'));
}

$service = new \App\Service\TelegramNotifyService();
$service->runAll($from, $to);
