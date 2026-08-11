<?php
// 마이페이지 "서비스 연장 신청" - 상품(기간) 선택 화면. unit/product.php의 카드 스타일을 재사용하되,
// 링크가 아니라 각 카드마다 폼으로 product_no를 POST한다(도메인/상품코드로 찾아가는 공개 신청 흐름과
// 달리, 로그인한 고객의 "지금 서비스"를 연장하는 거라 live_no는 서버(세션)에서만 결정한다).
?>
<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title">서비스 연장 신청</h1>
        <p class="signup-desc">
            <?= htmlspecialchars($service['company_name'] ?? '', ENT_QUOTES) ?>
            (<?= htmlspecialchars($service['sub_domain'] ?? $service['fid'], ENT_QUOTES) ?>) 서비스를 연장합니다.
            현재 만료일: <b><?= htmlspecialchars((string) $service['expire_date'], ENT_QUOTES) ?></b>
        </p>
    </div>

    <div class="signup-card">
        <h2 class="section__title" style="font-size:18px;">연장할 기간(상품)을 선택해주세요</h2>

        <div class="pricing-grid">
            <?php if (empty($products)): ?>
                <p class="pricing-empty">현재 신청 가능한 상품이 없습니다.</p>
            <?php endif; ?>
            <?php foreach ($products as $product): ?>
                <?php
                $price = (int) ($product['price'] ?? 0);
                $listPrice = (int) ($product['list_price'] ?? 0);
                $hasDiscount = $listPrice > $price;
                $discountRate = $hasDiscount ? (int) round((1 - $price / $listPrice) * 100) : 0;
                $priceLabel = $price > 0
                    ? number_format($price) . '원' . ($product['billing_type'] === 'MONTHLY' ? ' / 월' : '')
                    : '무료';
                ?>
                <div class="pricing-card<?= $hasDiscount ? ' pricing-card--featured' : '' ?>">
                    <p class="pricing-card__name">
                        <?= htmlspecialchars($product['product_name'], ENT_QUOTES) ?>
                        <span class="pricing-card__period"><?= htmlspecialchars(product_period_label($product['billing_type'], $product['period_day']), ENT_QUOTES) ?></span>
                    </p>
                    <?php if ($hasDiscount): ?>
                        <p class="pricing-card__price-original">
                            <?= number_format($listPrice) ?>원
                            <span style="color:#E11D48; font-weight:700;"><?= $discountRate ?>% 할인</span>
                        </p>
                    <?php endif; ?>
                    <p class="pricing-card__price"><?= $priceLabel ?></p>
                    <?php if (!empty($product['description'])): ?>
                        <p class="pricing-card__desc"><?= htmlspecialchars($product['description'], ENT_QUOTES) ?></p>
                    <?php endif; ?>
                    <form method="post" action="/mypage/extend">
                        <input type="hidden" name="product_no" value="<?= (int) $product['product_no'] ?>">
                        <button type="submit" class="btn <?= $price > 0 ? 'btn--outline' : 'btn--primary' ?>">
                            <?= $price > 0 ? '이 상품으로 결제 진행' : '이 상품으로 무료 연장 신청' ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <a href="/mypage" class="btn btn--outline">마이페이지로 돌아가기</a>
</div>
