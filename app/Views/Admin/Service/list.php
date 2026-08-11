<?php
$msg = session()->getFlashdata('msg');
$err = session()->getFlashdata('err');

$statusLabels = [
    'REQUEST'  => ['신청완료', 'wait'],
    'REVIEW'   => ['검토중', 'wait'],
    'APPROVED' => ['승인완료', 'done'],
    'REJECTED' => ['승인거절', 'stop'],
    'CANCEL'   => ['신청취소', 'stop'],
];
// 신청상태 옆 "세팅현황" 배지 - 상세화면 스텝퍼(대기/세팅중/완료/실패)와 동일한 queue(action='new') 기준.
$queueLabels = [
    'PENDING' => ['대기', 'wait'],
    'SENT'    => ['세팅중', 'wait'],
    'DONE'    => ['완료', 'done'],
    'FAILED'  => ['실패', 'stop'],
];
$typeLabels = ['NEW' => ['신규', 'wait'], 'RENEW' => ['연장', 'done']];
?>
<div class="crumb">HOME &nbsp;›&nbsp; 고객관리 &nbsp;›&nbsp; <b>서비스 신청 현황</b></div>

<div class="page-head">
    <h1>서비스 신청 현황</h1>
</div>

<?= view('common/admin_alert', ['msg' => $msg, 'err' => $err]) ?>

<div class="panel">
    <div class="panel-head">
        <h2>전체 신청내역</h2>
    </div>
    <table>
        <thead>
        <tr>
            <th>번호</th><th>유형</th><th>신청일</th><th>상호명</th><th>대표자명</th>
            <th>담당자</th><th>연락처</th><th>상품</th><th>희망도메인</th><th>신청상태</th><th>세팅현황</th><th>처리</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="12" style="text-align:center; color:#6B7A88;">서비스 신청 내역이 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($list as $row): ?>
                <?php
                $statusInfo = $statusLabels[$row['status']] ?? [$row['status'], 'wait'];
                $pending    = in_array($row['status'], ['REQUEST', 'REVIEW'], true);
                $queueInfo  = !empty($row['queue_status']) ? ($queueLabels[$row['queue_status']] ?? [$row['queue_status'], 'wait']) : null;
                $applyType  = $row['apply_type'] ?? 'NEW';
                $typeInfo   = $typeLabels[$applyType] ?? [$applyType, 'wait'];
                $isNew      = $applyType === 'NEW';
                ?>
                <tr class="row-link" onclick="location.href='<?= Admin_SITE ?>/Service/view/<?= (int) $row['apply_no'] ?>'">
                    <td><?= (int) $row['apply_no'] ?></td>
                    <td><span class="badge <?= $typeInfo[1] ?>"><?= htmlspecialchars($typeInfo[0], ENT_QUOTES) ?></span></td>
                    <td><?= htmlspecialchars((string) $row['request_date'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['ceo_name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['manager_name'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['manager_phone'], ENT_QUOTES) ?></td>
                    <td><?= !empty($row['product_name']) ? htmlspecialchars($row['product_name'], ENT_QUOTES) : '<span style="color:#6B7A88;">-</span>' ?></td>
                    <td><?= !empty($row['sub_domain']) ? htmlspecialchars($row['sub_domain'], ENT_QUOTES) : '<span style="color:#6B7A88;">-</span>' ?></td>
                    <td><span class="badge <?= $statusInfo[1] ?>"><?= htmlspecialchars($statusInfo[0], ENT_QUOTES) ?></span></td>
                    <td>
                        <?php if ($queueInfo): ?>
                            <span class="badge <?= $queueInfo[1] ?>"><?= htmlspecialchars($queueInfo[0], ENT_QUOTES) ?></span>
                        <?php else: ?>
                            <span style="color:#6B7A88;">-</span>
                        <?php endif; ?>
                    </td>
                    <td onclick="event.stopPropagation();">
                        <?php if ($pending): ?>
                            <form method="post" action="<?= Admin_SITE ?>/Service/approve/<?= (int) $row['apply_no'] ?>" style="display:inline;" onsubmit="return confirm('승인하시겠습니까? 운영서버가 자동 배정됩니다.');">
                                <button type="submit" class="primary" style="padding:4px 10px; font-size:12px;">승인</button>
                            </form>
                            <form method="post" action="<?= Admin_SITE ?>/Service/reject/<?= (int) $row['apply_no'] ?>" style="display:inline;" onsubmit="return confirm('거절하시겠습니까?');">
                                <button type="submit" style="padding:4px 10px; font-size:12px;">거절</button>
                            </form>
                        <?php elseif ($isNew && $row['status'] === 'APPROVED' && ($row['install_status'] ?? null) !== 'DONE'): ?>
                            <!-- 승인은 됐는데 service_live/queue가 안 만들어진 건을 뒤늦게 큐에 적재할 때 사용 - 신규(NEW) 신청 전용 -->
                            <form method="post" action="<?= Admin_SITE ?>/Service/requeue/<?= (int) $row['apply_no'] ?>" style="display:inline;" onsubmit="return confirm('큐에 적재하시겠습니까?');">
                                <button type="submit" style="padding:4px 10px; font-size:12px;">큐 적재</button>
                            </form>
                        <?php elseif ($isNew && $row['status'] === 'APPROVED' && ($row['live_status'] ?? null) === 'SUSPENDED'): ?>
                            <form method="post" action="<?= Admin_SITE ?>/Service/resume/<?= (int) $row['apply_no'] ?>" style="display:inline;" onsubmit="return confirm('서비스를 재개하시겠습니까?');">
                                <button type="submit" class="primary" style="padding:4px 10px; font-size:12px;">재개</button>
                            </form>
                        <?php elseif ($isNew && $row['status'] === 'APPROVED'): ?>
                            <form method="post" action="<?= Admin_SITE ?>/Service/suspend/<?= (int) $row['apply_no'] ?>" style="display:inline;" onsubmit="return confirm('서비스를 정지하시겠습니까?');">
                                <button type="submit" style="padding:4px 10px; font-size:12px;">정지</button>
                            </form>
                        <?php elseif (!$isNew && $row['status'] === 'APPROVED'): ?>
                            <span style="color:#6B7A88;">서비스 라이브에서 관리</span>
                        <?php else: ?>
                            <span style="color:#6B7A88;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
