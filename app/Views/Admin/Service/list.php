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
$payLabels = [
    'READY'     => ['결제대기', 'wait'],
    'PAID'      => ['결제완료', 'done'],
    'FAILED'    => ['결제실패', 'stop'],
    'CANCELLED' => ['결제취소', 'stop'],
    'REFUNDED'  => ['환불', 'stop'],
];
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
            <th>번호</th><th>신청일</th><th>아이디</th><th>상호명</th><th>대표자명</th>
            <th>담당자</th><th>연락처</th><th>상품</th><th>희망도메인</th><th>신청상태</th><th>결제여부</th><th>처리</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="12" style="text-align:center; color:#6B7A88;">서비스 신청 내역이 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($list as $row): ?>
                <?php
                $statusInfo = $statusLabels[$row['status']] ?? [$row['status'], 'wait'];
                $payInfo    = !empty($row['pay_status']) ? ($payLabels[$row['pay_status']] ?? [$row['pay_status'], 'wait']) : ['미결제', 'hold'];
                $pending    = in_array($row['status'], ['REQUEST', 'REVIEW'], true);
                ?>
                <tr class="row-link" onclick="location.href='<?= Admin_SITE ?>/Customer/view/<?= (int) $row['customer_no'] ?>'">
                    <td><?= (int) $row['apply_no'] ?></td>
                    <td><?= htmlspecialchars((string) $row['request_date'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['customer_login_id'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['ceo_name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['manager_name'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['manager_phone'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['product_name'], ENT_QUOTES) ?></td>
                    <td><?= !empty($row['sub_domain']) ? htmlspecialchars($row['sub_domain'], ENT_QUOTES) : '<span style="color:#6B7A88;">-</span>' ?></td>
                    <td><span class="badge <?= $statusInfo[1] ?>"><?= htmlspecialchars($statusInfo[0], ENT_QUOTES) ?></span></td>
                    <td><span class="badge <?= $payInfo[1] ?>"><?= htmlspecialchars($payInfo[0], ENT_QUOTES) ?></span></td>
                    <td onclick="event.stopPropagation();">
                        <?php if ($pending): ?>
                            <form method="post" action="<?= Admin_SITE ?>/Service/approve/<?= (int) $row['apply_no'] ?>" style="display:inline;" onsubmit="return confirm('승인하시겠습니까? 운영서버가 자동 배정됩니다.');">
                                <button type="submit" class="primary" style="padding:4px 10px; font-size:12px;">승인</button>
                            </form>
                            <form method="post" action="<?= Admin_SITE ?>/Service/reject/<?= (int) $row['apply_no'] ?>" style="display:inline;" onsubmit="return confirm('거절하시겠습니까?');">
                                <button type="submit" style="padding:4px 10px; font-size:12px;">거절</button>
                            </form>
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
