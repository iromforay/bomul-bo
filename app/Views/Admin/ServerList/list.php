<?php
$msg = session()->getFlashdata('msg');
$err = session()->getFlashdata('err');
?>
<div class="crumb">HOME &nbsp;›&nbsp; 서버관리 &nbsp;›&nbsp; <b>서버 목록</b></div>

<div class="page-head">
    <h1>서버 관리</h1>
    <div class="actions">
        <a href="<?= Admin_SITE ?>/ServerList/write"><button class="primary">+ 서버 등록</button></a>
    </div>
</div>

<?= view('common/admin_alert', ['msg' => $msg, 'err' => $err]) ?>

<div class="panel">
    <div class="panel-head">
        <h2>전체 서버</h2>
    </div>
    <table>
        <thead>
        <tr>
            <th>번호</th><th>서버코드</th><th>서버명</th><th>receiver URL</th>
            <th>배정 테넌트수</th><th>설치가능</th><th>상태</th><th>관리</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="8" style="text-align:center; color:#6B7A88;">등록된 서버가 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($list as $row): ?>
                <tr>
                    <td><?= (int) $row['server_no'] ?></td>
                    <td><?= htmlspecialchars($row['server_code'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['server_name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['receiver_url'], ENT_QUOTES) ?></td>
                    <td><?= (int) $row['tenant_count'] ?></td>
                    <td>
                        <?php if (($row['installable_yn'] ?? 'Y') === 'Y'): ?>
                            <span class="badge done">가능</span>
                        <?php else: ?>
                            <span class="badge stop">불가</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'USE'): ?>
                            <span class="badge done">정상</span>
                        <?php else: ?>
                            <span class="badge stop">중지</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= Admin_SITE ?>/ServerList/modify/<?= (int) $row['server_no'] ?>">수정</a>
                        &nbsp;|&nbsp;
                        <form method="post" action="<?= Admin_SITE ?>/ServerList/delete/<?= (int) $row['server_no'] ?>" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까? 배정된 서비스가 있으면 삭제되지 않습니다.');">
                            <button type="submit" style="border:none; background:none; color:#B3261E; cursor:pointer; padding:0; font-size:12px;">삭제</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
