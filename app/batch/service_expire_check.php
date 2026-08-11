<?php

/*
 * 사이트운영 상태 갱신 배치 - 매일 06시대, service_live.status를 만료일 기준으로 단계적으로
 * 내려간다: ACTIVE → SUSPENDED(만료일 당일부터, 관리자 백업/결제 유예) → TERMINATED(유예 15일 경과,
 * 완전 차단). app/batch/batch.php(마더)가 06시 안에서만 매분 require한다 - analy.php와 동일하게
 * 활성 시간대 동안 계속 재시도하는 패턴이라, 처리 대상 없으면 바로 조용히 종료한다.
 *
 * 관리자가 상세화면에서 수동으로 만료일/상태를 바꿀 때 쓰는 규칙
 * (ServiceApplyService::applyExpireDate()/changeStatus())과 동일한 단계 판정과 원격 상태 코드
 * 매핑(USER_CLOSED/ALL_CLOSED)을 배치로 옮긴 버전이다.
 *
 * 두 단계 모두 큐 적재가 실패하면 로컬 status는 그대로 두고 다음 실행 때 다시 픽업되도록 한다 -
 * 큐가 안 나갔는데 로컬만 앞서가면 재시도 대상에서 빠져버리므로 큐 성공을 먼저 확인한 뒤에만
 * 로컬 status를 갱신한다.
 *
 * CI4 부팅 방식은 queue_push.php와 동일 (bootConsole() - 이유는 그 파일 주석 참고).
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

const SUSPEND_GRACE_DAYS = 15;

$db = db_connect('default');
$serviceLiveModel = model('App\Models\ServiceLiveModel', true, $db);
$queueModel = model('App\Models\QueueModel', true, $db);

// 1단계: 만료일이 된 정상(ACTIVE) → 유예(SUSPENDED). 사용자웹만 차단, 중개사 관리자는 로그인 가능.
$toSuspend = $serviceLiveModel->get_expired_active();
foreach ($toSuspend as $row) {
    $payload = ['fid' => $row['fid'], 'status' => 'USER_CLOSED'];
    $ok = $queueModel->sql_save($row['live_no'], $row['server_no'], $row['fid'], $payload, 'status');

    if (!$ok) {
        log_message('error', "[service_expire_check] live_no={$row['live_no']} fid={$row['fid']} 유예 전환 큐 적재 실패 - 다음 실행 때 재시도");
        continue;
    }

    $serviceLiveModel->updateStatus($row['live_no'], 'SUSPENDED');
    log_message('info', "[service_expire_check] live_no={$row['live_no']} fid={$row['fid']} 유예 전환 완료 (ACTIVE → SUSPENDED)");
}

// 2단계: 유예 시작(suspended_at) 후 15일 지난 건 → 종료(TERMINATED). 관리자·사용자 전체 차단.
$toTerminate = $serviceLiveModel->get_suspended_expired(SUSPEND_GRACE_DAYS);
foreach ($toTerminate as $row) {
    $payload = ['fid' => $row['fid'], 'status' => 'ALL_CLOSED'];
    $ok = $queueModel->sql_save($row['live_no'], $row['server_no'], $row['fid'], $payload, 'status');

    if (!$ok) {
        log_message('error', "[service_expire_check] live_no={$row['live_no']} fid={$row['fid']} 종료 전환 큐 적재 실패 - 다음 실행 때 재시도");
        continue;
    }

    $serviceLiveModel->updateStatus($row['live_no'], 'TERMINATED');
    log_message('info', "[service_expire_check] live_no={$row['live_no']} fid={$row['fid']} 종료 전환 완료 (SUSPENDED[{$row['suspended_at']}] → TERMINATED)");
}
