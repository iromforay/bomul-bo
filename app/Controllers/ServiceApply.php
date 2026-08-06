<?php

namespace App\Controllers;

use App\Service\CustomerService;
use App\Service\ProductService;
use App\Service\ServiceApplyService;

// 로그인 회원 전용 서비스 신청 - 담당자·사무소 정보/사업자정보/도메인을 여기서 받는다.
// customer 프로필(customer_name/email/biz_reg_no/broker_reg_no/fid)이 이미 채워져 있으면
// (직전 신청에서 완료해둔 경우) 그 입력 단계를 건너뛰고 바로 상품 확인만 받는다.
class ServiceApply extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    public function getindex()
    {
        $customer = $this->requireLogin();
        if ($customer === null) {
            return redirect()->to('/login');
        }

        $customerService = new CustomerService();
        $productService = new ProductService();

        // Signup에서 신청 실패로 넘어온 경우 - 골랐던 상품/입력값을 그대로 이어받는다
        $old = session()->getFlashdata('apply_old') ?? [];
        $productParam = $old['product_idx'] ?? (string) $this->request->getGet('product');

        $data = [];
        $data['customer'] = $customer;
        $data['profileComplete'] = $customerService->isProfileComplete($customer);
        $data['lockedProduct'] = $productParam !== '' ? $productService->resolve($productParam) : null;
        $data['old'] = $old;
        $data['error'] = session()->getFlashdata('apply_error');
        echo $this->view("default", "service_apply", $data);
    }

    public function postindex()
    {
        $customer = $this->requireLogin();
        if ($customer === null) {
            return redirect()->to('/login');
        }

        $customerService = new CustomerService();
        $productService = new ProductService();
        $profileComplete = $customerService->isProfileComplete($customer);

        // 이번에 처음 입력하는 경우에만 "사업자정보 및 도메인"을 저장 - fid 중복체크는
        // CustomerService::completeProfile() 안에서 반드시 거친다.
        if (!$profileComplete) {
            $profileResult = $customerService->completeProfile((int) $customer['customer_idx'], [
                'customer_name' => $this->request->getPost('customer_name'),
                'email'         => $this->request->getPost('email'),
                'biz_reg_no'    => $this->request->getPost('biz_reg_no'),
                'broker_reg_no' => $this->request->getPost('broker_reg_no'),
                'fid'           => $this->request->getPost('fid'),
            ]);

            if ($profileResult['SUCCESS'] !== 'TRUE') {
                return $this->showError($customer, false, $profileResult['MSG']);
            }

            // fid 등 방금 저장한 값을 그대로 이어서 써야 하니 재조회
            $customer = $customerService->get_row($customer['customer_idx']);
            $profileComplete = true;
        }

        $applyService = new ServiceApplyService();
        $applyResult = $applyService->apply([
            'customer_idx'   => $customer['customer_idx'],
            'product_idx'    => $this->request->getPost('product_idx'),
            'company_name'   => $customer['company_name'],
            'ceo_name'       => $this->request->getPost('ceo_name'),
            'license_no'     => $customer['broker_reg_no'],
            'biz_no'         => $customer['biz_reg_no'],
            'manager_name'   => $customer['customer_name'],
            'manager_phone'  => $customer['phone'],
            'manager_email'  => $customer['email'],
            'sub_domain'     => $customer['fid'],
            'tel'            => $this->request->getPost('tel'),
            'homepage_name'  => $this->request->getPost('homepage_name'),
            'memo'           => $this->request->getPost('memo'),
        ]);

        if ($applyResult['SUCCESS'] !== 'TRUE') {
            return $this->showError($customer, $profileComplete, $applyResult['MSG']);
        }

        $data = [];
        $data['done'] = true;
        echo $this->view("default", "service_apply", $data);
    }

    private function requireLogin(): ?array
    {
        $login = session('customer_login');
        if (!($login['is_login'] ?? false)) {
            session()->setFlashdata('login_error', "서비스 신청은 로그인 후 이용 가능합니다.");
            return null;
        }

        $customer = (new CustomerService())->get_row($login['CUSTOMER_IDX']);
        return $customer ?: null;
    }

    private function showError(array $customer, bool $profileComplete, string $msg)
    {
        $productService = new ProductService();
        $old = $this->request->getPost();

        $data = [];
        $data['error'] = $msg;
        $data['old'] = $old;
        $data['customer'] = $customer;
        $data['profileComplete'] = $profileComplete;
        $data['lockedProduct'] = !empty($old['product_idx']) ? $productService->get_row($old['product_idx']) : null;
        echo $this->view("default", "service_apply", $data);
    }
}
