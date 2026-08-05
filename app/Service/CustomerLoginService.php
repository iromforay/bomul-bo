<?php

namespace App\Service;

use App\Libraries\AT_Result;

// CI_Service를 상속하지 않는다 - LoginService(Admin/manager용)와 동일한 이유로, 로그인 시점엔
// SHOP_CODE가 아직 정해지지 않았다. Admin\LoginService와 세션 키를 분리해서 ad/www 로그인이 섞이지 않게 한다.
class CustomerLoginService extends CI_Service
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = model('App\Models\CustomerModel', true, $this->db);
    }

    public function login(string $customer_id, string $customer_pw): array
    {
        if ($customer_id === '' || $customer_pw === '') {
            return AT_Result::toFailData("아이디와 비밀번호를 입력해주세요.");
        }

        $row = $this->model->findByLoginId($customer_id);

        // customer_pw는 SHA-256(hex) 해시로 저장 (CustomerService::save()와 동일 정책) - hash_equals로 타이밍 공격 방지
        if (!$row || !hash_equals($row['customer_pw'], hash('sha256', $customer_pw))) {
            return AT_Result::toFailData("아이디 또는 비밀번호가 올바르지 않습니다.");
        }

        session()->set('customer_login', [
            'is_login'     => true,
            'CUSTOMER_IDX' => $row['customer_idx'],
            'CUSTOMER_ID'  => $row['customer_id'],
            'CUSTOMER_NAME'=> $row['customer_name'],
            'COMPANY_NAME' => $row['company_name'],
            'domain' => $row['fid'],
        ]);

        return AT_Result::toSuccessData();
    }

    public function logout(): void
    {
        session()->remove('customer_login');
    }
}
