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
    private $customerModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('App\Models\ServiceApplyModel', true, $this->db);
        $this->productModel = model('App\Models\ProductModel', true, $this->db);
        $this->serverListModel = model('App\Models\ServerListModel', true, $this->db);
        $this->serviceLiveModel = model('App\Models\ServiceLiveModel', true, $this->db);
        $this->queueModel = model('App\Models\QueueModel', true, $this->db);
        $this->customerModel = model('App\Models\CustomerModel', true, $this->db);
    }

    // 어드민 "서비스 신청 현황" 목록
    public function get_list()
    {
        $rows = $this->model->get_list_with_details();
        return $rows;
    }

    // 어드민 "서비스 신청 상세" - Customer::get_row()와 별개. service_apply 1건 기준 조회.
    public function get_row($apply_no)
    {
        return $this->model->get_detail_by_apply_no($apply_no);
    }

    // 어드민 "서비스 신청 수정" - 신청서 스냅샷 항목만 수정 (status는 approve()/reject() 전용)
    public function edit($apply_no, array $data): array
    {
        $apply_no      = (int) $apply_no;
        $company_name  = trim($data['company_name'] ?? '');
        $ceo_name      = trim($data['ceo_name'] ?? '');
        $manager_phone = trim($data['manager_phone'] ?? '');
        $manager_email = trim($data['manager_email'] ?? '');
        $sub_domain    = trim($data['sub_domain'] ?? '');

        if ($company_name === '' || $ceo_name === '' || $manager_phone === '' || $manager_email === '' || $sub_domain === '') {
            return AT_Result::toFailData("필수 항목을 입력해주세요.");
        }

        if (!preg_match('/^[a-z0-9][a-z0-9-]{1,30}[a-z0-9]$/', $sub_domain)) {
            return AT_Result::toFailData("도메인 주소는 영문 소문자/숫자/하이픈만 사용할 수 있습니다.");
        }

        $apply = $this->model->findByIdx($apply_no);
        if (!$apply) {
            return AT_Result::toFailData("존재하지 않는 신청입니다.");
        }

        if ($this->model->existsBySubDomain($sub_domain, $apply_no)) {
            return AT_Result::toFailData("이미 사용중인 도메인 주소입니다.");
        }

        $entity = new ServiceApply_Entity();
        $entity->setApplyNo($apply_no);
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
        $entity->setSubDomain($sub_domain);
        $entity->setCustomDomain($data['custom_domain'] ?? null);
        $entity->setMemo($data['memo'] ?? null);

        if (!$this->model->sql_edit($entity)) {
            return AT_Result::toFailData("수정에 실패했습니다.");
        }

        return AT_Result::toSuccessData("서비스 신청 정보를 수정했습니다.");
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

        // 신청~승인 사이 다른 신청서가 같은 도메인을 먼저 승인받았을 수 있다 - 승인 직전 재확인.
        if ($this->model->existsBySubDomain($apply['sub_domain'], $apply_no)) {
            return AT_Result::toFailData("이미 사용중인 도메인 주소입니다.");
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

        $live = $this->createLive($apply, $apply_no, $server, $product);
        if (!$live) {
            return AT_Result::toFailData("서비스 라이브 생성에 실패했습니다.");
        }

        $this->serverListModel->incrementTenantCount($server['server_no']);

        // service_apply/service_live는 이미 커밋됐고 트랜잭션으로 묶여있지 않다 - 여기서 실패해도
        // 되돌리지 않고, 관리자가 "큐 적재" 버튼(requeue())으로 이 live_no를 그대로 재사용해서
        // 큐만 다시 넣을 수 있게 안내한다.
        if (!$this->enqueue($apply, $live['live_no'], $server, $product, $live['license_key'])) {
            return AT_Result::toFailData("승인은 처리됐지만 큐 적재에 실패했습니다. '큐 적재' 버튼으로 다시 시도해주세요.");
        }

        return AT_Result::toSuccessData("승인 처리되었습니다. (배정서버: {$server['server_code']})");
    }

    // APPROVED인데 service_live/queue가 안 만들어진 신청서를 뒤늦게 큐에 적재한다.
    // (예: approve()를 거치지 않고 status만 직접 APPROVED로 바뀐 데이터를 바로잡을 때)
    // - service_live가 이미 있고 install_status=DONE이면 재적재하지 않는다(이미 세팅 완료된 건).
    // - service_live가 있지만 PENDING/FAILED면 새로 만들지 않고 기존 live_no로 큐만 다시 적재한다.
    // - service_live가 아예 없으면 approve()와 동일하게 서버배정부터 새로 진행한다.
    public function requeue($apply_no): array
    {
        $apply = $this->model->findByIdx($apply_no);
        if (!$apply) {
            return AT_Result::toFailData("존재하지 않는 신청입니다.");
        }
        if ($apply['status'] !== 'APPROVED') {
            return AT_Result::toFailData("승인완료 상태의 신청만 큐에 적재할 수 있습니다.");
        }

        $product = $this->productModel->findByIdx($apply['product_no']);
        if (!$product) {
            return AT_Result::toFailData("상품 정보를 찾을 수 없습니다.");
        }

        $live = $this->serviceLiveModel->findByApplyNo($apply_no);

        if ($live) {
            if ($live['install_status'] === 'DONE') {
                return AT_Result::toFailData("이미 설치 완료된 건입니다.");
            }
            $server = $this->serverListModel->findByIdx($live['server_no']);
            if (!$server) {
                return AT_Result::toFailData("배정된 운영서버 정보를 찾을 수 없습니다.");
            }
            if (!$this->enqueue($apply, $live['live_no'], $server, $product, $live['license_key'])) {
                return AT_Result::toFailData("큐 적재에 실패했습니다.");
            }
            return AT_Result::toSuccessData("큐에 재적재되었습니다. (배정서버: {$server['server_code']})");
        }

        $server = $this->serverListModel->findLeastLoaded();
        if (!$server) {
            return AT_Result::toFailData("배정 가능한 운영서버가 없습니다.");
        }

        $live = $this->createLive($apply, $apply_no, $server, $product);
        if (!$live) {
            return AT_Result::toFailData("서비스 라이브 생성에 실패했습니다.");
        }

        $this->serverListModel->incrementTenantCount($server['server_no']);

        if (!$this->enqueue($apply, $live['live_no'], $server, $product, $live['license_key'])) {
            return AT_Result::toFailData("서비스 라이브는 생성됐지만 큐 적재에 실패했습니다. 다시 시도해주세요.");
        }

        return AT_Result::toSuccessData("큐에 적재되었습니다. (배정서버: {$server['server_code']})");
    }

    // service_live INSERT(또는 기존 재사용) 성공 시 ['live_no' => .., 'license_key' => ..], 실패 시 null
    private function createLive(array $apply, $apply_no, array $server, array $product): ?array
    {
        // apply_no당 service_live는 1건(uk_apply) - INSERT 전에 이미 있는지 먼저 확인한다.
        // 이미 있으면(status가 REQUEST/REVIEW로 되돌아간 뒤 재승인되는 경우 등) 다시 만들지 않고
        // 기존 값을 그대로 재사용 - TODO: 연장 등 갱신 정책이 정해지면 여기서 UPDATE로 처리
        $existing = $this->serviceLiveModel->findByApplyNo($apply_no);
        if ($existing) {
            return ['live_no' => $existing['live_no'], 'license_key' => $existing['license_key']];
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
            return null;
        }

        return ['live_no' => $live_no, 'license_key' => $liveEntity->getLicenseKey()];
    }

    // 운영서버가 이 payload로 (1) 테넌트 DB 세팅 (2) 중앙 bomul.member에 로그인 계정 등록까지
    // 한 번에 처리한다 - 그래서 customer_id/customer_pw(이미 SHA-256 해시됨, 평문 아님)도 같이
    // 실어보낸다. bomul.member.user_pwd도 SHA-256 고정이라 해시를 그대로 복사하면 된다
    // (CustomerModel::sql_save()와 동일 정책 - CustomerService.php의 이관 관련 주석 참고).
    private function enqueue(array $apply, $live_no, array $server, array $product, ?string $licenseKey = null): bool
    {
        $customer = $this->customerModel->findByIdx($apply['customer_no']);

        $payload = [
            'fid'          => $apply['sub_domain'],
            'license_key'  => $licenseKey,
            'product_code' => $product['product_code'],
            'company_name' => $apply['company_name'],
            'ceo_name'     => $apply['ceo_name'],
            'biz_no'       => $apply['biz_no'],
            'license_no'   => $apply['license_no'],
            'customer_id'  => $customer['customer_id'] ?? null,
            'customer_pw'  => $customer['customer_pw'] ?? null,
        ];
        $result = $this->queueModel->sql_save($live_no, $server['server_no'], $apply['sub_domain'], $payload, 'new');
        return (bool) $result;
    }

    // 이미 설치 완료된 서비스의 member.service(Y/N)만 바꾸는 가벼운 push - 신규 세팅(enqueue())과
    // 달리 DB 재생성/customer_id·pw 없이 fid+status만 실어보낸다. 관리자가 정지/재개 버튼을 누르거나,
    // 나중에 만료 배치가 자동으로 호출하는 지점이 될 수 있음.
    public function pushMemberStatus($apply_no, string $status): array
    {
        $status = strtoupper($status);
        if (!in_array($status, ['Y', 'N'], true)) {
            return AT_Result::toFailData("잘못된 상태값입니다.");
        }

        $live = $this->serviceLiveModel->findByApplyNo($apply_no);
        if (!$live) {
            return AT_Result::toFailData("설치된 서비스가 없습니다.");
        }
        if ($live['install_status'] !== 'DONE') {
            return AT_Result::toFailData("아직 설치가 완료되지 않은 서비스입니다.");
        }

        $server = $this->serverListModel->findByIdx($live['server_no']);
        if (!$server) {
            return AT_Result::toFailData("배정된 운영서버 정보를 찾을 수 없습니다.");
        }

        $payload = ['fid' => $live['fid'], 'status' => $status];
        $ok = $this->queueModel->sql_save($live['live_no'], $server['server_no'], $live['fid'], $payload, 'update');
        if (!$ok) {
            return AT_Result::toFailData("큐 적재에 실패했습니다." . $this->dbErrorSuffix($this->queueModel));
        }

        return AT_Result::toSuccessData($status === 'Y' ? "재개 요청이 큐에 적재되었습니다." : "정지 요청이 큐에 적재되었습니다.");
    }

    // 원인 파악용 - CI_Model::getDbError()에 남아있는 직전 쿼리의 실제 DB 에러를 실패 메시지에 붙인다.
    // service_apply/service_live/queue는 이 저장소에 CREATE TABLE이 없어 스키마를 코드로 확인할 수
    // 없으므로, 어디서 왜 막히는지는 이 에러 메시지로 진단해야 한다.
    private function dbErrorSuffix($model): string
    {
        $error = $model->getDbError();
        if (empty($error['message'])) {
            return '';
        }
        return " (DB: {$error['message']})";
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
        $sub_domain    = trim($data['sub_domain'] ?? '');

        if ($customer_no <= 0) {
            return AT_Result::toFailData("로그인 후 이용해주세요.");
        }
        if ($product_no <= 0 || $company_name === '' || $ceo_name === '' || $manager_phone === '' || $manager_email === '' || $sub_domain === '') {
            return AT_Result::toFailData("필수 항목을 입력해주세요.");
        }

        if (!preg_match('/^[a-z0-9][a-z0-9-]{1,30}[a-z0-9]$/', $sub_domain)) {
            return AT_Result::toFailData("도메인 주소는 영문 소문자/숫자/하이픈만 사용할 수 있습니다.");
        }
        // 세팅 전 상태(REQUEST/REVIEW/APPROVED)를 점유중인 다른 신청서와만 충돌 - 한 고객이
        // 서비스를 여러 개 신청할 수 있으므로 customer_no와 무관하게 sub_domain 자체로만 체크한다.
        if ($this->model->existsBySubDomain($sub_domain)) {
            return AT_Result::toFailData("이미 사용중인 도메인 주소입니다.");
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
        $entity->setSubDomain($sub_domain);
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
