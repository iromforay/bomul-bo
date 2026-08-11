<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title"><?= htmlspecialchars($row['title'], ENT_QUOTES) ?></h1>
        <p class="signup-desc">
            <?= htmlspecialchars($row['writer_name'] ?? '작성자', ENT_QUOTES) ?>
            &nbsp;·&nbsp;<?= htmlspecialchars(substr((string) $row['created_at'], 0, 16), ENT_QUOTES) ?>
            &nbsp;·&nbsp;조회 <?= (int) $row['view_count'] ?>
            &nbsp;·&nbsp;<?= $row['status'] === 'DONE' ? '답변완료' : '답변대기' ?>
        </p>
    </div>

    <div class="signup-card">
        <h2 class="section__title" style="font-size:18px;">문의 내용</h2>
        <p style="white-space:pre-wrap;"><?= htmlspecialchars($row['content'], ENT_QUOTES) ?></p>
    </div>

    <?php if (!empty($row['reply_content'])): ?>
        <div class="signup-card" style="border:1px solid #2D7DD2; background:#F0F7FF;">
            <h2 class="section__title" style="font-size:18px;">답변</h2>
            <p style="white-space:pre-wrap;"><?= htmlspecialchars($row['reply_content'], ENT_QUOTES) ?></p>
            <?php if (!empty($row['replied_at'])): ?>
                <p style="color:#6B7A88; font-size:12px; margin-top:8px;">답변일시: <?= htmlspecialchars(substr((string) $row['replied_at'], 0, 16), ENT_QUOTES) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <a href="/qna" class="btn btn--outline">목록으로</a>
</div>
