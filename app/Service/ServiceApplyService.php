<?php

namespace App\Service;

use App\Entity\ServiceApply_Entity;
use App\Entity\ServiceLive_Entity;
use App\Libraries\AT_Result;

class ServiceApplyService extends CI_Service
{
    // 만료 후 유예(SUSPENDED) 기간 - app/batch/service_expire_check.php와 동일한 값이어야 한다.
    private const SUSPEND_GRACE_DAYS = 15;

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

    // 어드민 승인 처리 - apply_type으로 분기한다.
    // NEW(신규): service_apply 상태변경 + 서버배정 + service_live 생성 + queue(action='new') 적재.
    // RENEW(연장): 새 서버/새 service_live 없이, renew_live_no가 가리키는 기존 service_live의
    // 만료일만 연장하고 queue(action='extend')로 통보 - approveRenew() 참고.
    public function approve($apply_no): array
    {
        $apply = $this->model->findByIdx($apply_no);
        if (!$apply) {
            return AT_Result::toFailData("존재하지 않는 신청입니다.");
        }
        if ($apply['status'] !== 'REQUEST' && $apply['status'] !== 'REVIEW') {
            return AT_Result::toFailData("이미 처리된 신청입니다.");
        }

        if (($apply['apply_type'] ?? 'NEW') === 'RENEW') {
            return $this->approveRenew($apply, $apply_no);
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

        // server_list.tenant_count는 더 이상 별도로 갱신하지 않는다 - ServerListModel이 매 조회마다
        // service_live를 실시간 COUNT()하므로 이 INSERT만으로 이미 반영됨 (findLeastLoaded() 참고).

        // service_apply/service_live는 이미 커밋됐고 트랜잭션으로 묶여있지 않다 - 여기서 실패해도
        // 되돌리지 않고, 관리자가 "큐 적재" 버튼(requeue())으로 이 live_no를 그대로 재사용해서
        // 큐만 다시 넣을 수 있게 안내한다.
        if (!$this->enqueue($apply, $live['live_no'], $server, $product, $live['license_key'])) {
            return AT_Result::toFailData("승인은 처리됐지만 큐 적재에 실패했습니다. '큐 적재' 버튼으로 다시 시도해주세요.");
        }

        return AT_Result::toSuccessData("승인 처리되었습니다. (배정서버: {$server['server_code']})");
    }

    // 연장 신청 승인 - 새 서버 배정/service_live 생성 없이 renew_live_no가 가리키는 기존
    // service_live 1건의 만료일만 연장한다. 날짜 기준 계산과 3단계 상태 자동판정(정상/유예/종료),
    // queue(action='extend') 적재는 applyExpireDate()(ServiceLive 상세화면의 "빠른 연장"과 동일 로직)를
    // 그대로 재사용해서 두 경로의 정책이 항상 일치하도록 한다.
    private function approveRenew(array $apply, $apply_no): array
    {
        $live = $this->serviceLiveModel->findByIdx($apply['renew_live_no'] ?? 0);
        if (!$live || (int) $live['customer_no'] !== (int) $apply['customer_no']) {
            return AT_Result::toFailData("연장 대상 서비스를 찾을 수 없습니다.");
        }
        if ($live['install_status'] !== 'DONE') {
            return AT_Result::toFailData("아직 설치가 완료되지 않은 서비스는 연장할 수 없습니다.");
        }

        $product = $this->productModel->findByIdx($apply['product_no']);
        if (!$product) {
            return AT_Result::toFailData("상품 정보를 찾을 수 없습니다.");
        }

        if (!$this->model->updateStatus($apply_no, 'APPROVED')) {
            return AT_Result::toFailData("승인 처리에 실패했습니다.");
        }

        // 연장 기준일: 오늘이 만료일 이전이면 만료일에, 이미 만료됐으면 오늘부터 상품 이용기간만큼 추가.
        $today = date('Y-m-d');
        $base = ($today < $live['expire_date']) ? $live['expire_date'] : $today;
        $newExpireDate = date('Y-m-d', strtotime($base . ' +' . (int) $product['period_day'] . ' days'));

        return $this->applyExpireDate($live, $newExpireDate, "연장 승인 처리되었습니다.");
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
        // 연장(RENEW) 신청은 이 apply_no로 새 service_live를 찾지 않는다(연장 대상은 renew_live_no가
        // 가리키는 기존 건) - 그대로 두면 findByApplyNo()가 null을 반환해 아래 로직이 신규 설치처럼
        // 서버를 새로 배정해버리는 사고가 나므로 여기서 막는다. 재시도가 필요하면 서비스 라이브
        // 상세화면에서 만료일/상태를 다시 조정해주세요.
        if (($apply['apply_type'] ?? 'NEW') === 'RENEW') {
            return AT_Result::toFailData("연장 신청은 이 방식으로 재처리할 수 없습니다. 서비스 라이브 상세에서 처리해주세요.");
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

    // 어드민 "서비스 라이브 현황" 목록
    public function get_live_list()
    {
        return $this->serviceLiveModel->get_list_with_details();
    }

    // 어드민 "서비스 라이브 상세"
    public function get_live_row($apply_no)
    {
        return $this->serviceLiveModel->get_detail_by_apply_no($apply_no);
    }

    // 상세화면의 "30일/60일 연장" 빠른 버튼 - days만큼 연장한다(항상 양수).
    // 기준일: 아직 안 끝난 서비스(오늘<만료일)는 만료일에서, 이미 끝난 서비스(오늘>=만료일)는
    // 오늘부터 더한다 - 이미 만료된 걸 만료일 기준으로 연장하면 연장한 기간만큼 공짜로 날아가버리므로.
    public function extendByDays($apply_no, int $days): array
    {
        if ($days <= 0) {
            return AT_Result::toFailData("연장할 일수를 입력해주세요.");
        }

        $live = $this->getExtendableLive($apply_no);
        if (is_array($live) && isset($live['SUCCESS'])) {
            return $live; // 실패 결과 그대로 반환
        }

        $today = date('Y-m-d');
        $base = ($today < $live['expire_date']) ? $live['expire_date'] : $today;
        $newExpireDate = date('Y-m-d', strtotime($base . " +{$days} days"));

        return $this->applyExpireDate($live, $newExpireDate, "{$days}일 연장되었습니다.");
    }

    // 상세화면의 달력 직접 지정 - 계산 없이 관리자가 고른 날짜를 그대로 만료일로 반영.
    public function setExpireDate($apply_no, string $newExpireDate): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $newExpireDate)) {
            return AT_Result::toFailData("만료일을 선택해주세요.");
        }

        $live = $this->getExtendableLive($apply_no);
        if (is_array($live) && isset($live['SUCCESS'])) {
            return $live;
        }

        if ($newExpireDate < $live['start_date']) {
            return AT_Result::toFailData("시작일({$live['start_date']}) 이전으로는 지정할 수 없습니다.");
        }

        return $this->applyExpireDate($live, $newExpireDate, "만료일이 변경되었습니다.");
    }

