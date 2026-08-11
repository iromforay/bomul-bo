<?php
$msg = session()->getFlashdata('msg');
$err = session()->getFlashdata('err');

$statusLabels = ['WAIT' => ['답변대기', 'wait'], 'DONE' => ['답변완료', 'done']];
?>
<div class="crumb">HOME &nbsp;›&nbsp; 게시판 &nbsp;›&nbsp; <b><?= htmlspecialchars($typeLabel, ENT_QUOTES) ?></b></div>

<div class="page-head">
    <h1><?= htmlspecialchars($typeLabel, ENT_QUOTES) ?></h1>
    <div class="actions">
        <a href="<?= Admin_SITE ?>/Board/<?= $type ?>/write"><button class="primary">+ 글쓰기</button></a>
    </div>
</div>

<?= view('common/admin_alert', ['msg' => $msg, 'err' => $err]) ?>

<div class="panel">
    <div class="panel-head">
        <h2><?= htmlspecialchars($typeLabel, ENT_QUOTES) ?> 목록</h2>
    </div>
    <table>
        <thead>
        <tr>
            <th>번호</th><th>제목</th><th>작성자</th><th>상태</th><th>노출</th><th>조회수</th><th>등록일</th><th>관리</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="8" style="text-align:center; color:#6B7A88;">등록된 글이 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($list as $row): ?>
                <?php $statusInfo = $statusLabels[$row['status']] ?? [$row['status'], 'wait']; ?>
                <tr>
                    <td><?= (int) $row['board_no'] ?></td>
                    <td><a href="<?= Admin_SITE ?>/Board/<?= $type ?>/modify/<?= (int) $row['board_no'] ?>"><?= htmlspecialchars($row['title'], ENT_QUOTES) ?></a></td>
                    <td><?= !empty($row['writer_name']) ? htmlspecialchars($row['writer_name'], ENT_QUOTES) : '<span style="color:#6B7A88;">관리자</span>' ?></td>
                    <td><span class="badge <?= $statusInfo[1] ?>"><?= htmlspecialchars($statusInfo[0], ENT_QUOTES) ?></span></td>
                    <td>
                        <?php if (($row['use_yn'] ?? 'Y') === 'Y'): ?>
                            <span class="badge done">노출</span>
                        <?php else: ?>
                            <span class="badge stop">숨김</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $row['view_count'] ?></td>
                    <td><?= htmlspecialchars((string) $row['created_at'], ENT_QUOTES) ?></td>
                    <td>
                        <a href="<?= Admin_SITE ?>/Board/<?= $type ?>/modify/<?= (int) $row['board_no'] ?>">수정</a>
                        &nbsp;|&nbsp;
                        <form method="post" action="<?= Admin_SITE ?>/Board/<?= $type ?>/delete/<?= (int) $row['board_no'] ?>" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                            <button type="submit" style="border:none; background:none; color:#B3261E; cursor:pointer; padding:0; font-size:12px;">삭제</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
