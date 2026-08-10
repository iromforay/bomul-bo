<?php
$msg = session()->getFlashdata('msg');
$err = session()->getFlashdata('err');

$statusLabels = [
    'REQUEST'    => ['요청', 'wait'],
    'ACTIVE'     => ['정상', 'done'],
    'SUSPENDED'  => ['유예', 'wait'],
    'TERMINATED' => ['종료', 'stop'],
];
?>
<div class="crumb">HOME &nbsp;›&nbsp; 고객관리 &nbsp;›&nbsp; <b>서비스 라이브 현황</b></div>

<div class="page-head">
    <h1>서비스 라이브 현황</h1>
</div>

<?= view('common/admin_alert', ['msg' => $msg, 'err' => $err]) ?>

<div class="panel">
    <div class="panel-head">
        <h2>운영중인 서비스 목록</h2>
    </div>
    <table>
        <thead>
        <tr>
            <th>번호</th><th>상호명</th><th>담당자</th><th>도메인(fid)</th>
            <th>배정서버</th><th>상태</th><th>시작일</th><th>만료일</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="8" style="text-align:center; color:#6B7A88;">라이브중인 서비스가 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($list as $row): ?>
                <?php $statusInfo = $statusLabels[$row['status']] ?? [$row['status'], 'wait']; ?>
                <tr class="row-link" onclick="location.href='<?= Admin_SITE ?>/ServiceLive/view/<?= (int) $row['apply_no'] ?>'">
                    <td><?= (int) $row['live_no'] ?></td>
                    <td><?= htmlspecialchars($row['company_name'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['manager_name'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['fid'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['server_code'] ?? '', ENT_QUOTES) ?></td>
                    <td><span class="badge <?= $statusInfo[1] ?>"><?= htmlspecialchars($statusInfo[0], ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars((string) $row['start_date'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars((string) $row['expire_date'], ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
