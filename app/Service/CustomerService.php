<?php

namespace App\Service;

use App\Entity\Customer_Entity;
use App\Libraries\AT_Result;
use App\Libraries\File_Upload;

// CI_Service를 상속하지 않는다 - ad(백오피스)와 이 고객 신청 흐름은 SHOP_CODE를 안 쓰는데
// CI_Service 생성자는 SHOP_CODE가 정의돼 있다고 가정한다 (LoginService/ManagerService와 동일한 이유).
class CustomerService extends CI_Service
{
    private $model;

    private const CERT_TYPES = ['biz', 'broker'];
    private const TEMP_DIR = 'customer/temp/';
    // 검증상태: N=대기, Y=검증완료, W=보류, F=검증실패
    private const VERIFY_STATUSES = ['N', 'Y', 'W', 'F'];

    public function __construct()
    {
        parent::__construct();
        $this->model = model('App\Models\CustomerModel', true, $this->db);
    }

    public function get_row($customer_idx)
    {
        $rows = $this->model->findbyid((int) $customer_idx);
        return $rows[0] ?? null;
    }

    public function get_list()
    {
        return $this->model->get_list();
    }

    // 회원가입 - id/pw/company_name/phone 4개만 필수. customer_name(담당자명)/biz_reg_no/broker_reg_no/
    // email/fid는 서비스 신청 단계(completeProfile())에서 받는다. 다만 Admin이 직접 등록할 땐 한 번에
    // 다 채워 보낼 수도 있어서, 넘어오면 그대로 저장하되 필수 조건에는 넣지 않는다.
    public function save(array $data): array
    {
        $customer_id   = trim($data['customer_id'] ?? '');
        $customer_pw   = (string) ($data['customer_pw'] ?? '');
        $company_name  = trim($data['company_name'] ?? '');
        $phone         = trim($data['phone'] ?? '');
        $customer_name = trim($data['customer_name'] ?? '');
        $biz_reg_no    = trim($data['biz_reg_no'] ?? '');
        $broker_reg_no = trim($data['broker_reg_no'] ?? '');
        $email         = trim($data['email'] ?? '');
        $fid           = trim($data['fid'] ?? '');

        if ($customer_id === '' || $customer_pw === '' || $company_name === '' || $phone === '') {
            return AT_Result::toFailData("아이디/비밀번호/중개사무소명/휴대폰번호를 입력해주세요.");
        }

        if ($this->model->existsByLoginId($customer_id)) {
            return AT_Result::toFailData("이미 사용중인 아이디입니다.");
        }

        if ($fid !== '' && !preg_match('/^[a-z0-9][a-z0-9-]{1,30}[a-z0-9]$/', $fid)) {
            return AT_Result::toFailData("도메인 주소는 영문 소문자/숫자/하이픈만 사용할 수 있습니다.");
        }
        if ($fid !== '' && $this->model->existsByFid($fid)) {
            return AT_Result::toFailData("이미 사용중인 도메인 주소입니다.");
        }

        $entity = new Customer_Entity();
        $entity->setCustomerId($customer_id);
        // SHA-256(64자) 고정 - 중앙 member.user_pwd가 VARCHAR(64)라 이관 시 해시를 그대로 복사하려면
        // 같은 알고리즘이어야 한다(원문 비밀번호 없이는 재해시 불가). manager(SHA-512)와는 별개 정책.
        $entity->setCustomerPw(hash('sha256', $customer_pw));
        $entity->setCompanyName($company_name);
        $entity->setPhone($phone);
        $entity->setUseYn('Y');
        if ($customer_name !== '') {
            $entity->setCustomerName($customer_name);
        }
        if ($biz_reg_no !== '') {
            $entity->setBizRegNo($biz_reg_no);
        }
        if ($broker_reg_no !== '') {
            $entity->setBrokerRegNo($broker_reg_no);
        }
        if ($email !== '') {
            $entity->setEmail($email);
        }
        if ($fid !== '') {
            $entity->setFid($fid);
        }
        // biz_reg_verified_yn / broker_reg_verified_yn / migrated_yn은 CustomerModel::sql_save()가
        // 항상 'N'으로 고정 저장 - 가입 시점엔 미검증/미이관 상태로 시작해야 하므로 여기서 넣어도 무시됨.

        $customer_idx = $this->model->sql_save($entity);
        if (!$customer_idx) {
            return AT_Result::toFailData("가입에 실패했습니다.");
        }

        // 방금 만든 계정이라 기존 파일은 없음 - 드롭존에서 temp로 올려둔 파일이 있으면 이 시점에 이동
        $emptyRow = ['biz_reg_file_dir' => null, 'biz_reg_file_name' => null, 'broker_reg_file_dir' => null, 'broker_reg_file_name' => null];
        $this->finalizeCertificateFiles((int) $customer_idx, $emptyRow, $data);

        return AT_Result::toSuccessData("고객 등록에 성공했습니다.", ['customer_idx' => (int) $customer_idx]);
    }

