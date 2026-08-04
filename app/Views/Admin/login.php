<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>관리자 로그인 :: 보이는 매물 Admin</title>
    <link rel="stylesheet" href="<?= RS_CSS ?>/common/admin_login.css?var=<?= FRESH_TIME ?>">
</head>
<body>
    <div class="login-wrap">
        <div class="login-brand">
            <div class="mark">보물</div>
            <h1>보이는 매물</h1>
            <p>ADMIN</p>
        </div>

        <div class="login-card">
            <h2>관리자 로그인</h2>

            <?php if (!empty($error)): ?>
                <div class="login-error"><?= htmlspecialchars($error, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= Admin_SITE ?>/Login">
                <div class="field">
                    <label for="mb_id">아이디</label>
                    <input type="text" id="mb_id" name="mb_id" autocomplete="username" required autofocus>
                </div>
                <div class="field">
                    <label for="mb_pw">비밀번호</label>
                    <input type="password" id="mb_pw" name="mb_pw" autocomplete="current-password" required>
                </div>

                <div class="opts">
                    <label><input type="checkbox" name="keep_login" value="Y"> 로그인 상태 유지</label>
                    <a href="#">비밀번호 찾기</a>
                </div>

                <button type="submit" class="btn-login">로그인</button>
            </form>
        </div>

        <p class="login-foot">Copyright &copy; <?= date('Y') ?> 보이는 매물 Admin. All rights reserved.</p>
    </div>
</body>
</html>
