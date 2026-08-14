<?php
// 결제위젯 콘텐츠 조각(PaymentService::pay()가 반환한 데이터로 호출자가 $this->view()에 넘겨 렌더링).
// 사이트 기본 레이아웃(header_menu/header/footer)에 얹히는 content 부분만 담당한다.
?>
<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title">결제 진행</h1>
        <p class="signup-desc">
            <?= htmlspecialchars($orderName, ENT_QUOTES) ?>
            &nbsp;-&nbsp; <b><?= number_format((int) $amount) ?>원</b>
        </p>
    </div>

    <div class="signup-card">
        <div id="payment-method"></div>
        <div id="agreement"></div>
        <button type="button" id="pay-submit-btn" class="btn btn--primary btn--block" disabled>결제하기</button>
    </div>
</div>

<script src="https://js.tosspayments.com/v2/standard"></script>
<script>
(function () {
    var tossPayments = TossPayments(<?= json_encode($clientKey) ?>);
    var widgets = tossPayments.widgets({ customerKey: TossPayments.ANONYMOUS });
    var submitBtn = document.getElementById('pay-submit-btn');

    widgets.setAmount({ currency: 'KRW', value: <?= (int) $amount ?> })
        .then(function () {
            return Promise.all([
                widgets.renderPaymentMethods({ selector: '#payment-method', variantKey: 'DEFAULT' }),
                widgets.renderAgreement({ selector: '#agreement', variantKey: 'AGREEMENT' }),
            ]);
        })
        .then(function () {
            submitBtn.disabled = false;
        });

    submitBtn.addEventListener('click', function () {
        submitBtn.disabled = true;
        widgets.requestPayment({
            orderId: <?= json_encode($orderId) ?>,
            orderName: <?= json_encode($orderName) ?>,
            successUrl: <?= json_encode($successUrl) ?>,
            failUrl: <?= json_encode($failUrl) ?>,
        }).catch(function (err) {
            submitBtn.disabled = false;
            alert('결제 요청 실패: ' + (err && err.message ? err.message : err));
        });
    });
})();
</script>
