<?php

namespace App\Controllers;

use App\Service\PaymentService;

// 결제 연동(#11) 테스트 전용 페이지 - PaymentService 공용 모듈 호출 예시 겸 동작 확인용.
// payment 테이블(apply_no FK)에는 쓰지 않는다(context에 apply_no를 안 넣으므로 Pay::getSuccess()가
// 자동으로 기록하지 않음) - 순수하게 결제창~승인 API 왕복만 확인하는 용도.
class PayTest extends BaseController
{
    public function getIndex()
    {
        echo $this->view("default", "pay_test");
    }

    // 폼이 자기 자신에게 POST - 여기서 필요한 데이터를 저장(테스트라 저장할 것 없음)한 뒤
    // PaymentService::pay()가 반환한 데이터를 그대로 view에 넘겨 렌더링한다.
    // return_url은 이 컨트롤러의 getComplete().
    public function postIndex()
    {
        $orderName = (string) $this->request->getPost('orderName');
        $amount = (int) $this->request->getPost('amount');

        $data = (new PaymentService())->pay([
            'amount'    => $amount,
            'orderName' => $orderName,
            'returnUrl' => SITE . '/pay-test/complete',
            'context'   => [],
        ]);

        echo $this->view("default", "payment/pay_widget", $data);
    }

    // PaymentService::pullOrder()로 confirm 결과를 꺼내 보여준다.
    public function getComplete()
    {
        $orderId = (string) $this->request->getGet('order_id');
        $order = (new PaymentService())->pullOrder($orderId);

        $data = [];
        $data['success'] = $order['confirm']['success'] ?? false;
        $data['result']  = $order['confirm']['result'] ?? ['message' => '주문 정보를 찾을 수 없습니다.'];
        echo $this->view("default", "pay_test_result", $data);
    }
}
