<?php

namespace App\Service;

use App\Entity\ServiceApply_Entity;
use App\Entity\ServiceLive_Entity;
use App\Libraries\AT_Result;

class ServiceApplyService extends CI_Service
{
    private $model;
    private $productModel;
    private $serverListModel;
    private $serviceLiveModel;
    private $queueModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('App\Models\ServiceApplyModel', true, $this->db);
        $this->productModel = model('App\Models\ProductModel', true, $this->db);
        $this->serverListModel = model('App\Models\ServerListModel', true, $this->db);
        $this->serviceLiveModel = model('App\Models\ServiceLiveModel', true, $this->db);
        $this->queueModel = model('App\Models\QueueModel', true, $this->db);
    }

    // 어드민 "서비스 신청 현황" 목록
    public function get_list()
    {
        return $this->model->get_list_with_details();
    }

    // 어드민 승인 처리 - service_apply 상태변경 + 서버배정 + service_live 생성 + queue 적재까지 한 번에.
    // (1) 서비스 신청서(service_apply) → APPROVED
    // (2) server_list에서 least-loaded 서버 배정
    // (3) service_live 생성 (install_status=PENDING)
    // (4) queue에 push 대상 1건 적재 - QueuePush 커맨드가 이걸 보고 운영서버로 실제 push한다
    public function approve($apply_no): array
    {
        $apply = $this->model->findByIdx($apply_no);
        if (!$apply) {
            return AT_Result::toFailData("존재하지 않는 신청입니다.");
        }
        if ($apply['status'] !== 'REQUEST' && $apply['status'] !== 'REVIEW') {
            return AT_Result::toFailData("이미 처리된 신청입니다.");
        }

        $server = $this->serverListModel->findLeastLoaded();
        if (!$server) {
            return AT_Result::toFailData("배정 가능한 운영서버가 없습니다.");
        }

        $product = $this->productModel->findByIdx($apply['product_no']);
        if (!$product) {
            return AT_Result::toFailData("상품 정보를 찾을 수 없습니다.");
        }

        if (!$this->model->updateStatus($apply_no, 'APPROVED')) {
            return AT_Result::toFailData("승인 처리에 실패했습니다.");
        }

        $liveEntity = new ServiceLive_Entity();
        $liveEntity->setCustomerNo($apply['customer_no']);
        $liveEntity->setApplyNo($apply_no);
        $liveEntity->setServerNo($server['server_no']);
        $liveEntity->setFid($apply['sub_domain']);
        // 운영서버가 push 수신 시 이 fid 요청이 정말 우리가 보낸 게 맞는지 확인하는 용도 - 매 승인마다 새로 발급
        $liveEntity->setLicenseKey(bin2hex(random_bytes(16)));
        $liveEntity->setStatus('ACTIVE');
        $liveEntity->setInstallStatus('PENDING');
        $liveEntity->setStartDate(date('Y-m-d'));
        $liveEntity->setExpireDate(date('Y-m-d', strtotime('+' . (int) $product['period_day'] . ' days')));

        $live_no = $this->serviceLiveModel->sql_save($liveEntity);
        if (!$live_no) {
            return AT_Result::toFailData("서비스 라이브 생성에 실패했습니다.");
        }

        $this->serverListModel->incrementTenantCount($server['server_no']);

        $payload = [
            'fid'          => $apply['sub_domain'],
            'license_key'  => $liveEntity->getLicenseKey(),
            'product_code' => $product['product_code'],
            'company_name' => $apply['company_name'],
            'ceo_name'     => $apply['ceo_name'],
            'biz_no'       => $apply['biz_no'],
            'license_no'   => $apply['license_no'],
        ];
        $this->queueModel->sql_save($live_no, $server['server_no'], $apply['sub_domain'], $payload);

        return AT_Result::toSuccessData("승인 처리되었습니다. (배정서버: {$server['server_code']})");
    }

    public function reject($apply_no, string $reason): array
    {
        $apply = $this->model->findByIdx($apply_no);
        if (!$apply) {
            return AT_Result::toFailData("존재하지 않는 신청입니다.");
        }
        if ($apply['status'] !== 'REQUEST' && $apply['status'] !== 'REVIEW') {
            return AT_Result::toFailData("이미 처리된 신청입니다.");
        }

        if (!$this->model->updateStatus($apply_no, 'REJECTED', $reason)) {
            return AT_Result::toFailData("거절 처리에 실패했습니다.");
        }

        return AT_Result::toSuccessData("거절 처리되었습니다.");
    }

    // $data['customer_no']는 항상 서버(세션)에서 채워서 넘겨야 한다 - 폼 입력값으로 받으면 안 됨(타 계정 신청 위조 방지)
    public function apply(array $data): array
    {
        $customer_no  = (int) ($data['customer_no'] ?? 0);
        $product_no   = (int) ($data['product_no'] ?? 0);
        $company_name  = trim($data['company_name'] ?? '');
        $ceo_name      = trim($data['ceo_name'] ?? '');
        $manager_phone = trim($data['manager_phone'] ?? '');
        $manager_email = trim($data['manager_email'] ?? '');

        if ($customer_no <= 0) {
            return AT_Result::toFailData("로그인 후 이용해주세요.");
        }
        if ($product_no <= 0 || $company_name === '' || $ceo_name === '' || $manager_phone === '' || $manager_email === '') {
            return AT_Result::toFailData("필수 항목을 입력해주세요.");
        }

        $product = $this->productModel->findByIdx($product_no);
        if (!$product || $product['status'] !== 'USE') {
            return AT_Result::toFailData("신청할 수 없는 상품입니다.");
        }

        // 이벤트 상품은 고객당 1회 - 상태 무관 기존 신청이력이 있으면 재신청 차단
        // (재신청 허용 정책이 REJECTED/CANCEL 예외를 두는 쪽으로 바뀌면 existsByCustomerProduct에 status 조건 추가)
        if (((string) $product['is_event']) === '1' && $this->model->existsByCustomerProduct($customer_no, $product_no)) {
            return AT_Result::toFailData("이미 신청한 이벤트 상품입니다.");
        }

        $entity = new ServiceApply_Entity();
        $entity->setCustomerNo($customer_no);
        $entity->setProductNo($product_no);
        $entity->setCompanyName($company_name);
        $entity->setCeoName($ceo_name);
        $entity->setLicenseNo($data['license_no'] ?? null);
        $entity->setBizNo($data['biz_no'] ?? null);
        $entity->setManagerName($data['manager_name'] ?? null);
        $entity->setManagerPhone($manager_phone);
        $entity->setManagerEmail($manager_email);
        $entity->setTel($data['tel'] ?? null);
        $entity->setFax($data['fax'] ?? null);
        $entity->setZipcode($data['zipcode'] ?? null);
        $entity->setAddr1($data['addr1'] ?? null);
        $entity->setAddr2($data['addr2'] ?? null);
        $entity->setHomepageName($data['homepage_name'] ?? null);
        $entity->setSubDomain($data['sub_domain'] ?? null);
        $entity->setCustomDomain($data['custom_domain'] ?? null);
        $entity->setMemo($data['memo'] ?? null);
        $entity->setStatus('REQUEST');

        $apply_no = $this->model->sql_save($entity);
        if (!$apply_no) {
            return AT_Result::toFailData("서비스 신청에 실패했습니다.");
        }

        return AT_Result::toSuccessData("서비스 신청이 완료되었습니다.", ['apply_no' => $apply_no]);
    }
}
