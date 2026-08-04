<?php

namespace App\Service;

use App\Libraries\AT_Result;

// CI_Service를 상속하지 않는다 - CI_Service::__construct()는 SHOP_CODE가 이미 정의돼 있다고
// 가정하는데, 로그인은 그 SHOP_CODE를 이제 막 찾아내는 시점이라 순서가 반대다.
class LoginService
{
    private $db;
    private $model;

    public function __construct()
    {
        $this->db = db_connect("default");
        $this->model = model('App\Models\ManagerModel', true, $this->db);
    }

    public function login(string $manager_id, string $manager_pw): array
    {
        if ($manager_id === '' || $manager_pw === '') {
            return AT_Result::toFailData("아이디와 비밀번호를 입력해주세요.");
        }

        $row = $this->model->findByLoginId($manager_id);

        // manager_pw는 SHA-512(hex) 해시로 저장 - hash_equals로 타이밍 공격 방지
        if (!$row || !hash_equals($row['manager_pw'], hash('sha512', $manager_pw))) {
            return AT_Result::toFailData("아이디 또는 비밀번호가 올바르지 않습니다.");
        }

        // manager는 SHOP_CODE(중개사용 bo 쪽 개념)와 무관 - MB_ID는 로그인 세션 식별용일 뿐.
        session()->set('login', [
            'is_login' => true,
            'MB_ID'    => $row['manager_id'],
            'MB_NO'    => $row['manager_no'],
            'MB_NAME'  => $row['manager_name'],
        ]);

        return AT_Result::toSuccessData();
    }

    public function logout(): void
    {
        session()->remove('login');
    }
}
