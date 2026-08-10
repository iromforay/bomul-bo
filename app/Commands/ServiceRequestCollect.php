<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

// crontab이 매분 호출 (* * * * * php /path/to/bomul-bo/spark service:collect-request).
// service_live.status='REQUEST'인 건(관리자가 상세화면에서 상태를 "요청"으로 바꾼 건)을 모아
// queue에 action='new'로 적재하고, 적재 직후 status를 ACTIVE로 되돌린다(재적재 방지 - 큐 자체의
// 성공/실패는 여느 때처럼 Admin\Callback::postComplete()가 install_status로 보고받는다).
class ServiceRequestCollect extends BaseCommand
{
    protected $group       = 'queue';
    protected $name        = 'service:collect-request';
    protected $description = 'service_live.status=REQUEST 건을 모아 queue에 적재한다.';

    public function run(array $params)
    {
        date_default_timezone_set("Asia/Seoul");
        $db = db_connect('default');
        $serviceLiveModel = model('App\Models\ServiceLiveModel', true, $db);
        $queueModel = model('App\Models\QueueModel', true, $db);

        $rows = $serviceLiveModel->get_request_pending();
        if (empty($rows)) {
            CLI::write('요청 상태의 service_live 없음. 종료', 'red');
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
                CLI::write("[live_no={$row['live_no']}] 큐 적재 실패 - 다음 실행 때 재시도", 'red');
                continue;
            }

            $serviceLiveModel->updateInstallStatus($row['live_no'], 'PENDING');
            $serviceLiveModel->updateStatus($row['live_no'], 'ACTIVE');
            CLI::write("[live_no={$row['live_no']}] 큐 적재 완료 (fid={$row['fid']})", 'green');
        }

        CLI::write('service:collect-request 완료: ' . count($rows) . '건 처리', 'green');
    }
}
