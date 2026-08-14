<?php

namespace App\Service;

use App\Entity\Payment_Entity;

// 결제 연동(#11) 공용 모듈 - 어떤 컨트롤러든 pay()만 호출하면 결제위젯을 렌더링하고, PG 응답은
// 항상 중앙 콜백(Controllers/Pay::getSuccess/getFail)으로만 돌아온다. 호출자(주문 발생지)와
// 콜백을 연결하는 매개는 orderId를 키로 하는 세션 - 별도 "주문" 테이블 없이 같은 브라우저 세션
// 안에서 왕복이 끝난다는 전제로 최소 구현했다(장바구니처럼 여러 창을 오래 띄워두는 흐름이면
// 세션 대신 DB 테이블로 바꿔야 할 수 있음).
class PaymentService extends CI_Service
{
    private const SESSION_PREFIX = 'pay_order_';

    private $paymentModel;

    public function __construct()
    {
        parent::__construct();
        $this->paymentModel = model('App\Models\PaymentModel', true, $this->db);
    }

    // 호출자가 결제를 시작할 때 호출 - 렌더링은 하지 않고, payment/pay_widget 뷰에 그대로 넘기면
    // 되는 데이터만 가공해서 반환한다. 실제 echo/레이아웃 조립은 호출자(컨트롤러)의 $this->view() 몫 -
    // 그래야 호출자마다 원하는 레이아웃(풀 레이아웃/미니멀 등)을 자유롭게 고를 수 있다.
    // $params: amount(int), orderName(string), returnUrl(string, 호출자가 결과를 받을 자기 라우트), context(array, 임의 - 승인 후 호출자가 다시 꺼내 쓸 정보, 예: apply_no)
    public function pay(array $params): array
    {
        $orderId = 'ORD' . date('YmdHis') . strtoupper(bin2hex(random_bytes(4)));

        session()->set(self::SESSION_PREFIX . $orderId, [
            'amount'     => (int) $params['amount'],
            'return_url' => $params['returnUrl'],
            'context'    => $params['context'] ?? [],
        ]);

        $data = [];
        $data['clientKey']  = env('clientKey');
        $data['amount']     = (int) $params['amount'];
        $data['orderName']  = $params['orderName'];
        $data['orderId']    = $orderId;
        $data['successUrl'] = SITE . '/pay/success';
        $data['failUrl']    = SITE . '/pay/fail';

        return $data;
    }

    // 중앙 콜백에서 호출 - 토스 결제승인(confirm) API 실제 호출부.
    public function confirm(string $paymentKey, string $orderId, int $amount): array
    {
        $secretKey = env('secretKey');
        $client = \Config\Services::curlrequest();

        try {
            $response = $client->request('POST', 'https://api.tosspayments.com/v1/payments/confirm', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'paymentKey' => $paymentKey,
                    'orderId'    => $orderId,
                    'amount'     => $amount,
                ],
                'http_errors' => false,
            ]);

            return [
                'success' => $response->getStatusCode() === 200,
                'result'  => json_decode((string) $response->getBody(), true),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'result' => ['message' => $e->getMessage()]];
        }
    }

    // orderId로 세션에 저장해둔 주문 정보(return_url/amount/context) 조회 - 세션에서 제거하지 않는다.
    // 중앙 콜백이 return_url을 알아내기 위해 먼저 한 번 읽고, 호출자의 return_url 핸들러가
    // pullOrder()로 다시 읽어서 자기 context(apply_no 등)를 꺼내 쓰는 2단계 조회 구조.
    public function peekOrder(string $orderId): ?array
    {
        return session(self::SESSION_PREFIX . $orderId);
    }

    // 호출자의 return_url 핸들러에서 호출 - 조회 후 세션에서 제거(1회성).
    public function pullOrder(string $orderId): ?array
    {
        $order = $this->peekOrder($orderId);
        session()->remove(self::SESSION_PREFIX . $orderId);
        return $order;
    }

    // apply_no/customer_no가 확정된 실제 결제 건을 payment 테이블에 기록 - recordFreePayment(무료건)와 대칭.
    public function recordPayment(int $applyNo, int $customerNo, array $confirmResult): void
    {
        $r = $confirmResult['result'] ?? [];

        $entity = new Payment_Entity();
        $entity->setApplyNo($applyNo);
        $entity->setCustomerNo($customerNo);
        $entity->setPayMethod('CARD');
        $entity->setPgProvider('TOSS');
        $entity->setPgTid($r['paymentKey'] ?? null);
        $entity->setPayAmount((int) ($r['totalAmount'] ?? 0));
        $entity->setPayStatus($confirmResult['success'] ? 'PAID' : 'FAILED');
        $entity->setPaidAt($r['approvedAt'] ?? null);
        if (!$confirmResult['success']) {
            $entity->setFailReason($r['message'] ?? null);
        }
        $entity->setPgResponse(json_encode($r, JSON_UNESCAPED_UNICODE));

        $payment_no = $this->paymentModel->sql_save($entity);
        if (!$payment_no) {
            log_message('error', 'PaymentService::recordPayment 저장 실패 apply_no={0} db_error={1} query={2}', [
                $applyNo,
                json_encode($this->paymentModel->getDbError()),
                $this->paymentModel->getQuery(),
            ]);
        }
    }
}
