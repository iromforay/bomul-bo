<?php
$msg = $msg ?? null;
$err = $err ?? null;
?>
<?php if ($msg): ?>
    <div class="alert alert--success">
        <span><?= htmlspecialchars($msg, ENT_QUOTES) ?></span>
        <button type="button" class="alert__close" aria-label="닫기">&times;</button>
    </div>
<?php endif; ?>
<?php if ($err): ?>
    <div class="alert alert--error">
        <span><?= htmlspecialchars($err, ENT_QUOTES) ?></span>
        <button type="button" class="alert__close" aria-label="닫기">&times;</button>
    </div>
<?php endif; ?>
