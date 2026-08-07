<?php
$statusLabels = [
    'REQUEST'  => ['신청완료', 'wait'],
    'REVIEW'   => ['검토중', 'wait'],
    'APPROVED' => ['승인완료', 'done'],
    'REJECTED' => ['승인거절', 'stop'],
    'CANCEL'   => ['신청취소', 'stop'],
];
?>
<div class="crumb">HOME &nbsp;›&nbsp; 고객관리 &nbsp;›&nbsp; <b>서비스 신청 현황</b></div>

<div class="page-head">
    <h1>서비스 신청 현황</h1>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>전체 신청내역</h2>
    </div>
    <table>
        <thead>
        <tr>
            <th>번호</th><th>신청일</th><th>아이디</th><th>상호명</th><th>대표자명</th>
            <th>담당자</th><th>연락처</th><th>상품</th><th>희망도메인</th><th>상태</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="10" style="text-align:center; color:#6B7A88;">서비스 신청 내역이 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($list as $row): ?>
                <?php $statusInfo = $statusLabels[$row['status']] ?? [$row['status'], 'wait']; ?>
                <tr class="row-link" onclick="location.href='<?= Admin_SITE ?>/Customer/view/<?= (int) $row['customer_idx'] ?>'">
                    <td><?= (int) $row['apply_idx'] ?></td>
                    <td><?= htmlspecialchars((string) $row['request_date'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['customer_login_id'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['ceo_name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['manager_name'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['manager_phone'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['product_name'], ENT_QUOTES) ?></td>
                    <td><?= !empty($row['sub_domain']) ? htmlspecialchars($row['sub_domain'], ENT_QUOTES) : '<span style="color:#6B7A88;">-</span>' ?></td>
                    <td><span class="badge <?= $statusInfo[1] ?>"><?= htmlspecialchars($statusInfo[0], ENT_QUOTES) ?></span></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
