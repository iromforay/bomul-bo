<?php

namespace App\Controllers\Admin;

use App\Libraries\AT_Result;

// 운영서버(bomul-api)가 세팅 완료/실패를 보고하는 서버-대-서버 전용 엔드포인트.
// 브라우저 세션 로그인이 아니라 server_list.api_key(Bearer 토큰)로 인증한다 -
// 그래서 BaseController::auth()의 세션 게이트를 타면 안 되므로 skipAuth로 뺀다
// (Admin\Login과 동일한 이유 - Admin\BaseController::__construct() 참고).
class Callback extends BaseController
{
    protected $skipAuth = true;

    public function postComplete()
    {
        $server_code = (string) $this->request->getPost('server_code');
        $queue_no    = (int) $this->request->getPost('queue_no');
        $status      = (string) $this->request->getPost('status'); // DONE | FAILED
        $errorMsg    = (string) $this->request->getPost('error_message');

        if ($server_code === '' || $queue_no <= 0 || !in_array($status, ['DONE', 'FAILED'], true)) {
            echo AT_Result::toJson(AT_Result::toFailData("필수 파라미터가 없습니다."));
            return;
        }

        $db = db_connect('default');
        $serverListModel = model('App\Models\ServerListModel', true, $db);
        $queueModel = model('App\Models\QueueModel', true, $db);
        $serviceLiveModel = model('App\Models\ServiceLiveModel', true, $db);

        $server = $this->findServerByCode($serverListModel, $db, $server_code);
        if (!$server || !$this->checkToken($server['api_key'])) {
            echo AT_Result::toJson(AT_Result::toFailData("인증에 실패했습니다."));
            return;
        }

        $queue = $queueModel->findByIdx($queue_no);
        if (!$queue || (int) $queue['server_no'] !== (int) $server['server_no']) {
            echo AT_Result::toJson(AT_Result::toFailData("잘못된 요청입니다."));
            return;
        }

        if ($status === 'DONE') {
            $queueModel->markDone($queue_no);
            $serviceLiveModel->updateInstallStatus($queue['live_no'], 'DONE');
        } else {
            $queueModel->markFailed($queue_no, $errorMsg);
            $serviceLiveModel->updateInstallStatus($queue['live_no'], 'FAILED', $errorMsg);
        }

        echo AT_Result::toJson(AT_Result::toSuccessData());
    }

    private function findServerByCode($serverListModel, $db, string $server_code)
    {
        $sql = "SELECT * FROM `server_list` WHERE `server_code` = " . $db->escape($server_code) . " LIMIT 1";
        $rows = $serverListModel->getExecQuery($sql);
        return $rows[0] ?? null;
    }

    // Authorization 헤더의 Bearer 토큰이 그 서버의 api_key와 일치하는지 (hash_equals로 타이밍 공격 방지)
    private function checkToken(string $expectedKey): bool
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $auth);
        return $expectedKey !== '' && hash_equals($expectedKey, $token);
    }
}
