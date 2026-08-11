<?php
// 상품(요금제) 카드 그리드 - index.php 요금안내 섹션과 service_apply.php 상품선택 스텝에서 공용으로 쓰는 파츠.
// $applyBase: "신청하기" 버튼이 붙을 기본 경로 - 로그인 여부에 따라 /signup 또는 /service-apply를
//             호출부에서 결정해서 넘겨준다 (예: (session('customer_login')['is_login'] ?? false) ? '/service-apply' : '/signup').
// $products: ProductService::get_usable_list() 결과(status=USE, sort순) - 어드민 "상품관리"(Admin/Product)에서
//            등록한 내용이 그대로 여기 반영된다. billing_type/is_event/price 등 실제 값 기준으로 카드를 그린다.
$products = $products ?? [];
?>
<div class="pricing-grid">
    <?php if (empty($products)): ?>
        <p class="pricing-empty">현재 신청 가능한 상품이 없습니다.</p>
    <?php endif; ?>
    <?php foreach ($products as $product): ?>
        <?php
        $isEvent = ((string) ($product['is_event'] ?? '0')) === '1';
        $price = (int) ($product['price'] ?? 0);
        $priceLabel = $price > 0
            ? number_format($price) . '원' . ($product['billing_type'] === 'MONTHLY' ? ' / 월' : '')
            : '무료';
        ?>
        <div class="pricing-card<?= $isEvent ? ' pricing-card--featured' : '' ?>">
            <?php if ($isEvent): ?>
                <span class="pricing-card__badge">이벤트</span>
            <?php endif; ?>
            <p class="pricing-card__name"><?= htmlspecialchars($product['product_name'], ENT_QUOTES) ?></p>
            <p class="pricing-card__price"><?= $priceLabel ?></p>
            <?php if (!empty($product['description'])): ?>
                <p class="pricing-card__desc"><?= htmlspecialchars($product['description'], ENT_QUOTES) ?></p>
            <?php endif; ?>
            <a href="<?= htmlspecialchars($applyBase . '?product=' . urlencode($product['product_code']), ENT_QUOTES) ?>" class="btn <?= $isEvent ? 'btn--primary' : 'btn--outline' ?>">신청하기</a>
        </div>
    <?php endforeach; ?>
</div>