    // 연장/만료일 지정 공통 사전조건 - 설치완료된 service_live 1건을 반환하거나 실패결과를 반환
    private function getExtendableLive($apply_no)
    {
        $live = $this->serviceLiveModel->findByApplyNo($apply_no);
        if (!$live) {
            return AT_Result::toFailData("설치된 서비스가 없습니다.");
        }
        if ($live['install_status'] !== 'DONE') {
            return AT_Result::toFailData("아직 설치가 완료되지 않은 서비스입니다.");
        }
        return $live;
    }

    // 큐 적재 즉시 service_live.expire_date를 반영(낙관적 갱신)하고, action='extend'로 운영서버에도
    // 통보한다 - fire-and-forget push라 운영서버 반영이 실패해도 BO 표시값은 이미 바뀐 채로 남는다.
    // 만료일이 바뀌면 status도 새 만료일 기준으로 3단계로 같이 갱신한다(service:expire-check 배치와
    // 동일 규칙 - 오늘<만료일=정상, 만료일 당일~+15일=유예, 그 이후=종료).
    // queue payload에도 status를 실어 운영서버 bomul.member.status까지 맞춰준다.
    private function applyExpireDate(array $live, string $newExpireDate, string $successMsg): array
    {
        $server = $this->serverListModel->findByIdx($live['server_no']);
        if (!$server) {
            return AT_Result::toFailData("배정된 운영서버 정보를 찾을 수 없습니다.");
        }

        $today = date('Y-m-d');
        if ($newExpireDate > $today) {
            $newStatus = 'ACTIVE';
        } elseif ($newExpireDate >= date('Y-m-d', strtotime("-" . self::SUSPEND_GRACE_DAYS . " days"))) {
            $newStatus = 'SUSPENDED';
        } else {
            $newStatus = 'TERMINATED';
        }

        if (!$this->serviceLiveModel->updateExpireDate($live['live_no'], $newExpireDate)) {
            return AT_Result::toFailData("만료일 변경에 실패했습니다." . $this->dbErrorSuffix($this->serviceLiveModel));
        }
        $this->serviceLiveModel->updateStatus($live['live_no'], $newStatus);

        $payload = ['fid' => $live['fid'], 'expire_date' => $newExpireDate, 'status' => $this->remoteStatus($newStatus)];
        $queued = $this->queueModel->sql_save($live['live_no'], $server['server_no'], $live['fid'], $payload, 'extend');

        $msg = "{$successMsg} (변경된 만료일: {$newExpireDate}, 상태: " . $this->statusLabel($newStatus) . ")";
        if (!$queued) {
            // BO 표시값(expire_date/status)은 이미 바뀌었지만 운영서버에 통보할 큐가 안 만들어졌다 -
            // 이대로 두면 운영서버 bomul.member.status가 어긋난 채로 남으므로 관리자에게 반드시 알려야 한다.
            $msg .= " 단, 큐 적재에 실패해 운영서버에는 아직 반영되지 않았습니다." . $this->dbErrorSuffix($this->queueModel);
        }

        return AT_Result::toSuccessData($msg);
    }

