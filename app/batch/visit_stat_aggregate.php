<?php

/*
 * 방문자 집계(#36) 일별 통계 배치 - visit_log/visit_conversion을 검색어/유입경로별로 집계해서
 * visit_stat_daily에 적재한다. batch.php가 10분 간격(매시 정각/10/20/30/40/50)으로 호출 -
 * "당일"치를 매번 다시 집계해서 거의 실시간에 가깝게 갱신한다(완전 실시간 카운터는 아님 - 여전히
 * 배치/UPSERT 방식이라는 원래 설계 그대로, 주기만 하루 1번 → 10분마다로 당김).
 *
 * 자정 시간대(00시)에는 "어제"치도 한 번 더 같이 돈다 - 마지막 10분 구간(23:50~24:00)이 자정을
 * 넘기며 "오늘" 집계로 넘어가버려 어제 몫에서 누락되는 걸 방지하기 위함.
 *
 * VisitStatDailyModel::sql_upsert()가 ON DUPLICATE KEY UPDATE라 재실행해도 안전(멱등) - 10분마다
 * 다시 돌아도 결과가 누적되지 않고 최신 집계로 덮어써질 뿐이다.
 *
 * CI4 부팅 방식은 queue_push.php/service_expire_check.php와 동일 (bootConsole() - 그 파일 주석 참고).
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

$targetDates = [date('Y-m-d')];
if (date('H') === '00') {
    $targetDates[] = date('Y-m-d', strtotime('-1 day')); // 자정 시간대에만 전날치 마감 재집계
}

$service = new \App\Service\VisitService();

foreach ($targetDates as $statDate) {
    [$done, $failed] = $service->aggregateDaily($statDate);

    if ($failed > 0) {
        log_message('error', "[visit_stat_aggregate] {$statDate} 집계 완료 - 성공 {$done}건, 실패 {$failed}건(재시도 필요)");
    } else {
        log_message('info', "[visit_stat_aggregate] {$statDate} 집계 완료 - {$done}건 반영");
    }
}
