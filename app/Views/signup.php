<div class="blue-bg-wrap">

    <div class="signup-wrap login-wrap">
    <?php $old = $old ?? []; ?>
    <?php $product = $product ?? ''; ?>

        <div class="signup-card login-card">
            <h1 class="signup-title">회원가입</h1>
            <p class="signup-desc">아이디/비밀번호와 사무소 정보만 있으면 바로 시작할 수 있습니다</p>

            <?php if (!empty($error)): ?>
                <div class="form-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <form method="post" action="/signup">
                <?php if ($product !== ''): ?>
                    <input type="hidden" name="product" value="<?= htmlspecialchars($product, ENT_QUOTES) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="customer_id">아이디</label>
                    <input type="text" id="customer_id" name="customer_id" value="<?= htmlspecialchars($old['customer_id'] ?? '', ENT_QUOTES) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="customer_pw">비밀번호</label>
                        <input type="password" id="customer_pw" name="customer_pw" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_pw_confirm">비밀번호 확인</label>
                        <input type="password" id="customer_pw_confirm" name="customer_pw_confirm" required>
                        <p class="field-hint" id="pwMismatchHint" style="display:none; color:#DC2626;">비밀번호가 일치하지 않습니다.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="company_name">중개사무소명</label>
                    <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($old['company_name'] ?? '', ENT_QUOTES) ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">휴대폰번호</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES) ?>" required>
                </div>

                <p class="field-hint" style="margin:4px 0 16px;">담당자·사업자 정보와 도메인은 다음 단계(서비스 신청)에서 입력합니다.</p>

                <button type="submit" class="btn btn--primary btn--block">가입하기</button>
            </form>

            <p class="login-foot">이미 계정이 있으신가요? <a href="/login">로그인</a></p>
        </div>

        <script>
        (function () {
            var form = document.querySelector('form[action="/signup"]');
            if (!form) return;
            form.addEventListener('submit', function (e) {
                var pw = document.getElementById('customer_pw');
                var pwConfirm = document.getElementById('customer_pw_confirm');
                var hint = document.getElementById('pwMismatchHint');
                if (pw.value !== pwConfirm.value) {
                    hint.style.display = 'block';
                    pwConfirm.focus();
                    e.preventDefault();
                } else {
                    hint.style.display = 'none';
                }
            });
        })();
        </script>
    </div>
</div>
