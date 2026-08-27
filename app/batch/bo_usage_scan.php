<?php

/*
 * bo(테넌트 백오피스) 사용 현황 배치(#37) - "매일 배치가 전일 작업 현황을 가져오도록" 요청대로,
 * 각 테넌트의 {fid} DB(basic/menu/img/add_menu, post/property/note)와 중앙 login_session에 직접
 * 붙어 "어제 하루 동안" 로그인했는지/기본세팅을 손댔는지/컨텐츠를 몇 건 등록했는지를 스캔해서
 * bo_usage_daily에 날짜별 로그로 저장한다(#36 visit_stat_daily와 동일한 성격).
 *
 * API 통신(bomul-api 경유)이 아니라 이 서버가 직접 DB를 읽는 방식 - 극초기엔 테넌트 DB가 bomul-bo와
 * 같은 MySQL 호스트에 있어 원격 API가 필요 없다는 판단(BoUsageScanService 주석 참고). 테넌트 수가
 * 늘어나 별도 호스트로 흩어지면 이 배치는 다시 설계해야 한다.
 *
 * 하루 1번(03시대, #36의 02시와 안 겹치는 시간대)만 돈다 - "전일치" 집계라 당일 데이터는 어차피
 * 다음날에나 확정되고, 테넌트마다 DB 커넥션을 새로 여는 작업이라 자주 돌릴 이유도 없음.
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

$statDate = date('Y-m-d', strtotime('-1 day'));

$service = new \App\Service\BoUsageScanService();
[$done, $failed] = $service->runForDate($statDate);

if ($failed > 0) {
    log_message('error', "[bo_usage_scan] {$statDate} 완료 - 성공 {$done}건, 실패 {$failed}건(DB 연결/쿼리 실패 - 다음 실행 때 재시도)");
} else {
    log_message('info', "[bo_usage_scan] {$statDate} 완료 - {$done}건 반영");
}
