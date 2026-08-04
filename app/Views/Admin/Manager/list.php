<?php
$msg = session()->getFlashdata('msg');
$err = session()->getFlashdata('err');
?>
<div class="crumb">HOME &nbsp;›&nbsp; 관리자 &nbsp;›&nbsp; <b>관리자 목록</b></div>

<div class="page-head">
    <h1>관리자 관리</h1>
    <div class="actions">
        <a href="<?= Admin_SITE ?>/Manager/write"><button class="primary">+ 관리자 등록</button></a>
    </div>
</div>

<?php if ($msg): ?>
    <div class="panel" style="border-color:#2E7D32;"><div style="padding:10px 14px; color:#2E7D32; font-size:12.5px;"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div></div>
<?php endif; ?>
<?php if ($err): ?>
    <div class="panel" style="border-color:#B3261E;"><div style="padding:10px 14px; color:#B3261E; font-size:12.5px;"><?= htmlspecialchars($err, ENT_QUOTES) ?></div></div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head">
        <h2>관리자 목록</h2>
    </div>
    <table>
        <thead>
        <tr>
            <th>번호</th><th>아이디</th><th>이름</th><th>사용여부</th><th>등록일</th><th>관리</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="6" style="text-align:center; color:#6B7A88;">등록된 관리자가 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($list as $row): ?>
                <tr>
                    <td><?= (int) $row['manager_no'] ?></td>
                    <td><?= htmlspecialchars($row['manager_id'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['manager_name'], ENT_QUOTES) ?></td>
                    <td>
                        <?php if (($row['use_yn'] ?? 'Y') === 'Y'): ?>
                            <span class="badge done">사용</span>
                        <?php else: ?>
                            <span class="badge stop">미사용</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars((string) $row['reg_date'], ENT_QUOTES) ?></td>
                    <td>
                        <a href="<?= Admin_SITE ?>/Manager/modify/<?= (int) $row['manager_no'] ?>">수정</a>
                        &nbsp;|&nbsp;
                        <form method="post" action="<?= Admin_SITE ?>/Manager/delete/<?= (int) $row['manager_no'] ?>" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                            <button type="submit" style="border:none; background:none; color:#B3261E; cursor:pointer; padding:0; font-size:12px;">삭제</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
