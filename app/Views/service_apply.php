<div class="signup-wrap">
<?php if (!empty($done)): ?>

    <div class="signup-card signup-success">
        <div class="signup-success__icon">✅</div>
        <h1 class="signup-title">서비스 신청이 완료되었습니다</h1>
        <p class="signup-desc">확인 후 영업일 기준 1~2일 내로 안내드리겠습니다.</p>
        <a href="/" class="btn btn--primary">홈으로 돌아가기</a>
    </div>

<?php else: ?>

    <?php $lockedProduct = $lockedProduct ?? null; ?>

    <?php if (!$lockedProduct): ?>

        <div class="signup-card signup-success">
            <h1 class="signup-title">신청할 상품을 먼저 선택해주세요</h1>
            <p class="signup-desc">요금 안내에서 원하는 상품의 "신청하기"를 눌러주세요</p>
            <a href="/#pricing" class="btn btn--primary">요금 안내 보기</a>
        </div>

    <?php else: ?>

    <div class="signup-card">
        <h1 class="signup-title">서비스 신청</h1>
        <p class="signup-desc"><?= htmlspecialchars($customer['company_name'] ?? '', ENT_QUOTES) ?> 계정으로 상품을 신청합니다</p>

        <?php if (!empty($error)): ?>
            <div class="form-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <?php $old = $old ?? []; ?>
        <?php $profileComplete = $profileComplete ?? false; ?>

        <form method="post" action="/service-apply">
            <input type="hidden" name="product_idx" value="<?= (int) $lockedProduct['product_idx'] ?>">

            <?php if (!$profileComplete): ?>
                <!-- 담당자·사무소 정보 및 사업자 정보 및 도메인 - 이미 완료된 회원은 이 블록 자체가 안 나온다 -->
                <h2 class="wizard-step__title" style="font-size:1rem; margin-bottom:14px;">담당자·사업자 정보 및 도메인</h2>

                <div class="form-group">
                    <label for="customer_name">담당자명</label>
                    <input type="text" id="customer_name" name="customer_name" value="<?= htmlspecialchars($old['customer_name'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">이메일</label>
                    <input type="text" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="biz_reg_no">사업자등록번호</label>
                        <input type="text" id="biz_reg_no" name="biz_reg_no" value="<?= htmlspecialchars($old['biz_reg_no'] ?? '', ENT_QUOTES) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="broker_reg_no">중개사무소등록번호</label>
                        <input type="text" id="broker_reg_no" name="broker_reg_no" value="<?= htmlspecialchars($old['broker_reg_no'] ?? '', ENT_QUOTES) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="fid">희망 도메인 주소</label>
                    <div class="form-domain-preview">
                        <input type="text" id="fid" name="fid" value="<?= htmlspecialchars($old['fid'] ?? '', ENT_QUOTES) ?>" placeholder="영문 소문자/숫자" required>
                        <span class="form-domain-suffix">.bomul.ai.kr</span>
                    </div>
                    <p class="field-hint">사무소 홈페이지 주소로 사용됩니다 (예: happy2025.bomul.ai.kr) - 다른 사무소가 이미 쓰는 주소는 선택할 수 없습니다</p>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>담당자 / 사무소</label>
                    <p class="field-hint"><?= htmlspecialchars($customer['customer_name'] ?? '', ENT_QUOTES) ?> · <?= htmlspecialchars($customer['email'] ?? '', ENT_QUOTES) ?> · <?= htmlspecialchars($customer['fid'] ?? '', ENT_QUOTES) ?>.bomul.ai.kr</p>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="ceo_name">대표자명</label>
                <input type="text" id="ceo_name" name="ceo_name" value="<?= htmlspecialchars($old['ceo_name'] ?? '', ENT_QUOTES) ?>" required>
            </div>

            <div class="product-locked">
                <div class="product-locked__info">
                    <span class="product-locked__name"><?= htmlspecialchars($lockedProduct['product_name'], ENT_QUOTES) ?></span>
                    <span class="product-locked__price"><?= $lockedProduct['price'] > 0 ? number_format($lockedProduct['price']) . '원' : '무료' ?></span>
                </div>
            </div>

            <div class="form-group">
                <label for="memo">요청사항 (선택)</label>
                <input type="text" id="memo" name="memo" value="<?= htmlspecialchars($old['memo'] ?? '', ENT_QUOTES) ?>">
            </div>

            <button type="submit" class="btn btn--primary btn--block">서비스 신청하기</button>
        </form>
    </div>

    <?php endif; ?>

<?php endif; ?>
</div>
