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
        $redirectUrl = $this->sanitizeRedirect((string) $this->request->getGet('redirect_url'));

        if (session('customer_login')['is_login'] ?? false) {
            return redirect()->to($redirectUrl !== '' ? $redirectUrl : '/');
        }

        $data = [];
        $data['error'] = session()->getFlashdata('login_error');
        $data['redirectUrl'] = $redirectUrl;
        echo $this->view("default", "login", $data);
    }

    public function postindex()
    {
        $customer_id = trim((string) $this->request->getPost('customer_id'));
        $customer_pw = (string) $this->request->getPost('customer_pw');
        $redirectUrl = $this->sanitizeRedirect((string) $this->request->getPost('redirect_url'));

        $service = new CustomerLoginService();
        $result = $service->login($customer_id, $customer_pw);

        if ($result['SUCCESS'] !== 'TRUE') {
            session()->setFlashdata('login_error', $result['MSG']);
            return redirect()->to('/login' . ($redirectUrl !== '' ? '?redirect_url=' . urlencode($redirectUrl) : ''));
        }

        return redirect()->to($redirectUrl !== '' ? $redirectUrl : '/');
    }

    public function getlogout()
    {
        (new CustomerLoginService())->logout();
        return redirect()->to('/');
    }

    // 다른 도메인으로 열린 리다이렉트(Open Redirect)를 막기 위해 "/"로 시작하는 내부 경로만 허용.
    // "//evil.com"(프로토콜 상대경로), "https://evil.com"(절대 URL)은 전부 버리고 기본 경로로 처리한다.
    private function sanitizeRedirect(string $url): string
    {
        $url = trim($url);
        if ($url === '' || $url[0] !== '/' || str_starts_with($url, '//') || preg_match('#^https?://#i', $url)) {
            return '';
        }
        return $url;
    }
}
