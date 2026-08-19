<?php
// 상품(요금제) 카드 그리드 - index.php 요금안내 섹션과 service_apply.php 상품선택 스텝에서 공용으로 쓰는 파츠.
// $applyBase: "신청하기" 버튼이 붙을 기본 경로 - 로그인 여부에 따라 /signup 또는 /service-apply를
//             호출부에서 결정해서 넘겨준다 (예: (session('customer_login')['is_login'] ?? false) ? '/service-apply' : '/signup').
// $products: ProductService::get_usable_base_list() 결과(status=USE, product_type=BASE, sort순) -
//            어드민 "상품관리"(Admin/Product)에서 등록한 내용이 그대로 여기 반영된다. 부가서비스(ADDON)는
//            아직 이 그리드에 안 섞임(2026-08-11 상품 재구성 - 워크맵 참고).
// 상품명(product_name)은 순수 명칭만(예: "중개사 사이트 개설") - 기간은 product_period_label()로
// billing_type/period_day에서 계산해 이름 옆 배지로 별도 표시한다.
$products = $products ?? [];
?>
<div class="pricing-grid">
    <?php if (empty($products)): ?>
        <p class="pricing-empty">현재 신청 가능한 상품이 없습니다.</p>
    <?php endif; ?>
    <?php foreach ($products as $index => $product): ?>
        <?php
        $isEvent = ((string) ($product['is_event'] ?? '0')) === '1';
        $price = (int) ($product['price'] ?? 0);
        $listPrice = (int) ($product['list_price'] ?? 0);
        $hasDiscount = $listPrice > $price;
        $discountRate = $hasDiscount ? (int) round((1 - $price / $listPrice) * 100) : 0;
        $priceLabel = $price > 0
            ? number_format($price) . '원' . ($product['billing_type'] === 'MONTHLY' ? ' / 월' : '')
            : '무료';
        ?>
        <div class="pricing-card<?= ($isEvent || $hasDiscount) ? ' pricing-card--featured' : '' ?>" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
            <?php if ($isEvent): ?>
                <span class="pricing-card__badge">이벤트</span>
            <?php endif; ?>
            <p class="pricing-card__name"><?= htmlspecialchars($product['product_name'], ENT_QUOTES) ?> <span class="pricing-card__period"><?= htmlspecialchars(product_period_label($product['billing_type'], $product['period_day']), ENT_QUOTES) ?></span></p>
            <?php if ($hasDiscount): ?>
                <p class="pricing-card__price-original">
                    <?= number_format($listPrice) ?>원
                </p>
                <span style="color:#E11D48; font-weight:700;font-size: 23px;"><?= $discountRate ?>% 할인</span>
            <?php endif; ?>
            <p class="pricing-card__price"><?= $priceLabel ?></p>
            <?php if (!empty($product['description'])): ?>
                <p class="pricing-card__desc"><?= htmlspecialchars($product['description'], ENT_QUOTES) ?></p>
            <?php endif; ?>
            <a href="<?= htmlspecialchars($applyBase . '?product=' . urlencode($product['product_code']), ENT_QUOTES) ?>" class="btn <?= $isEvent ? 'btn--primary' : 'btn--outline' ?>">신청하기</a>
        </div>
    <?php endforeach; ?>
</div>
