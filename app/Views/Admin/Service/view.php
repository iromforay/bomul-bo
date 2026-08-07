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
$statusInfo = $statusLabels[$row['status']] ?? [$row['status'], 'wait'];
$payInfo    = !empty($row['pay_status']) ? ($payLabels[$row['pay_status']] ?? [$row['pay_status'], 'wait']) : ['미결제', 'hold'];
?>
<div class="crumb">HOME &nbsp;›&nbsp; 고객관리 &nbsp;›&nbsp; <b>서비스 신청 상세</b></div>

<div class="page-head">
    <h1>서비스 신청 상세</h1>
    <div class="actions">
        <a href="<?= Admin_SITE ?>/Service"><button type="button">목록으로</button></a>
    </div>
</div>

<?= view('common/admin_alert', ['msg' => $msg, 'err' => $err]) ?>

<div class="panel">
    <div class="panel-head"><h2>신청 정보</h2></div>
    <table class="form-table">
        <tbody>
        <tr><th>신청번호</th><td><?= (int) $row['apply_no'] ?></td></tr>
        <tr><th>신청일</th><td><?= htmlspecialchars((string) $row['request_date'], ENT_QUOTES) ?></td></tr>
        <tr><th>신청 아이디</th><td><?= !empty($row['customer_login_id']) ? htmlspecialchars($row['customer_login_id'], ENT_QUOTES) : '<span style="color:#6B7A88;">탈퇴/알수없음</span>' ?></td></tr>
        <tr><th>상품</th><td><?= !empty($row['product_name']) ? htmlspecialchars($row['product_name'], ENT_QUOTES) : '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        <tr><th>신청상태</th><td><span class="badge <?= $statusInfo[1] ?>"><?= htmlspecialchars($statusInfo[0], ENT_QUOTES) ?></span></td></tr>
        <tr><th>결제여부</th><td><span class="badge <?= $payInfo[1] ?>"><?= htmlspecialchars($payInfo[0], ENT_QUOTES) ?></span></td></tr>
        <?php if (!empty($row['reject_reason'])): ?>
        <tr><th>거절사유</th><td><?= htmlspecialchars($row['reject_reason'], ENT_QUOTES) ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>사업자 정보</h2></div>
    <table class="form-table">
        <tbody>
        <tr><th>상호명</th><td><?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?></td></tr>
        <tr><th>대표자명</th><td><?= htmlspecialchars($row['ceo_name'], ENT_QUOTES) ?></td></tr>
        <tr><th>사업자등록번호</th><td><?= htmlspecialchars($row['biz_no'] ?? '', ENT_QUOTES) ?: '<span style="color:#6B7A88;">미입력</span>' ?></td></tr>
        <tr><th>중개등록번호</th><td><?= htmlspecialchars($row['license_no'] ?? '', ENT_QUOTES) ?: '<span style="color:#6B7A88;">미입력</span>' ?></td></tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>담당자 정보</h2></div>
    <table class="form-table">
        <tbody>
        <tr><th>담당자</th><td><?= htmlspecialchars($row['manager_name'] ?? '', ENT_QUOTES) ?></td></tr>
        <tr><th>연락처</th><td><?= htmlspecialchars($row['manager_phone'], ENT_QUOTES) ?></td></tr>
        <tr><th>이메일</th><td><?= htmlspecialchars($row['manager_email'], ENT_QUOTES) ?></td></tr>
        <tr><th>전화</th><td><?= htmlspecialchars($row['tel'] ?? '', ENT_QUOTES) ?: '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        <tr><th>팩스</th><td><?= htmlspecialchars($row['fax'] ?? '', ENT_QUOTES) ?: '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        <tr><th>주소</th><td><?= htmlspecialchars(trim(($row['zipcode'] ?? '') . ' ' . ($row['addr1'] ?? '') . ' ' . ($row['addr2'] ?? '')), ENT_QUOTES) ?: '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>도메인 / 메모</h2></div>
    <table class="form-table">
        <tbody>
        <tr><th>희망도메인</th><td><?= !empty($row['sub_domain']) ? htmlspecialchars($row['sub_domain'], ENT_QUOTES) : '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        <tr><th>커스텀도메인</th><td><?= !empty($row['custom_domain']) ? htmlspecialchars($row['custom_domain'], ENT_QUOTES) : '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        <tr><th>홈페이지명</th><td><?= !empty($row['homepage_name']) ? htmlspecialchars($row['homepage_name'], ENT_QUOTES) : '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        <tr><th>메모</th><td><?= !empty($row['memo']) ? nl2br(htmlspecialchars($row['memo'], ENT_QUOTES)) : '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        </tbody>
    </table>
</div>

<div class="form-actions">
    <a href="<?= Admin_SITE ?>/Service/modify/<?= (int) $row['apply_no'] ?>"><button type="button" class="primary">수정</button></a>
    <a href="<?= Admin_SITE ?>/Service"><button type="button">리스트</button></a>
</div>
