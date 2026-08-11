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

    public function get_row($customer_no)
    {
        $rows = $this->model->findbyid((int) $customer_no);
        return $rows[0] ?? null;
    }

    public function get_list()
    {
        return $this->model->get_list();
    }

    // 회원가입 - id/pw/company_name/phone 4개만 필수. customer_name(담당자명)/biz_reg_no/broker_reg_no/
    // email은 서비스 신청 단계(completeProfile())에서 받는다. 다만 Admin이 직접 등록할 땐 한 번에
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

        if ($customer_id === '' || $customer_pw === '' || $company_name === '' || $phone === '') {
            return AT_Result::toFailData("아이디/비밀번호/중개사무소명/휴대폰번호를 입력해주세요.");
        }

        if ($this->model->existsByLoginId($customer_id)) {
            return AT_Result::toFailData("이미 사용중인 아이디입니다.");
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
        // biz_reg_verified_yn / broker_reg_verified_yn / migrated_yn은 CustomerModel::sql_save()가
        // 항상 'N'으로 고정 저장 - 가입 시점엔 미검증/미이관 상태로 시작해야 하므로 여기서 넣어도 무시됨.

        $customer_no = $this->model->sql_save($entity);
        if (!$customer_no) {
            return AT_Result::toFailData("가입에 실패했습니다.");
        }

        // 방금 만든 계정이라 기존 파일은 없음 - 드롭존에서 temp로 올려둔 파일이 있으면 이 시점에 이동
        $emptyRow = ['biz_reg_file_dir' => null, 'biz_reg_file_name' => null, 'broker_reg_file_dir' => null, 'broker_reg_file_name' => null];
        $this->finalizeCertificateFiles((int) $customer_no, $emptyRow, $data);

        return AT_Result::toSuccessData("고객 등록에 성공했습니다.", ['customer_no' => (int) $customer_no]);
    }

    // 서비스 신청 단계의 "사업자정보" 입력 - customer_name(담당자명)/email/biz_reg_no/broker_reg_no를
    // 채운다. verified_yn/use_yn/service_yn 등 상태값은 entity에 아예 세팅하지 않아서 sql_edit()이
    // 건드리지 않는다 (edit()과 달리 이 메서드는 고객 본인이 호출하는 경로라 관리자 전용 상태값을
    // 실수로라도 덮어쓰면 안 됨). 희망 도메인(sub_domain)은 여기서 다루지 않는다 - 서비스 신청
    // 1건마다 다를 수 있어 service_apply 쪽에서 신청서별로 받는다.
    public function completeProfile(int $customer_no, array $data): array
    {
        $customer_name = trim($data['customer_name'] ?? '');
        $email         = trim($data['email'] ?? '');
        $biz_reg_no    = trim($data['biz_reg_no'] ?? '');
        $broker_reg_no = trim($data['broker_reg_no'] ?? '');

        if ($customer_name === '' || $email === '' || $biz_reg_no === '' || $broker_reg_no === '') {
            return AT_Result::toFailData("모든 항목을 입력해주세요.");
        }

        $entity = new Customer_Entity();
        $entity->setCustomerNo($customer_no);
        $entity->setCustomerName($customer_name);
        $entity->setEmail($email);
        $entity->setBizRegNo($biz_reg_no);
        $entity->setBrokerRegNo($broker_reg_no);

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
            && trim((string) ($row['broker_reg_no'] ?? '')) !== '';
    }

    // 고객 상세 > 수정 화면 전체 저장 - 기본정보 + 각종 상태값(검증여부/계정사용여부/서비스사용여부/
    // 서비스만료일/fid)까지 전부 여기서 처리. 등록(write) 화면엔 없는, 수정 화면 전용 항목들이다.
    public function edit($customer_no, array $data): array
    {
        $customer_no   = (int) $customer_no;
        $customer_id   = trim($data['customer_id'] ?? '');
        $customer_name = trim($data['customer_name'] ?? '');
        $company_name  = trim($data['company_name'] ?? '');
        $biz_reg_no    = trim($data['biz_reg_no'] ?? '');
        $broker_reg_no = trim($data['broker_reg_no'] ?? '');
        $phone         = trim($data['phone'] ?? '');
        $email         = trim($data['email'] ?? '');

        if ($customer_id === '' || $customer_name === '' || $company_name === ''
            || $biz_reg_no === '' || $broker_reg_no === '' || $phone === '' || $email === '') {
            return AT_Result::toFailData("모든 항목을 입력해주세요.");
        }

        $row = $this->get_row($customer_no);
        if (!$row) {
            return AT_Result::toFailData("존재하지 않는 고객입니다.");
        }

        if ($this->model->existsByLoginId($customer_id, $customer_no)) {
            return AT_Result::toFailData("이미 사용중인 아이디입니다.");
        }

        $entity = new Customer_Entity();
        $entity->setCustomerNo($customer_no);
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

        if (!$this->model->sql_edit($entity)) {
            return AT_Result::toFailData("수정에 실패했습니다.");
        }

        // del_img=biz|broker로 넘어온 항목은 실삭제, 새 temp 파일이 넘어온 항목은 실경로로 이동
        $this->finalizeCertificateFiles($customer_no, $row, $data);

        return AT_Result::toSuccessData("고객 정보를 수정했습니다.");
    }

    // 마이페이지(#1) "필수 서류 제출" - 등록번호(biz_reg_no/broker_reg_no) + 서류 파일만 다룬다.
    // completeProfile()과 달리 담당자명/이메일은 안 건드림(그건 서비스 신청 단계 몫).
    // 등록번호나 파일이 실제로 바뀐 항목만 검증(verified_yn)을 대기(N)로 되돌린다 - 아무것도 안
    // 바뀌었는데 재제출했다고 매번 리셋하면 관리자가 어드민에서 검증완료(Y) 처리해둔 게 고객이
    // 마이페이지를 다시 열기만 해도 조용히 사라지는 문제가 생기므로, 실제 변경분에 한정한다.
    public function saveDocuments($customer_no, array $data): array
    {
        $customer_no   = (int) $customer_no;
        $biz_reg_no    = trim($data['biz_reg_no'] ?? '');
        $broker_reg_no = trim($data['broker_reg_no'] ?? '');

        if ($biz_reg_no === '' || $broker_reg_no === '') {
            return AT_Result::toFailData("사업자등록번호와 중개사무소등록번호를 입력해주세요.");
        }

        $row = $this->get_row($customer_no);
        if (!$row) {
            return AT_Result::toFailData("존재하지 않는 고객입니다.");
        }

        $delList = (array) ($data['del_img'] ?? []);

        // 검증완료(Y)된 서류는 삭제 금지 - 화면에서 삭제 버튼을 안 보여주는 것과는 별개로, 요청을
        // 직접 조작해서 넘겨도 서버에서 막아야 한다(재업로드로 교체하는 건 막지 않음 - 새 파일이
        // 오면 어차피 검증이 대기로 리셋되고 관리자가 다시 확인함).
        if (in_array('biz', $delList, true) && ($row['biz_reg_verified_yn'] ?? 'N') === 'Y') {
            return AT_Result::toFailData("검증완료된 사업자등록증은 삭제할 수 없습니다.");
        }
        if (in_array('broker', $delList, true) && ($row['broker_reg_verified_yn'] ?? 'N') === 'Y') {
            return AT_Result::toFailData("검증완료된 중개사무소등록증은 삭제할 수 없습니다.");
        }

        $bizChanged = $biz_reg_no !== trim((string) ($row['biz_reg_no'] ?? ''))
            || trim((string) ($data['biz_reg_file_name'] ?? '')) !== ''
            || in_array('biz', $delList, true);
        $brokerChanged = $broker_reg_no !== trim((string) ($row['broker_reg_no'] ?? ''))
            || trim((string) ($data['broker_reg_file_name'] ?? '')) !== ''
            || in_array('broker', $delList, true);

        $entity = new Customer_Entity();
        $entity->setCustomerNo($customer_no);
        $entity->setBizRegNo($biz_reg_no);
        $entity->setBrokerRegNo($broker_reg_no);
        if ($bizChanged) {
            $entity->setBizRegVerifiedYn('N');
        }
        if ($brokerChanged) {
            $entity->setBrokerRegVerifiedYn('N');
        }

        if (!$this->model->sql_edit($entity)) {
            return AT_Result::toFailData("저장에 실패했습니다.");
        }

        $this->finalizeCertificateFiles($customer_no, $row, $data);

        return AT_Result::toSuccessData("필수 서류가 제출되었습니다. 검증 완료까지 영업일 기준 1~2일 소요됩니다.");
    }

    // 드래그드랍 위젯이 드롭 즉시 호출 - customer/temp/에만 올려두고, 실제 소유자(customer_no)가
    // 정해지는 저장(save/edit) 시점에 finalizeCertificateFiles()가 최종 경로로 옮긴다.
    // customer_no를 몰라도 되는 구조라 등록 화면(신규 가입)에서도, 나중에 만들 사용자 페이지에서도
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
    private function finalizeCertificateFiles(int $customer_no, array $row, array $data): void
    {
        $delList = (array) ($data['del_img'] ?? []);
        $entity = new Customer_Entity();
        $entity->setCustomerNo($customer_no);
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

        // public/pds/{customer_no}/ 아래 - 파일명 자체가 md5 해시라 biz/broker 둘 다 같은 폴더에
        // 섞여도 충돌 없음(2026-08-10 확정 - 예전엔 customer/{no}/{type}_reg/였음).
        $toDir = "{$entity->getCustomerNo()}/";
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
    public function isVerified($customer_no): bool
    {
        $row = $this->get_row($customer_no);
        if (!$row) {
            return false;
        }

        return ($row['biz_reg_verified_yn'] ?? 'N') === 'Y'
            && ($row['broker_reg_verified_yn'] ?? 'N') === 'Y';
    }

    // 서비스 신청 처리 컨트롤러가 실제 신청 로직을 타기 전에 제일 먼저 호출해야 하는 게이트.
    // 검증이 안 끝났으면 신청 자체를 막는다.
    public function guardServiceApply($customer_no): array
    {
        if (!$this->isVerified($customer_no)) {
            return AT_Result::toFailData("사업자등록번호/중개사무소등록번호 검증이 완료된 후 서비스 신청이 가능합니다.");
        }

        return AT_Result::toSuccessData();
    }

    // ad 백오피스에서 관리자가 서류 확인 후 검증 처리만 빠르게 할 때 사용 (edit() 없이 단독 호출 가능)
    // $bizStatus/$brokerStatus: N=대기, Y=검증완료, W=보류, F=검증실패
    public function verify($customer_no, string $bizStatus, string $brokerStatus): array
    {
        $entity = new Customer_Entity();
        $entity->setCustomerNo((int) $customer_no);
        $entity->setBizRegVerifiedYn($this->normalizeVerifyStatus($bizStatus));
        $entity->setBrokerRegVerifiedYn($this->normalizeVerifyStatus($brokerStatus));

        if ($this->model->sql_edit($entity)) {
            return AT_Result::toSuccessData();
        }

        return AT_Result::toFailData("검증 처리에 실패했습니다.");
    }

    /*
     * TODO: 결제 완료 후 실사용자 전환(이관) 로직 - 아직 미구현.
     * fid(도메인)는 customer가 아니라 서비스 신청 1건(service_apply.sub_domain → 승인되면
     * service_live.fid)에 속한 값이다 - 한 고객이 여러 서비스를 신청할 수 있으므로 이관은
     * customer_no 단위가 아니라 승인된 service_live 1건 단위로 이뤄져야 한다.
     * 결제 기능/테넌트 DB 프로비저닝 방식이 정해지면 아래를 구현:
     *   1. 중앙 member insert: user_id=customer_id, user_pwd=customer_pw(그대로 복사, 둘 다 SHA-256),
     *      fid=service_live.fid, service='Y', fname=company_name, regist_num=biz_reg_no,
     *      mb_name=customer_name, mb_id=customer_id, mb_hp=phone, mb_email=email
     *      (ftel/faddress1/faddress2는 중앙 member엔 있으나 customer엔 아직 없음 - 필요시 추가 수집)
     *   2. {fid} 테넌트 DB 생성 후 basic 테이블에 broker_reg_no 등 시딩 (Basic_Entity 참고 -
     *      중앙 member엔 중개사무소등록번호 컬럼이 없어서 테넌트 basic 쪽으로 감)
     *   3. customer.member_no/migrated_yn/migrated_date 갱신 (해당 service_live 건에 한해)
     */
    public function migrateToMember($customer_no): array
    {
        return AT_Result::toFailData("이관 로직은 아직 구현되지 않았습니다. 결제 기능과 함께 설계 필요.");
    }
}
