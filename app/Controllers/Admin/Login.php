<?php

namespace App\Controllers\Admin;

use App\Service\LoginService;

class Login extends BaseController
{
    // 로그인 화면/로그아웃은 세션이 없는 게 정상 상태라 BaseController의 자동 auth() 대상에서 제외
    protected $skipAuth = true;

    public function getIndex()
    {
        // 이미 로그인된 상태면 로그인 화면을 또 볼 필요가 없으니 대시보드로
        if (session('login')['is_login'] ?? false) {
            return redirect()->to(SITE . '/Home');
        }

        $error = session()->getFlashdata('login_error');
        echo view('Admin/login', ['error' => $error]);
    }

    public function postIndex()
    {
        $mb_id = trim((string) $this->request->getPost('mb_id'));
        $mb_pw = (string) $this->request->getPost('mb_pw');

        $service = new LoginService();
        $result = $service->login($mb_id, $mb_pw);

        if ($result['SUCCESS'] === 'TRUE') {
            return redirect()->to(SITE . '/Home');
        }

        session()->setFlashdata('login_error', $result['MSG']);
        return redirect()->to(SITE . '/Login');
    }

    public function getLogout()
    {
        (new LoginService())->logout();
        return redirect()->to(SITE . '/Login');
    }
}
