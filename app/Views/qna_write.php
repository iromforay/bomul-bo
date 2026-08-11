<?php $error = session()->getFlashdata('qna_error'); ?>
<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title">QNA 문의 등록</h1>
        <p class="signup-desc">문의하실 내용을 남겨주시면 확인 후 답변 드립니다.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="signup-card" style="border:1px solid #B3261E; background:#FDECEA;">
            <p><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
        </div>
    <?php endif; ?>

    <div class="signup-card">
        <form method="post" action="/qna/write">
            <table style="width:100%; border-collapse:collapse;">
                <tbody>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88; vertical-align:top;">제목</th>
                    <td style="padding:8px 12px;"><input type="text" name="title" style="width:100%;" required></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88; vertical-align:top;">내용</th>
                    <td style="padding:8px 12px;"><textarea name="content" rows="8" style="width:100%;" required></textarea></td>
                </tr>
                </tbody>
            </table>

            <div style="margin-top:12px; display:flex; gap:8px;">
                <button type="submit" class="btn btn--primary">등록</button>
                <a href="/qna" class="btn btn--outline">취소</a>
            </div>
        </form>
    </div>
</div>
