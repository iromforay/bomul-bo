<?php
// 결제 분기점 스텁(#11 실결제 연동 전) - 유료 상품을 고르면 여기로 온다. 아직 PG 연동이 없어서
// 실제 결제는 못 하지만, 나중에 여기에 결제창 호출을 붙이면 되는 자리로 미리 잡아둔 화면.
$price = (int) ($product['price'] ?? 0);
?>
<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title">결제 진행</h1>
        <p class="signup-desc">
            선택하신 상품: <b><?= htmlspecialchars($product['product_name'] ?? '', ENT_QUOTES) ?></b>
            (<?= htmlspecialchars(product_period_label($product['billing_type'] ?? null, $product['period_day'] ?? 0), ENT_QUOTES) ?>)
            &nbsp;-&nbsp; <b><?= number_format($price) ?>원</b>
        </p>
    </div>

    <div class="signup-card">
        <p>결제 모듈은 현재 준비중입니다. 결제 연동 완료 전까지는 관리자에게 문의해 안내받아주세요.</p>
        <a href="/mypage/extend" class="btn btn--outline">다른 상품 선택</a>
        <a href="/mypage" class="btn btn--primary">마이페이지로 돌아가기</a>
    </div>
</div>
