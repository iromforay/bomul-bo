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
    private function push(array $row): void
    {
        $payload = array_merge(
            json_decode($row['payload'], true) ?? [],
            ['queue_no' => (int) $row['queue_no']]
        );

        $ch = curl_init($row['receiver_url']);
        curl_setopt_array($ch, [
            CURLOPT_POST              => true,
            CURLOPT_POSTFIELDS        => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER        => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $row['api_key'],
            ],
            CURLOPT_TIMEOUT_MS        => 500,
            CURLOPT_CONNECTTIMEOUT_MS => 300,
            CURLOPT_RETURNTRANSFER    => true,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
