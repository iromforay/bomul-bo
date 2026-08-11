<?php

/*
 * 요청 수집 배치 - service_live.status='REQUEST'인 건(관리자가 상세화면에서 상태를 "요청"으로
 * 바꾼 건)을 모아 queue에 action='new'로 적재하고, 적재 직후 status를 ACTIVE로 되돌린다
 * (재적재 방지 - 큐 자체의 성공/실패는 여느 때처럼 Admin\Callback::postComplete()가
 * install_status로 보고받는다). app/batch/batch.php(마더)가 매분 require한다.
 *
 * app/Commands/ServiceRequestCollect.php(`spark service:collect-request`)와 로직은 같다 -
 * 그건 수동/디버그용으로 남겨두고, 실제 crontab은 이제 batch.php 하나만 호출하므로 여기서
 * 같은 처리를 spark 없이(bootConsole()만 써서 더 가볍게) 반복한다.
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

$db = db_connect('default');
$serviceLiveModel = model('App\Models\ServiceLiveModel', true, $db);
$queueModel = model('App\Models\QueueModel', true, $db);

$rows = $serviceLiveModel->get_request_pending();
if (empty($rows)) {
    return;
}

foreach ($rows as $row) {
    $payload = [
        'fid'          => $row['fid'],
        'license_key'  => $row['license_key'],
        'product_code' => $row['product_code'],
        'company_name' => $row['company_name'],
        'ceo_name'     => $row['ceo_name'],
        'biz_no'       => $row['biz_no'],
        'license_no'   => $row['license_no'],
        'customer_id'  => $row['customer_id'],
        'customer_pw'  => $row['customer_pw'],
    ];

    $ok = $queueModel->sql_save($row['live_no'], $row['server_no'], $row['fid'], $payload, 'new');
    if (!$ok) {
        log_message('error', "[service_request_collect] live_no={$row['live_no']} 큐 적재 실패 - 다음 실행 때 재시도");
        continue;
    }

    $serviceLiveModel->updateInstallStatus($row['live_no'], 'PENDING');
    $serviceLiveModel->updateStatus($row['live_no'], 'ACTIVE');
    log_message('info', "[service_request_collect] live_no={$row['live_no']} 큐 적재 완료 (fid={$row['fid']})");
}
