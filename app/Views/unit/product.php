<?php
// 상품(요금제) 카드 그리드 - index.php 요금안내 섹션에서 분리한 재사용 파츠.
// $applyBase: "신청하기" 버튼이 붙을 기본 경로 - 로그인 여부에 따라 /signup 또는 /service-apply를
//             호출부에서 결정해서 넘겨준다 (예: (session('customer_login')['is_login'] ?? false) ? '/service-apply' : '/signup').

?>
<div class="pricing-grid">
    <div class="pricing-card">
        <p class="pricing-card__name">1개월 이용권</p>
        <p class="pricing-card__price">무료</p>
        <p class="pricing-card__desc">먼저 무료로 체험해보세요</p>
        <ul class="pricing-card__features">
            <li>매물 홈페이지 1개</li>
            <li>매물 등록 무제한</li>
            <li>고객 상담 관리</li>
        </ul>
        <!-- TODO: 실제 상품(product_code=1month)은 220,000원 유료라 이 카드의 "무료" 문구와 안 맞음 -
             상품/문구 중 뭘 고칠지 정해지기 전까진 상품을 미리 골라 넘기지 않는다 -->
        <a href="/service-apply?product=1month" class="btn btn--outline">신청하기</a>
    </div>
    <div class="pricing-card">
        <p class="pricing-card__name">2개월 이용권</p>
        <p class="pricing-card__price-original">500,000원</p>
        <p class="pricing-card__price">무료<span class="pricing-card__event-badge">이벤트</span></p>
        <p class="pricing-card__desc">지금 신청하면 이벤트로 무료 제공</p>
        <ul class="pricing-card__features">
            <li>1개월 이용권 포함 기능</li>
            <li>통계 리포트</li>
            <li>우선 고객지원</li>
        </ul>
        <a href="/service-apply?product=2month" class="btn btn--outline">신청하기</a>
    </div>
    <div class="pricing-card pricing-card--featured">
        <span class="pricing-card__badge">추천</span>
        <p class="pricing-card__name">정기구독</p>
        <p class="pricing-card__price">220,000<span>원 / 월</span></p>
        <p class="pricing-card__desc">매월 자동결제, 언제든 해지 가능</p>
        <ul class="pricing-card__features">
            <li>1개월 무료이용권 포함 기능</li>
            <li>자동 갱신</li>
            <li>해지 시 남은 기간 이용 가능</li>
        </ul>
        <a href="/service-apply?product=billing" class="btn btn--primary">신청하기</a>
    </div>
</div>