    // 서비스 신청 단계의 "사업자정보 및 도메인" 입력 - customer_name(담당자명)/email/biz_reg_no/
    // broker_reg_no/fid를 채운다. verified_yn/use_yn/service_yn 등 상태값은 entity에 아예 세팅하지
    // 않아서 sql_edit()이 건드리지 않는다 (edit()과 달리 이 메서드는 고객 본인이 호출하는 경로라
    // 관리자 전용 상태값을 실수로라도 덮어쓰면 안 됨).
    public function completeProfile(int $customer_idx, array $data): array
    {
        $customer_name = trim($data['customer_name'] ?? '');
        $email         = trim($data['email'] ?? '');
        $biz_reg_no    = trim($data['biz_reg_no'] ?? '');
        $broker_reg_no = trim($data['broker_reg_no'] ?? '');
        $fid           = trim($data['fid'] ?? '');

        if ($customer_name === '' || $email === '' || $biz_reg_no === '' || $broker_reg_no === '' || $fid === '') {
            return AT_Result::toFailData("모든 항목을 입력해주세요.");
        }

        if (!preg_match('/^[a-z0-9][a-z0-9-]{1,30}[a-z0-9]$/', $fid)) {
            return AT_Result::toFailData("도메인 주소는 영문 소문자/숫자/하이픈만 사용할 수 있습니다.");
        }
        if ($this->model->existsByFid($fid, $customer_idx)) {
            return AT_Result::toFailData("이미 사용중인 도메인 주소입니다.");
        }

        $entity = new Customer_Entity();
        $entity->setCustomerIdx($customer_idx);
        $entity->setCustomerName($customer_name);
        $entity->setEmail($email);
        $entity->setBizRegNo($biz_reg_no);
        $entity->setBrokerRegNo($broker_reg_no);
        $entity->setFid($fid);

        if (!$this->model->sql_edit($entity)) {
            return AT_Result::toFailData("저장에 실패했습니다.");
        }

        return AT_Result::toSuccessData();
    }

    // 서비스 신청 화면에서 "사업자정보 및 도메인"을 또 물어봐야 하는지 판단하는 기준 -
    // 이전 신청에서 이미 다 채웠으면(=completeProfile 완료) 그 화면을 건너뛰고(pass) 바로 상품 확인으로.
    public function isProfileComplete(?array $row): bool
    {
        if (!$row) {
            return false;
        }

        return trim((string) ($row['customer_name'] ?? '')) !== ''
            && trim((string) ($row['email'] ?? '')) !== ''
            && trim((string) ($row['biz_reg_no'] ?? '')) !== ''
            && trim((string) ($row['broker_reg_no'] ?? '')) !== ''
            && trim((string) ($row['fid'] ?? '')) !== '';
    }

