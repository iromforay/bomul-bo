<?php
// 토스페이먼츠 연동 테스트 전용 화면(#11) - 금액/상품명을 입력해 자기 자신(PayTest::postIndex)에
// POST하면, 컨트롤러가 PaymentService::pay()를 호출해 결제위젯 화면으로 넘어간다.
// 실제 서비스 신청/연장 흐름과는 무관 - 공용 결제 모듈(PaymentService) 호출 예시 겸 동작 확인용.
?>
<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title">결제 연동 테스트</h1>
        <p class="signup-desc">테스트 키로 결제창을 호출합니다. 실제 신청/결제 이력에는 반영되지 않습니다.</p>
    </div>

    <div class="signup-card">
        <form method="post" action="/pay-test">
            <p class="form-row">
                <label for="orderName">상품명</label>
                <input type="text" id="orderName" name="orderName" value="결제 연동 테스트" required>
            </p>
            <p class="form-row">
                <label for="amount">금액(원)</label>
                <input type="number" id="amount" name="amount" value="1000" min="100" step="100" required>
            </p>
            <button type="submit" class="btn btn--primary">테스트 결제 진행</button>
        </form>
    </div>
</div>
