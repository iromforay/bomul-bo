<div class="blue-bg-wrap">
    <div class="signup-wrap login-wrap">
        <div class="signup-card login-card">
            <h1 class="signup-title">로그인</h1>
            <p class="signup-desc">보이는 매물 서비스를 이용하려면 로그인해주세요</p>

            <?php if (!empty($error)): ?>
                <div class="form-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <form method="post" action="/login">
                <?php if (!empty($redirectUrl)): ?>
                    <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($redirectUrl, ENT_QUOTES) ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="customer_id">아이디</label>
                    <input type="text" id="customer_id" name="customer_id" autocomplete="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="customer_pw">비밀번호</label>
                    <input type="password" id="customer_pw" name="customer_pw" autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn--primary btn--block">로그인</button>
            </form>

            <p class="login-foot">아직 계정이 없으신가요? <a href="/signup">회원가입</a></p>
        </div>
    </div>
</div>
