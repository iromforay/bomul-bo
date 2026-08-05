<?php

namespace App\Controllers;

use App\Service\CustomerLoginService;

// 공개 사이트(www)용 고객 로그인 - Admin\Login과는 세션 키('customer_login')와 대상 테이블(customer)이 분리되어 있다.
class Login extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    public function getindex()
    {
        if (session('customer_login')['is_login'] ?? false) {
            return redirect()->to('/');
        }

        $data = [];
        $data['error'] = session()->getFlashdata('login_error');
        echo $this->view("default", "login", $data);
    }

    public function postindex()
    {
        $customer_id = trim((string) $this->request->getPost('customer_id'));
        $customer_pw = (string) $this->request->getPost('customer_pw');

        $service = new CustomerLoginService();
        $result = $service->login($customer_id, $customer_pw);

        if ($result['SUCCESS'] !== 'TRUE') {
            session()->setFlashdata('login_error', $result['MSG']);
            return redirect()->to('/login');
        }

        return redirect()->to('/');
    }

    public function getlogout()
    {
        (new CustomerLoginService())->logout();
        return redirect()->to('/');
    }
}
