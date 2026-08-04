<?php

namespace App\Service;

use App\Entity\Customer_Entity;
use App\Libraries\AT_Result;

// CI_Service를 상속하지 않는다 - ad(백오피스)와 이 고객 신청 흐름은 SHOP_CODE를 안 쓰는데
// CI_Service 생성자는 SHOP_CODE가 정의돼 있다고 가정한다 (LoginService/ManagerService와 동일한 이유).
class CustomerService
{
    private $db;
    private $model;

    public function __construct()
    {
        $this->db = db_connect("default");
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

    public function save(array $data): array
    {
        $customer_id   = trim($data['customer_id'] ?? '');
        $customer_pw   = (string) ($data['customer_pw'] ?? '');
        $customer_name = trim($data['customer_name'] ?? '');
        $company_name  = trim($data['company_name'] ?? '');
        $biz_reg_no    = trim($data['biz_reg_no'] ?? '');
        $broker_reg_no = trim($data['broker_reg_no'] ?? '');
        $phone         = trim($data['phone'] ?? '');
        $email         = trim($data['email'] ?? '');

        if ($customer_id === '' || $customer_pw === '' || $customer_name === '' || $company_name === ''
            || $biz_reg_no === '' || $broker_reg_no === '' || $phone === '' || $email === '') {
            return AT_Result::toFailData("모든 항목을 입력해주세요.");
        }

        if ($this->model->existsByLoginId($customer_id)) {
            return AT_Result::toFailData("이미 사용중인 아이디입니다.");
        }

        $entity = new Customer_Entity();
        $entity->setCustomerId($customer_id);
        // SHA-256(64자) 고정 - 중앙 member.user_pwd가 VARCHAR(64)라 이관 시 해시를 그대로 복사하려면
        // 같은 알고리즘이어야 한다(원문 비밀번호 없이는 재해시 불가). manager(SHA-512)와는 별개 정책.
        $entity->setCustomerPw(hash('sha256', $customer_pw));
        $entity->setCustomerName($customer_name);
        $entity->setCompanyName($company_name);
        $entity->setBizRegNo($biz_reg_no);
        $entity->setBrokerRegNo($broker_reg_no);
        $entity->setPhone($phone);
        $entity->setEmail($email);
        $entity->setUseYn('Y');
        // biz_reg_verified_yn / broker_reg_verified_yn / migrated_yn은 CustomerModel::sql_save()가
        // 항상 'N'으로 고정 저장 - 가입 시점엔 미검증/미이관 상태로 시작해야 하므로 여기서 넣어도 무시됨.

        if ($this->model->sql_save($entity)) {
            return AT_Result::toSuccessData();
        }

        return AT_Result::toFailData("가입에 실패했습니다.");
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

    // ad 백오피스에서 관리자가 서류 확인 후 검증 처리할 때 사용
    public function verify($customer_no, bool $bizVerified, bool $brokerVerified): array
    {
        $entity = new Customer_Entity();
        $entity->setCustomerNo((int) $customer_no);
        $entity->setBizRegVerifiedYn($bizVerified ? 'Y' : 'N');
        $entity->setBrokerRegVerifiedYn($brokerVerified ? 'Y' : 'N');

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
    public function migrateToMember($customer_no): array
    {
        return AT_Result::toFailData("이관 로직은 아직 구현되지 않았습니다. 결제 기능과 함께 설계 필요.");
    }
}
