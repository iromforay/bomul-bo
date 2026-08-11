<?php

/*
 * 큐 push 배치 - queue의 PENDING 건을 배정된 운영서버(server_list.receiver_url)로
 * fire-and-forget push하고 SENT로 마킹한다. app/batch/batch.php(마더)가 매분 require한다.
 * 완료 여부는 여기서 기다리지 않는다 - 운영서버가 처리 끝나고 Admin\Callback::postComplete()로
 * 직접 보고한다 (bomul-api의 app/batch/setting_callback.php와 동일한 fire-and-forget 패턴).
 *
 * app/Commands/QueuePush.php(`spark queue:push`)와 로직은 같다 - 그건 수동/디버그용으로 남겨두고,
 * 실제 crontab은 이제 batch.php 하나만 호출하므로 여기서 같은 처리를 spark 없이(bootConsole()만
 * 써서 더 가볍게) 반복한다.
 *
 * CI4 부팅 방식은 bootConsole() - spark가 쓰는 bootSpark()보다 가벼움. bootConsole()엔
 * bootWeb()/bootSpark()에 있는 defineEnvironment() 호출이 빠져있어서, ENVIRONMENT 상수가
 * 정의되기 전에 loadEnvironmentBootstrap()이 그 상수를 참조해 죽는 프레임웍 버그가 있다.
 * 그래서 .env를 먼저 직접 로드해 CI_ENVIRONMENT 기준으로 상수를 미리 정의해서 bootConsole()
 * 내부에서 빠진 그 단계를 대신 채워준다 (bomul-api의 analy.php와 동일한 이유/우회).
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

const QUEUE_PUSH_LIMIT              = 20;
const QUEUE_PUSH_TIMEOUT_MS         = 500;
const QUEUE_PUSH_CONNECT_TIMEOUT_MS = 300;

$db = db_connect('default');
$queueModel = model('App\Models\QueueModel', true, $db);

$rows = $queueModel->get_pending_with_server(QUEUE_PUSH_LIMIT);
if (empty($rows)) {
    return;
}

foreach ($rows as $row) {
    $payload = array_merge(
        json_decode($row['payload'], true) ?? [],
        ['queue_no' => (int) $row['queue_no'], 'action' => $row['action'] ?? 'new']
    );
    $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);
    $authHeader = 'Authorization: Bearer ' . $row['api_key'];

    $ch = curl_init($row['receiver_url']);
    curl_setopt_array($ch, [
        CURLOPT_POST              => true,
        CURLOPT_POSTFIELDS        => $jsonBody,
        CURLOPT_HTTPHEADER        => [
            'Content-Type: application/json',
            $authHeader,
        ],
        CURLOPT_TIMEOUT_MS        => QUEUE_PUSH_TIMEOUT_MS,
        CURLOPT_CONNECTTIMEOUT_MS => QUEUE_PUSH_CONNECT_TIMEOUT_MS,
        CURLOPT_RETURNTRANSFER    => true,
    ]);
    $response = curl_exec($ch);
    $errno    = curl_errno($ch);
    $error    = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // fire-and-forget이므로 curl 성공/실패와 무관하게 SENT로 마킹 - 실제 처리결과는
    // 운영서버가 별도로 Admin\Callback::postComplete()로 보고한다.
    $queueModel->markSent($row['queue_no']);

    if ($errno !== 0) {
        log_message('error', "[queue_push] queue_no={$row['queue_no']} curl 실패 (errno={$errno}): {$error}");
    } else {
        log_message('info', "[queue_push] queue_no={$row['queue_no']} HTTP {$httpCode} / response: " . ($response === false ? '(false)' : $response));
    }
}
