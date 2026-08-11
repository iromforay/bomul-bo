<?php

namespace App\Service;

// 축약형 마이페이지(워크맵 #16) - 내 서비스 현황 + 내 결제 리스트만 보여준다.
// 상품/서비스가 여러 개로 늘어나기 전까지는 "현재 서비스" 1건만 다룬다.
class MyPageService extends CI_Service
{
    private $serviceLiveModel;
    private $paymentModel;

    public function __construct()
    {
        parent::__construct();
        $this->serviceLiveModel = model('App\Models\ServiceLiveModel', true, $this->db);
        $this->paymentModel = model('App\Models\PaymentModel', true, $this->db);
    }

    public function get_current_service($customer_no)
    {
        return $this->serviceLiveModel->getCurrentByCustomerNo($customer_no);
    }

    public function get_payment_list($customer_no)
    {
        return $this->paymentModel->get_list_by_customer($customer_no);
    }
}