    // 상세화면의 "서비스 상태" 직접 변경 - 요청(REQUEST)은 로컬 상태만 바꾸고 큐를 적재하지 않는다
    // (다음 service:collect-request 크론 실행 때 그 건이 픽업되어 큐에 적재된다).
    // 정상/유예/종료는 이미 설치완료된 건만 대상으로, 즉시 반영 + action='status'로 운영서버에 통보한다.
    public function changeStatus($apply_no, string $status): array
    {
        $status = strtoupper($status);
        if (!in_array($status, ['REQUEST', 'ACTIVE', 'SUSPENDED', 'TERMINATED'], true)) {
            return AT_Result::toFailData("잘못된 상태값입니다.");
        }

        $live = $this->serviceLiveModel->findByApplyNo($apply_no);
        if (!$live) {
            return AT_Result::toFailData("설치된 서비스가 없습니다.");
        }

        if ($status === 'REQUEST') {
            if (!$this->serviceLiveModel->updateStatus($live['live_no'], 'REQUEST')) {
                return AT_Result::toFailData("상태 변경에 실패했습니다." . $this->dbErrorSuffix($this->serviceLiveModel));
            }
            return AT_Result::toSuccessData("요청 상태로 변경되었습니다. 다음 세팅 수집 배치 때 큐에 적재됩니다.");
        }

        if ($live['install_status'] !== 'DONE') {
            return AT_Result::toFailData("아직 설치가 완료되지 않은 서비스입니다.");
        }

        $server = $this->serverListModel->findByIdx($live['server_no']);
        if (!$server) {
            return AT_Result::toFailData("배정된 운영서버 정보를 찾을 수 없습니다.");
        }

        if (!$this->serviceLiveModel->updateStatus($live['live_no'], $status)) {
            return AT_Result::toFailData("상태 변경에 실패했습니다." . $this->dbErrorSuffix($this->serviceLiveModel));
        }

        $payload = ['fid' => $live['fid'], 'status' => $this->remoteStatus($status)];
        $queued = $this->queueModel->sql_save($live['live_no'], $server['server_no'], $live['fid'], $payload, 'status');

        $msg = "상태가 '" . $this->statusLabel($status) . "'(으)로 변경되었습니다.";
        if (!$queued) {
            $msg .= " 단, 큐 적재에 실패해 운영서버에는 아직 반영되지 않았습니다." . $this->dbErrorSuffix($this->queueModel);
        }

        return AT_Result::toSuccessData($msg);
    }

    // 상세화면의 "설치진행상태" 직접 변경 - 운영서버 콜백을 기다리지 않고 관리자가 수동 보정할 때 사용
    // (예: 콜백이 유실됐거나 운영서버 쪽 상태와 어긋난 경우).
    public function changeInstallStatus($apply_no, string $installStatus): array
    {
        $installStatus = strtoupper($installStatus);
        if (!in_array($installStatus, ['PENDING', 'INSTALLING', 'DONE', 'FAILED'], true)) {
            return AT_Result::toFailData("잘못된 설치상태값입니다.");
        }

        $live = $this->serviceLiveModel->findByApplyNo($apply_no);
        if (!$live) {
            return AT_Result::toFailData("설치된 서비스가 없습니다.");
        }

        if (!$this->serviceLiveModel->updateInstallStatus($live['live_no'], $installStatus)) {
            return AT_Result::toFailData("설치상태 변경에 실패했습니다." . $this->dbErrorSuffix($this->serviceLiveModel));
        }

        return AT_Result::toSuccessData("설치진행상태가 변경되었습니다.");
    }

    private function statusLabel(string $status): string
    {
        $labels = ['REQUEST' => '요청', 'ACTIVE' => '정상', 'SUSPENDED' => '유예', 'TERMINATED' => '종료'];
        return $labels[$status] ?? $status;
    }

    // 로컬 service_live.status(4단계: 요청/정상/유예/종료) → 운영서버로 보내는 화면차단 3단계.
    // 정상=운영(전부 접근 가능), 유예=사용자종료(중개사 관리자는 로그인해서 결제/갱신 가능, 방문객 화면만 차단),
    // 종료=전체종료(관리자·사용자 둘 다 차단). REQUEST는 큐를 안 태우므로 매핑 대상이 아니다.
    private function remoteStatus(string $status): string
    {
        $map = ['ACTIVE' => 'OPERATING', 'SUSPENDED' => 'USER_CLOSED', 'TERMINATED' => 'ALL_CLOSED'];
        return $map[$status] ?? $status;
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
