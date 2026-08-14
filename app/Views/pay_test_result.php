<?php
// 결제 테스트 결과 화면 - 승인(confirm) API 응답 원문을 그대로 보여준다(디버깅 용도).
?>
<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title"><?= $success ? '결제 승인 성공' : '결제 실패/오류' ?></h1>
        <p class="signup-desc"><?= $success ? '토스 결제승인(confirm) API 호출까지 정상 완료되었습니다.' : '결제가 실패했거나 승인 과정에서 오류가 발생했습니다.' ?></p>
    </div>

    <div class="signup-card">
        <pre style="white-space:pre-wrap; word-break:break-all; background:#f5f5f5; padding:16px; border-radius:8px;"><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?></pre>
    </div>

    <a href="/pay-test" class="btn btn--outline">다시 테스트</a>
</div>
