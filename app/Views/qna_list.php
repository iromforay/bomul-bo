<?php
$notice = session()->getFlashdata('qna_notice');
$error  = session()->getFlashdata('qna_error');
$isLoggedIn = !empty(session('customer_login')['is_login']);
?>
<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title">QNA</h1>
        <p class="signup-desc">궁금하신 점을 문의해주시면 확인 후 답변 드립니다.</p>
    </div>

    <?php if (!empty($notice)): ?>
        <div class="signup-card" style="border:1px solid #FFD166; background:#FFFBEB;">
            <p><?= htmlspecialchars($notice, ENT_QUOTES) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="signup-card" style="border:1px solid #B3261E; background:#FDECEA;">
            <p><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
        </div>
    <?php endif; ?>

    <div class="signup-card">
        <div style="display:flex; justify-content:flex-end; margin-bottom:12px;">
            <?php if ($isLoggedIn): ?>
                <a href="/qna/write" class="btn btn--primary">문의 등록</a>
            <?php else: ?>
                <a href="/login?redirect_url=<?= urlencode('/qna/write') ?>" class="btn btn--primary">로그인 후 문의 등록</a>
            <?php endif; ?>
        </div>

        <?php if (empty($list)): ?>
            <p>등록된 문의가 없습니다.</p>
        <?php else: ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">제목</th>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88; width:110px;">상태</th>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88; width:120px;">등록일</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($list as $row): ?>
                    <tr>
                        <td style="padding:8px 12px;">
                            <a href="/qna/view/<?= (int) $row['board_no'] ?>"><?= htmlspecialchars($row['title'], ENT_QUOTES) ?></a>
                        </td>
                        <td style="padding:8px 12px;">
                            <?= $row['status'] === 'DONE' ? '답변완료' : '답변대기' ?>
                        </td>
                        <td style="padding:8px 12px;"><?= htmlspecialchars(substr((string) $row['created_at'], 0, 10), ENT_QUOTES) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