    // 고객 상세 > 수정 화면 전체 저장 - 기본정보 + 각종 상태값(검증여부/계정사용여부/서비스사용여부/
    // 서비스만료일/fid)까지 전부 여기서 처리. 등록(write) 화면엔 없는, 수정 화면 전용 항목들이다.
    public function edit($customer_idx, array $data): array
    {
        $customer_idx   = (int) $customer_idx;
        $customer_id   = trim($data['customer_id'] ?? '');
        $customer_name = trim($data['customer_name'] ?? '');
        $company_name  = trim($data['company_name'] ?? '');
        $biz_reg_no    = trim($data['biz_reg_no'] ?? '');
        $broker_reg_no = trim($data['broker_reg_no'] ?? '');
        $phone         = trim($data['phone'] ?? '');
        $email         = trim($data['email'] ?? '');
        $fid           = trim($data['fid'] ?? '');

        if ($customer_id === '' || $customer_name === '' || $company_name === ''
            || $biz_reg_no === '' || $broker_reg_no === '' || $phone === '' || $email === '') {
            return AT_Result::toFailData("모든 항목을 입력해주세요.");
        }

        $row = $this->get_row($customer_idx);
        if (!$row) {
            return AT_Result::toFailData("존재하지 않는 고객입니다.");
        }

        if ($this->model->existsByLoginId($customer_id, $customer_idx)) {
            return AT_Result::toFailData("이미 사용중인 아이디입니다.");
        }

        if ($fid !== '' && $this->model->existsByFid($fid, $customer_idx)) {
            return AT_Result::toFailData("이미 사용중인 fid입니다.");
        }

        $entity = new Customer_Entity();
        $entity->setCustomerIdx($customer_idx);
        $entity->setCustomerId($customer_id);
        $entity->setCustomerName($customer_name);
        $entity->setCompanyName($company_name);
        $entity->setBizRegNo($biz_reg_no);
        $entity->setBrokerRegNo($broker_reg_no);
        $entity->setPhone($phone);
        $entity->setEmail($email);

        // 비밀번호는 입력했을 때만 변경
        if (!empty($data['customer_pw'])) {
            $entity->setCustomerPw(hash('sha256', $data['customer_pw']));
        }

        $entity->setBizRegVerifiedYn($this->normalizeVerifyStatus($data['biz_reg_verified_yn'] ?? null));
        $entity->setBrokerRegVerifiedYn($this->normalizeVerifyStatus($data['broker_reg_verified_yn'] ?? null));
        $entity->setUseYn(($data['use_yn'] ?? 'Y') === 'N' ? 'N' : 'Y');
        $entity->setServiceYn(($data['service_yn'] ?? 'N') === 'Y' ? 'Y' : 'N');

        if (!empty($data['service_expire_date'])) {
            $entity->setServiceExpireDate($data['service_expire_date']);
        }
        if ($fid !== '') {
            $entity->setFid($fid);
        }

        if (!$this->model->sql_edit($entity)) {
            return AT_Result::toFailData("수정에 실패했습니다.");
        }

        // del_img=biz|broker로 넘어온 항목은 실삭제, 새 temp 파일이 넘어온 항목은 실경로로 이동
        $this->finalizeCertificateFiles($customer_idx, $row, $data);

        return AT_Result::toSuccessData("고객 정보를 수정했습니다.");
    }

    // 드래그드랍 위젯이 드롭 즉시 호출 - customer/temp/에만 올려두고, 실제 소유자(customer_idx)가
    // 정해지는 저장(save/edit) 시점에 finalizeCertificateFiles()가 최종 경로로 옮긴다.
    // customer_idx를 몰라도 되는 구조라 등록 화면(신규 가입)에서도, 나중에 만들 사용자 페이지에서도
    // 그대로 재사용 가능하다.
    public function uploadTemp(string $type, array $file): array
    {
        if (!in_array($type, self::CERT_TYPES, true)) {
            return AT_Result::toFailData("잘못된 요청입니다.");
        }

        $uploader = new File_Upload();
        $result = $uploader->setTargetDir(self::TEMP_DIR)->upload($file);

        if ($result['SUCCESS'] !== 'TRUE') {
            return $result;
        }

        // FE 미리보기용 - 이미지가 아니어도(pdf 등) 링크로 열어볼 수 있게 항상 내려준다.
        $result['RECORD']['temp_url'] = SITE_PDS . $result['RECORD']['file_path'] . $result['RECORD']['file_name'];

        return $result;
    }

    // save()/edit() 공통 - del_img[]로 넘어온 타입은 실삭제 후 DB 값 비움,
    // {type}_reg_file_name으로 새 temp 파일명이 넘어온 타입은 temp -> customer/{no}/{type}_reg/ 로 이동.
    // 둘 다 아니면 기존 파일 그대로 유지(entity에 아무것도 세팅 안 해서 sql_edit이 자동 스킵).
    private function finalizeCertificateFiles(int $customer_idx, array $row, array $data): void
    {
        $delList = (array) ($data['del_img'] ?? []);
        $entity = new Customer_Entity();
        $entity->setCustomerIdx($customer_idx);
        $touched = false;

        foreach (self::CERT_TYPES as $type) {
            $touched = $this->applyCertificateFile($entity, $row, $type, $data, $delList) || $touched;
        }

        if ($touched) {
            $this->model->sql_edit($entity);
        }
    }

