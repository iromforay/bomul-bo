<?php

namespace App\Service;

use App\Entity\Member_Entity;
use App\Libraries\AT_Result;

// CI_Service를 상속하지 않는다 - ad(백오피스)와 서비스 신청 흐름은 SHOP_CODE를 안 쓰는데
// CI_Service 생성자는 SHOP_CODE가 정의돼 있다고 가정한다 (LoginService/ManagerService와 동일한 이유).
class MemberService
{
    private $db;
    private $model;

    public function __construct()
    {
        $this->db = db_connect("default");
        $this->model = model('App\Models\MemberModel', true, $this->db);
    }

    public function get_row($member_no)
    {
        $rows = $this->model->findbyid((int) $member_no);
        return $rows[0] ?? null;
    }

    public function save(array $data): array
    {
        $member_id     = trim($data['member_id'] ?? '');
        $member_pw     = (string) ($data['member_pw'] ?? '');
        $member_name   = trim($data['member_name'] ?? '');
        $company_name  = trim($data['company_name'] ?? '');
        $biz_reg_no    = trim($data['biz_reg_no'] ?? '');
        $broker_reg_no = trim($data['broker_reg_no'] ?? '');
        $phone         = trim($data['phone'] ?? '');
        $email         = trim($data['email'] ?? '');

        if ($member_id === '' || $member_pw === '' || $member_name === '' || $company_name === ''
            || $biz_reg_no === '' || $broker_reg_no === '' || $phone === '' || $email === '') {
            return AT_Result::toFailData("모든 항목을 입력해주세요.");
        }

        if ($this->model->existsByLoginId($member_id)) {
            return AT_Result::toFailData("이미 사용중인 아이디입니다.");
        }

        $entity = new Member_Entity();
        $entity->setMemberId($member_id);
        $entity->setMemberPw(hash('sha512', $member_pw));
        $entity->setMemberName($member_name);
        $entity->setCompanyName($company_name);
        $entity->setBizRegNo($biz_reg_no);
        $entity->setBrokerRegNo($broker_reg_no);
        $entity->setPhone($phone);
        $entity->setEmail($email);
        $entity->setUseYn('Y');
        // biz_reg_verified_yn / broker_reg_verified_yn은 MemberModel::sql_save()가 항상 'N'으로 고정 -
        // 가입 시점엔 검증되지 않은 상태로 시작해야 하므로 여기서 값을 넣어도 무시됨.

        if ($this->model->sql_save($entity)) {
            return AT_Result::toSuccessData();
        }

        return AT_Result::toFailData("가입에 실패했습니다.");
    }

    // 사업자등록번호/중개사무소등록번호 둘 다 검증완료(Y)여야 서비스 신청 가능
    public function isVerified($member_no): bool
    {
        $row = $this->get_row($member_no);
        if (!$row) {
            return false;
        }

        return ($row['biz_reg_verified_yn'] ?? 'N') === 'Y'
            && ($row['broker_reg_verified_yn'] ?? 'N') === 'Y';
    }

    // 서비스 신청 처리 컨트롤러가 실제 신청 로직을 타기 전에 제일 먼저 호출해야 하는 게이트.
    // 검증이 안 끝났으면 신청 자체를 막는다.
    public function guardServiceApply($member_no): array
    {
        if (!$this->isVerified($member_no)) {
            return AT_Result::toFailData("사업자등록번호/중개사무소등록번호 검증이 완료된 후 서비스 신청이 가능합니다.");
        }

        return AT_Result::toSuccessData();
    }

    // ad 백오피스에서 관리자가 서류 확인 후 검증 처리할 때 사용
    public function verify($member_no, bool $bizVerified, bool $brokerVerified): array
    {
        $entity = new Member_Entity();
        $entity->setMemberNo((int) $member_no);
        $entity->setBizRegVerifiedYn($bizVerified ? 'Y' : 'N');
        $entity->setBrokerRegVerifiedYn($brokerVerified ? 'Y' : 'N');

        if ($this->model->sql_edit($entity)) {
            return AT_Result::toSuccessData();
        }

        return AT_Result::toFailData("검증 처리에 실패했습니다.");
    }
}
