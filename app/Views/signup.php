<div class="signup-wrap">
<?php if (!empty($done)): ?>

    <div class="signup-card signup-success">
        <div class="signup-success__icon">✅</div>
        <h1 class="signup-title">가입 신청이 완료되었습니다</h1>
        <p class="signup-desc">확인 후 영업일 기준 1~2일 내로 안내드리겠습니다.</p>
        <a href="/" class="btn btn--primary">홈으로 돌아가기</a>
    </div>

<?php else: ?>

    <div class="signup-card">
        <h1 class="signup-title">회원가입 및 서비스 신청</h1>
        <p class="signup-desc">1/2개월 무료 이용권은 결제 없이 바로 신청할 수 있습니다</p>

        <?php if (!empty($error)): ?>
            <div class="form-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
        <?php endif; ?>

        <?php $old = $old ?? []; ?>

        <div class="wizard-progress">
            <div class="wizard-progress__bar"><span id="wizardProgressFill"></span></div>
            <p class="wizard-progress__label"><span id="wizardStepNum">1</span> / 3</p>
        </div>

        <form method="post" action="/signup" id="signupForm">

            <!-- 1단계: 계정 정보 -->
            <div class="wizard-step" data-step="1">
                <h2 class="wizard-step__title">계정 정보</h2>

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

                <div class="wizard-actions">
                    <span></span>
                    <button type="button" class="btn btn--primary wizard-next">다음</button>
                </div>
            </div>

            <!-- 2단계: 담당자·사무소 정보 -->
            <div class="wizard-step" data-step="2" hidden>
                <h2 class="wizard-step__title">담당자·사무소 정보</h2>

                <div class="form-group">
                    <label for="customer_name">담당자명</label>
                    <input type="text" id="customer_name" name="customer_name" value="<?= htmlspecialchars($old['customer_name'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form-group">
                    <label for="company_name">중개사무소명</label>
                    <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($old['company_name'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">휴대폰번호</label>
                        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">이메일</label>
                        <input type="text" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>" required>
                    </div>
                </div>

                <div class="wizard-actions">
                    <button type="button" class="btn btn--outline wizard-prev">이전</button>
                    <button type="button" class="btn btn--primary wizard-next">다음</button>
                </div>
            </div>

            <!-- 3단계: 사업자 정보 및 도메인 -->
            <div class="wizard-step" data-step="3" hidden>
                <h2 class="wizard-step__title">사업자 정보 및 도메인</h2>

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
                    <p class="field-hint">사무소 홈페이지 주소로 사용됩니다 (예: happy2025.bomul.ai.kr)</p>
                </div>

                <p class="field-hint" style="margin:4px 0 16px;">사업자등록증·중개사무소등록증 제출은 가입 후 별도 안내드립니다.</p>

                <div class="wizard-actions">
                    <button type="button" class="btn btn--outline wizard-prev">이전</button>
                    <button type="submit" class="btn btn--primary">가입 신청하기</button>
                </div>
            </div>

        </form>
    </div>

    <script>
    (function () {
        var form = document.getElementById('signupForm');
        if (!form) return;

        var steps = Array.prototype.slice.call(form.querySelectorAll('.wizard-step'));
        var total = steps.length;
        var current = 0;
        var progressFill = document.getElementById('wizardProgressFill');
        var stepNumEl = document.getElementById('wizardStepNum');

        function showStep(i) {
            steps.forEach(function (step, idx) { step.hidden = idx !== i; });
            current = i;
            if (progressFill) progressFill.style.width = ((i + 1) / total * 100) + '%';
            if (stepNumEl) stepNumEl.textContent = i + 1;
        }

        function validateStep(i) {
            var step = steps[i];
            var inputs = step.querySelectorAll('input[required]');
            for (var j = 0; j < inputs.length; j++) {
                if (!inputs[j].checkValidity()) {
                    inputs[j].reportValidity();
                    return false;
                }
            }
            if (i === 0) {
                var pw = document.getElementById('customer_pw');
                var pwConfirm = document.getElementById('customer_pw_confirm');
                var hint = document.getElementById('pwMismatchHint');
                if (pw.value !== pwConfirm.value) {
                    hint.style.display = 'block';
                    pwConfirm.focus();
                    return false;
                }
                hint.style.display = 'none';
            }
            return true;
        }

        form.querySelectorAll('.wizard-next').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!validateStep(current)) return;
                if (current < total - 1) showStep(current + 1);
            });
        });
        form.querySelectorAll('.wizard-prev').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (current > 0) showStep(current - 1);
            });
        });

        form.addEventListener('submit', function (e) {
            if (!validateStep(current)) e.preventDefault();
        });

        showStep(0);
    })();
    </script>

<?php endif; ?>
</div>
