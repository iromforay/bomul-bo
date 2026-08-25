<?php

namespace App\Controllers;

use App\Service\PaymentService;

// 결제(#11) 공용 중앙 콜백 - 어떤 호출자가 PaymentService::pay()를 불렀든 PG는 항상 여기로만
// 응답한다. 승인(confirm) API 호출 후, 호출자가 pay() 시점에 지정했던 return_url로 결과를
// 붙여 되돌려보낸다. 이 컨트롤러 자체는 apply_no 같은 호출자별 후속 처리를 모른다 -
// 그건 각 호출자의 return_url 핸들러가 PaymentService::pullOrder()로 context를 꺼내 처리한다.
class Pay extends BaseController
{
    public function getSuccess()
    {
        $paymentKey = (string) $this->request->getGet('paymentKey');
        $orderId    = (string) $this->request->getGet('orderId');
        $amount     = (int) $this->request->getGet('amount');

        $service = new PaymentService();
        $order = $service->peekOrder($orderId);

        if ($order === null) {
            // 세션 만료 등으로 주문을 못 찾으면 되돌려보낼 곳이 없다 - 홈으로.
            return redirect()->to(SITE);
        }

        $confirmResult = $service->confirm($paymentKey, $orderId, $amount);

        // apply_no/customer_no가 이미 확정된 context면 여기서 바로 결제 기록까지 남긴다.
        // (결제 성공 후에야 apply_no를 만드는 흐름이면 호출자의 return_url 핸들러가 직접 기록해야 함)
        $applyNo = $order['context']['apply_no'] ?? null;
        $customerNo = $order['context']['customer_no'] ?? null;
        if ($applyNo !== null && $customerNo !== null) {
            $service->recordPayment((int) $applyNo, (int) $customerNo, $confirmResult);
        }

        // 다음 조회에서 호출자가 confirm 결과를 그대로 볼 수 있도록 세션에 남겨둔다.
        session()->set('pay_order_' . $orderId, array_merge($order, ['confirm' => $confirmResult]));

        $sep = str_contains($order['return_url'], '?') ? '&' : '?';
        return redirect()->to($order['return_url'] . $sep . 'order_id=' . urlencode($orderId) . '&pay_result=' . ($confirmResult['success'] ? 'success' : 'fail'));
    }

    public function getFail()
    {
        $orderId = (string) $this->request->getGet('orderId');
        $code    = (string) $this->request->getGet('code');
        $message = (string) $this->request->getGet('message');

        $service = new PaymentService();
        $order = $service->peekOrder($orderId);

        if ($order === null) {
            return redirect()->to(SITE);
        }

        session()->set('pay_order_' . $orderId, array_merge($order, [
            'confirm' => ['success' => false, 'result' => ['code' => $code, 'message' => $message]],
        ]));

        $sep = str_contains($order['return_url'], '?') ? '&' : '?';
        return redirect()->to($order['return_url'] . $sep . 'order_id=' . urlencode($orderId) . '&pay_result=fail');
    }
}
