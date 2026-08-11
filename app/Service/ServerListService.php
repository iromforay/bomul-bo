<?php

namespace App\Service;

use App\Entity\ServerList_Entity;
use App\Libraries\AT_Result;

// CI_Service를 상속하지 않는다 - ad(이 백오피스)는 SHOP_CODE를 안 쓰는데 CI_Service 생성자는
// SHOP_CODE가 정의돼 있다고 가정한다 (ManagerService/ProductService와 동일한 이유).
class ServerListService extends CI_Service
{
    private $model;
    private $serviceLiveModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('App\Models\ServerListModel', true, $this->db);
        $this->serviceLiveModel = model('App\Models\ServiceLiveModel', true, $this->db);
    }

    public function get_list()
    {
        return $this->model->get_list();
    }

    public function get_row($server_no)
    {
        return $this->model->findByIdx($server_no);
    }

    public function save(array $data): array
    {
        [$entity, $error] = $this->buildEntity(null, $data);
        if ($error) {
            return $error;
        }

        if ($this->model->sql_save($entity)) {
            return AT_Result::toSuccessData("서버를 등록했습니다.");
        }

        return AT_Result::toFailData("등록에 실패했습니다.");
    }

    public function edit($server_no, array $data): array
    {
        [$entity, $error] = $this->buildEntity((int) $server_no, $data);
        if ($error) {
            return $error;
        }

        if ($this->model->sql_edit($entity)) {
            return AT_Result::toSuccessData("서버 정보를 수정했습니다.");
        }

        return AT_Result::toFailData("수정에 실패했습니다.");
    }

    public function delete($server_no): array
    {
        $server_no = (int) $server_no;

        if ($this->serviceLiveModel->existsByServerNo($server_no)) {
            return AT_Result::toFailData("이미 이 서버에 배정된 서비스가 있어 삭제할 수 없습니다. 신규 배정을 막으려면 '설치가능'을 N으로 바꿔주세요.");
        }

        if ($this->model->sql_delete($server_no)) {
            return AT_Result::toSuccessData("서버를 삭제했습니다.");
        }

        return AT_Result::toFailData("삭제에 실패했습니다.");
    }

    // 등록/수정 공통 검증 + Entity 조립. 실패 시 [null, AT_Result 실패배열], 성공 시 [Entity, null]
    private function buildEntity(?int $server_no, array $data): array
    {
        $server_code  = trim($data['server_code'] ?? '');
        $server_name  = trim($data['server_name'] ?? '');
        $db_host      = trim($data['db_host'] ?? '');
        $db_port      = trim((string) ($data['db_port'] ?? ''));
        $receiver_url = trim($data['receiver_url'] ?? '');
        $api_key      = trim($data['api_key'] ?? '');
        $installable_yn = ($data['installable_yn'] ?? 'Y') === 'N' ? 'N' : 'Y';
        $status       = $data['status'] ?? 'USE';

        if ($server_code === '' || $server_name === '' || $receiver_url === '') {
            return [null, AT_Result::toFailData("서버코드, 서버명, receiver URL을 입력해주세요.")];
        }
        if (!in_array($status, ['USE', 'STOP'], true)) {
            return [null, AT_Result::toFailData("잘못된 상태값입니다.")];
        }
        if ($db_port !== '' && !ctype_digit($db_port)) {
            return [null, AT_Result::toFailData("DB 포트는 숫자만 입력해주세요.")];
        }

        if ($this->model->existsByCode($server_code, $server_no)) {
            return [null, AT_Result::toFailData("이미 사용중인 서버코드입니다.")];
        }

        // 신규 등록 시 비워두면 QueuePush가 인증에 쓸 토큰을 자동 발급 (기존 승인 흐름의
        // ServiceApplyService::createLive() license_key 발급 방식과 동일)
        if ($api_key === '' && $server_no === null) {
            $api_key = bin2hex(random_bytes(16));
        }
        if ($api_key === '') {
            return [null, AT_Result::toFailData("API 키를 입력해주세요.")];
        }

        $entity = new ServerList_Entity();
        if ($server_no !== null) {
            $entity->setServerNo($server_no);
        }
        $entity->setServerCode($server_code);
        $entity->setServerName($server_name);
        $entity->setDbHost($db_host !== '' ? $db_host : null);
        $entity->setDbPort($db_port !== '' ? (int) $db_port : null);
        $entity->setReceiverUrl($receiver_url);
        $entity->setApiKey($api_key);
        $entity->setInstallableYn($installable_yn);
        $entity->setStatus($status);
        $entity->setMemo($data['memo'] ?? null);

        return [$entity, null];
    }
}
