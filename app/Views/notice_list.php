<?php $error = session()->getFlashdata('notice_error'); ?>
<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title">공지사항</h1>
        <p class="signup-desc">보이는 매물의 새로운 소식을 안내해드립니다.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="signup-card" style="border:1px solid #B3261E; background:#FDECEA;">
            <p><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
        </div>
    <?php endif; ?>

    <div class="signup-card">
        <?php if (empty($list)): ?>
            <p>등록된 공지사항이 없습니다.</p>
        <?php else: ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">제목</th>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88; width:120px;">등록일</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($list as $row): ?>
                    <tr>
                        <td style="padding:8px 12px;">
                            <a href="/notice/view/<?= (int) $row['board_no'] ?>"><?= htmlspecialchars($row['title'], ENT_QUOTES) ?></a>
                        </td>
                        <td style="padding:8px 12px;"><?= htmlspecialchars(substr((string) $row['created_at'], 0, 10), ENT_QUOTES) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
