<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title"><?= htmlspecialchars($row['title'], ENT_QUOTES) ?></h1>
        <p class="signup-desc">
            <?= htmlspecialchars(substr((string) $row['created_at'], 0, 16), ENT_QUOTES) ?>
            &nbsp;·&nbsp;조회 <?= (int) $row['view_count'] ?>
        </p>
    </div>

    <div class="signup-card">
        <p style="white-space:pre-wrap;"><?= htmlspecialchars($row['content'], ENT_QUOTES) ?></p>
    </div>

    <a href="/notice" class="btn btn--outline">목록으로</a>
</div>