    // $entity에 해당 타입의 파일 필드를 세팅했으면 true 반환 (DB 갱신이 필요하다는 뜻)
    private function applyCertificateFile(Customer_Entity $entity, array $row, string $type, array $data, array $delList): bool
    {
        $isBiz   = $type === 'biz';
        $dirKey  = $isBiz ? 'biz_reg_file_dir'  : 'broker_reg_file_dir';
        $nameKey = $isBiz ? 'biz_reg_file_name' : 'broker_reg_file_name';
        $uploader = new File_Upload();

        // 1) 삭제 요청 우선 처리
        if (in_array($type, $delList, true)) {
            if (!empty($row[$nameKey])) {
                $uploader->delete(['file_path' => $row[$dirKey], 'file_name' => $row[$nameKey]]);
            }
            $isBiz ? $entity->setBizRegFileDir('') : $entity->setBrokerRegFileDir('');
            $isBiz ? $entity->setBizRegFileName('') : $entity->setBrokerRegFileName('');
            return true;
        }

        // 2) 새 temp 파일이 넘어왔으면 실제 경로로 이동
        $tempName = trim($data[$type . '_reg_file_name'] ?? '');
        if ($tempName === '') {
            return false;
        }

        $toDir = "customer/{$entity->getCustomerIdx()}/{$type}_reg/";
        if (!$uploader->move(self::TEMP_DIR, $tempName, $toDir)) {
            return false; // temp 파일이 이미 없어졌거나 하는 경우 - 조용히 스킵
        }

        // 기존 파일이 있었으면 교체이므로 실삭제
        if (!empty($row[$nameKey])) {
            $uploader->delete(['file_path' => $row[$dirKey], 'file_name' => $row[$nameKey]]);
        }

        $isBiz ? $entity->setBizRegFileDir($toDir) : $entity->setBrokerRegFileDir($toDir);
        $isBiz ? $entity->setBizRegFileName($tempName) : $entity->setBrokerRegFileName($tempName);

        return true;
    }

    // N/Y/W/F가 아닌 값(빈 값, 조작된 값 등)은 안전하게 대기(N)로 취급
    private function normalizeVerifyStatus(?string $value): string
    {
        return in_array($value, self::VERIFY_STATUSES, true) ? $value : 'N';
    }

    // 사업자등록번호/중개사무소등록번호 둘 다 검증완료(Y)여야 서비스 신청 가능
    public function isVerified($customer_idx): bool
    {
        $row = $this->get_row($customer_idx);
        if (!$row) {
            return false;
        }

        return ($row['biz_reg_verified_yn'] ?? 'N') === 'Y'
            && ($row['broker_reg_verified_yn'] ?? 'N') === 'Y';
    }

    // 서비스 신청 처리 컨트롤러가 실제 신청 로직을 타기 전에 제일 먼저 호출해야 하는 게이트.
    // 검증이 안 끝났으면 신청 자체를 막는다.
    public function guardServiceApply($customer_idx): array
    {
        if (!$this->isVerified($customer_idx)) {
            return AT_Result::toFailData("사업자등록번호/중개사무소등록번호 검증이 완료된 후 서비스 신청이 가능합니다.");
        }

        return AT_Result::toSuccessData();
    }

    // ad 백오피스에서 관리자가 서류 확인 후 검증 처리만 빠르게 할 때 사용 (edit() 없이 단독 호출 가능)
    // $bizStatus/$brokerStatus: N=대기, Y=검증완료, W=보류, F=검증실패
    public function verify($customer_idx, string $bizStatus, string $brokerStatus): array
    {
        $entity = new Customer_Entity();
        $entity->setCustomerIdx((int) $customer_idx);
        $entity->setBizRegVerifiedYn($this->normalizeVerifyStatus($bizStatus));
        $entity->setBrokerRegVerifiedYn($this->normalizeVerifyStatus($brokerStatus));

        if ($this->model->sql_edit($entity)) {
            return AT_Result::toSuccessData();
        }

        return AT_Result::toFailData("검증 처리에 실패했습니다.");
    }

    /*
     * TODO: 결제 완료 후 실사용자 전환(이관) 로직 - 아직 미구현.
     * fid는 서비스 신청 단계에서 고객이 이미 입력/선택해 customer.fid에 저장돼 있다고 가정
     * (CustomerModel::existsByFid()로 신청 시점에 customer 내 중복은 체크하지만, 최종 발급 가능
     * 여부는 이미 활성화된 중앙 member.fid까지 같이 확인해야 함 - 신청 처리 로직에서 필요).
     * 결제 기능/테넌트 DB 프로비저닝 방식이 정해지면 아래를 구현:
     *   1. 중앙 member insert: user_id=customer_id, user_pwd=customer_pw(그대로 복사, 둘 다 SHA-256),
     *      fid=customer.fid, service='Y', fname=company_name, regist_num=biz_reg_no,
     *      mb_name=customer_name, mb_id=customer_id, mb_hp=phone, mb_email=email
     *      (ftel/faddress1/faddress2는 중앙 member엔 있으나 customer엔 아직 없음 - 필요시 추가 수집)
     *   2. {fid} 테넌트 DB 생성 후 basic 테이블에 broker_reg_no 등 시딩 (Basic_Entity 참고 -
     *      중앙 member엔 중개사무소등록번호 컬럼이 없어서 테넌트 basic 쪽으로 감)
     *   3. customer.member_no/migrated_yn/migrated_date 갱신
     */
    public function migrateToMember($customer_idx): array
    {
        return AT_Result::toFailData("이관 로직은 아직 구현되지 않았습니다. 결제 기능과 함께 설계 필요.");
    }
}
