<?php

namespace App\Controllers;

use App\Service\CustomerService;
use App\Service\PaymentService;
use App\Service\ProductService;
use App\Service\ServiceApplyService;

// 로그인 회원 전용 서비스 신청 - 담당자·사무소 정보/사업자정보를 여기서 받는다.
// customer 프로필(customer_name/email/biz_reg_no/broker_reg_no)이 이미 채워져 있으면
// (직전 신청에서 완료해둔 경우) 그 입력 단계를 건너뛰고 바로 상품 확인만 받는다.
// 희망 도메인(sub_domain)은 프로필과 별개 - 신청서 1건마다 항상 새로 입력받는다
// (한 고객이 서비스를 여러 개 신청할 수 있고, 그때마다 도메인이 달라야 하므로).
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

            return redirect()->to(SITE.'/login?redirect_url=' . urlencode($this->currentPathWithQuery()));
        }

        $customerService = new CustomerService();
        $productService = new ProductService();

        // Signup에서 신청 실패로 넘어온 경우 - 골랐던 상품/입력값을 그대로 이어받는다
        $old = session()->getFlashdata('apply_old') ?? [];
        $productParam = $old['product_no'] ?? (string) $this->request->getGet('product');

        $data = [];
        $data['customer'] = $customer;
        $data['profileComplete'] = $customerService->isProfileComplete($customer);
        $data['lockedProduct'] = $productParam !== '' ? $productService->resolve($productParam) : null;
        $data['products'] = $productService->get_usable_base_list();
        $data['old'] = $old;
        $data['error'] = session()->getFlashdata('apply_error');
        echo $this->view("default", "service_apply", $data);
    }

    public function postindex()
    {
        $customer = $this->requireLogin();
        if ($customer === null) {
            return redirect()->to('/login?redirect_url=' . urlencode($this->currentPathWithQuery()));
        }

        $customerService = new CustomerService();
        $productService = new ProductService();
        $profileComplete = $customerService->isProfileComplete($customer);

        // 이번에 처음 입력하는 경우에만 "사업자정보"를 저장
        if (!$profileComplete) {
            $profileResult = $customerService->completeProfile((int) $customer['customer_no'], [
                'customer_name' => $this->request->getPost('customer_name'),
                'email'         => $this->request->getPost('email'),
                'biz_reg_no'    => $this->request->getPost('biz_reg_no'),
                'broker_reg_no' => $this->request->getPost('broker_reg_no'),
            ]);

            if ($profileResult['SUCCESS'] !== 'TRUE') {
                return $this->showError($customer, false, $profileResult['MSG']);
            }

            $customer = $customerService->get_row($customer['customer_no']);
            $profileComplete = true;
        }

        // 희망 도메인은 신청서마다 새로 입력받는다 - 유일성 체크는 ServiceApplyService::validateApplyData() 안에서.
        $applyData = [
            'customer_no'   => $customer['customer_no'],
            'product_no'    => $this->request->getPost('product_no'),
            'company_name'   => $customer['company_name'],
            'ceo_name'       => $this->request->getPost('ceo_name'),
            'license_no'     => $customer['broker_reg_no'],
            'biz_no'         => $customer['biz_reg_no'],
            'manager_name'   => $customer['customer_name'],
            'manager_phone'  => $customer['phone'],
            'manager_email'  => $customer['email'],
            'sub_domain'     => $this->request->getPost('sub_domain'),
            'tel'            => $this->request->getPost('tel'),
            'homepage_name'  => $this->request->getPost('homepage_name'),
            'memo'           => $this->request->getPost('memo'),
        ];

        $applyService = new ServiceApplyService();
        $validation = $applyService->validateApplyData($applyData);
        if ($validation['error'] !== null) {
            return $this->showError($customer, $profileComplete, $validation['error']);
        }

        $price = (int) ($validation['product']['price'] ?? 0);

        // 0원(무료·이벤트)은 결제 없이 바로 신청서 생성 - 유료는 결제 먼저(#11), 결제 성공 후
        // getComplete()에서 apply()를 호출해 신청서를 만든다(신청서를 결제 전에 만들지 않음).
        if ($price <= 0) {
            $applyResult = $applyService->apply($applyData);
            if ($applyResult['SUCCESS'] !== 'TRUE') {
                return $this->showError($customer, $profileComplete, $applyResult['MSG']);
            }

            $data = [];
            $data['done'] = true;
            echo $this->view("default", "service_apply", $data);
            return;
        }

        $payData = (new PaymentService())->pay([
            'amount'    => $price,
            'orderName' => $validation['product']['product_name'] ?? '서비스 신청',
            'returnUrl' => SITE . '/service-apply/complete',
            'context'   => ['apply_data' => $applyData],
        ]);
        echo $this->view("default", "payment/pay_widget", $payData);
    }

    // 결제(#11) 완료 후 복귀 지점 - 결제 성공 시에만 신청서(service_apply)를 생성한다.
    public function getComplete()
    {
        $customer = $this->requireLogin();
        if ($customer === null) {
            return redirect()->to(SITE . '/login?redirect_url=' . urlencode($this->currentPathWithQuery()));
        }

        $orderId = (string) $this->request->getGet('order_id');
        $paymentService = new PaymentService();
        $order = $paymentService->pullOrder($orderId);

        if ($order === null || !($order['confirm']['success'] ?? false)) {
            $message = $order['confirm']['result']['message'] ?? "결제가 완료되지 않았습니다. 다시 시도해주세요.";
            return $this->showError($customer, true, $message);
        }

        $applyData = $order['context']['apply_data'] ?? [];
        $applyService = new ServiceApplyService();
        $applyResult = $applyService->apply($applyData);

        if ($applyResult['SUCCESS'] !== 'TRUE') {
            // 결제는 이미 성공했는데 신청서 생성이 실패한 케이스 - 자동 환불은 하지 않고 로그만
            // 남긴다(결제수단 컨텍스트가 이 시점엔 다 사라진 뒤라 여기서 취소 처리하기 애매함).
            // 관리자가 pg_tid로 토스 콘솔에서 직접 취소 처리해야 한다.
            log_message('error', 'ServiceApply::getComplete apply() 실패(결제는 성공) customer_no={0} msg={1} order_id={2}', [
                $customer['customer_no'],
                $applyResult['MSG'],
                $orderId,
            ]);
            return $this->showError($customer, true, "결제는 완료되었으나 신청 처리 중 오류가 발생했습니다. 고객센터로 문의해주세요.");
        }

        $applyNo = $applyResult['RECORD']['apply_no'] ?? null;
        if ($applyNo !== null) {
            $paymentService->recordPayment((int) $applyNo, (int) $customer['customer_no'], $order['confirm']);
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

        $customer = (new CustomerService())->get_row($login['CUSTOMER_NO']);
        return $customer ?: null;
    }

    // 로그인 게이트에 걸렸을 때 로그인 후 돌아올 위치 - 쿼리스트링(?product=...)까지 그대로 보존
    private function currentPathWithQuery(): string
    {
        $uri = $this->request->getUri();
        $path = '/' . ltrim($uri->getPath(), '/');
        $query = $uri->getQuery();
        return $path . ($query !== '' ? '?' . $query : '');
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
        $data['lockedProduct'] = !empty($old['product_no']) ? $productService->get_row($old['product_no']) : null;
        $data['products'] = $productService->get_usable_base_list();
        echo $this->view("default", "service_apply", $data);
    }
}
