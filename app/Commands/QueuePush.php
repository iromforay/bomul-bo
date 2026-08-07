<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

// crontab이 매분 호출 (* * * * * php /path/to/bomul-bo/spark queue:push).
// queue의 PENDING 건을 배정된 운영서버(server_list.receiver_url)로 fire-and-forget push하고 SENT로 마킹.
// 완료 여부는 이 커맨드가 기다리지 않는다 - 운영서버가 처리 끝나고 Admin\Callback::postComplete()로
// 직접 보고한다 (bomul-api의 app/batch/analy.php와 동일한 fire-and-forget 패턴).
class QueuePush extends BaseCommand
{
    protected $group       = 'queue';
    protected $name        = 'queue:push';
    protected $description = 'queue의 PENDING 건을 배정된 운영서버로 push한다.';

    public function run(array $params)
    {
        $db = db_connect('default');
        $queueModel = model('App\Models\QueueModel', true, $db);

        $rows = $queueModel->get_pending_with_server(20);
        if (empty($rows)) {
            CLI::write('queue push 데이터 없음. 종료 ', 'red' );
            return;
        }

        foreach ($rows as $row) {
            $this->push($row);
            $queueModel->markSent($row['queue_no']);
        }

        CLI::write('queue push 완료: ' . count($rows) . '건', 'green');
    }

    // fire-and-forget - 운영서버에 요청이 도달할 정도만 기다리고 응답(설치 결과)은 안 기다린다.
    // 진단용으로 실제 curl 커맨드 재현 문자열 + 응답/에러/HTTP코드를 화면(및 crontab 로그)에 출력한다.
    private function push(array $row): void
    {
        $payload = array_merge(
            json_decode($row['payload'], true) ?? [],
            ['queue_no' => (int) $row['queue_no']]
        );
        $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $authHeader = 'Authorization: Bearer ' . $row['api_key'];

        // 그대로 터미널에 붙여넣어 재현 가능한 curl 커맨드 문자열
        $curlCommand = sprintf(
            "curl -v -X POST %s -H %s -H %s -d %s",
            escapeshellarg($row['receiver_url']),
            escapeshellarg('Content-Type: application/json'),
            escapeshellarg($authHeader),
            escapeshellarg($jsonBody)
        );
        CLI::write('[queue_no=' . $row['queue_no'] . '] curl 재현: ' . $curlCommand, 'yellow');

        $ch = curl_init($row['receiver_url']);
        curl_setopt_array($ch, [
            CURLOPT_POST              => true,
            CURLOPT_POSTFIELDS        => $jsonBody,
            CURLOPT_HTTPHEADER        => [
                'Content-Type: application/json',
                $authHeader,
            ],
            CURLOPT_TIMEOUT_MS        => 500,
            CURLOPT_CONNECTTIMEOUT_MS => 300,
            CURLOPT_RETURNTRANSFER    => true,
        ]);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            CLI::write("[queue_no={$row['queue_no']}] curl 실패 (errno={$errno}): {$error}", 'red');
        } else {
            CLI::write("[queue_no={$row['queue_no']}] HTTP {$httpCode} / response: " . ($response === false ? '(false)' : $response), 'light_cyan');
        }
    }
}
