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
$typeLabels = ['NEW' => ['신규', 'wait'], 'RENEW' => ['연장', 'done']];
$statusInfo = $statusLabels[$row['status']] ?? [$row['status'], 'wait'];
$payInfo    = !empty($row['pay_status']) ? ($payLabels[$row['pay_status']] ?? [$row['pay_status'], 'wait']) : ['미결제', 'hold'];
$typeInfo   = $typeLabels[$row['apply_type'] ?? 'NEW'] ?? [$row['apply_type'], 'wait'];

// 세팅 진행 현황(승인완료 건만) - queue(action='new')의 status를 3단계로 매핑.
// PENDING(대기, 아직 push 전) → SENT(세팅중, 운영서버로 push됨·응답 대기) → DONE(완료)/FAILED(실패).
$queueStatus  = $row['queue_status'] ?? null;
$queueStepMap = ['PENDING' => 1, 'SENT' => 2, 'DONE' => 3, 'FAILED' => 3];
$queueStep    = $queueStepMap[$queueStatus] ?? 0;
$queueFailed  = $queueStatus === 'FAILED';
$queueStepLabels = ['대기', '세팅중', $queueFailed ? '실패' : '완료'];
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
        <tr><th>신청유형</th><td><span class="badge <?= $typeInfo[1] ?>"><?= htmlspecialchars($typeInfo[0], ENT_QUOTES) ?></span>
            <?php if (($row['apply_type'] ?? 'NEW') === 'RENEW' && !empty($row['renew_live_no'])): ?>
                <span style="color:#6B7A88; font-size:12px; margin-left:6px;">(연장 대상 live_no: <?= (int) $row['renew_live_no'] ?>)</span>
            <?php endif; ?>
        </td></tr>
        <tr><th>신청상태</th><td><span class="badge <?= $statusInfo[1] ?>"><?= htmlspecialchars($statusInfo[0], ENT_QUOTES) ?></span></td></tr>
        <tr><th>결제여부</th><td><span class="badge <?= $payInfo[1] ?>"><?= htmlspecialchars($payInfo[0], ENT_QUOTES) ?></span></td></tr>
        <?php if (!empty($row['reject_reason'])): ?>
        <tr><th>거절사유</th><td><?= htmlspecialchars($row['reject_reason'], ENT_QUOTES) ?></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>신청 상품 정보</h2></div>
    <table class="form-table">
        <tbody>
        <?php if (empty($row['product_name'])): ?>
        <tr><td colspan="2" style="color:#6B7A88;">상품 정보를 찾을 수 없습니다(삭제된 상품일 수 있음).</td></tr>
        <?php else: ?>
        <tr><th>상품명</th><td>
            <?= htmlspecialchars($row['product_name'], ENT_QUOTES) ?>
            <span class="badge wait"><?= htmlspecialchars(product_period_label($row['billing_type'], $row['period_day']), ENT_QUOTES) ?></span>
            <?php if (((string) ($row['is_event'] ?? '0')) === '1'): ?><span class="badge wait">이벤트</span><?php endif; ?>
        </td></tr>
        <tr><th>상품코드 / 유형</th><td><?= htmlspecialchars($row['product_code'] ?? '', ENT_QUOTES) ?> / <?= ($row['product_type'] ?? 'BASE') === 'ADDON' ? '부가서비스' : '기본상품' ?></td></tr>
        <tr><th>판매가</th><td>
            <?php if (!empty($row['list_price']) && (int) $row['list_price'] > (int) $row['price']): ?>
                <span style="text-decoration:line-through; color:#6B7A88;"><?= number_format((int) $row['list_price']) ?>원</span>
            <?php endif; ?>
            <?= (int) $row['price'] > 0 ? number_format((int) $row['price']) . '원' : '무료' ?>
        </td></tr>
        <?php if (!empty($row['product_description'])): ?>
        <tr><th>상품설명</th><td><?= htmlspecialchars($row['product_description'], ENT_QUOTES) ?></td></tr>
        <?php endif; ?>
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

<?php if ($row['status'] === 'APPROVED'): ?>
<div class="panel">
    <div class="panel-head"><h2>세팅 진행 현황</h2></div>
    <?php if (!$queueStatus): ?>
        <p style="padding:16px; color:#6B7A88;">
            아직 세팅 큐에 등록되지 않았습니다. 목록에서 "큐 적재" 버튼으로 재시도해주세요.
        </p>
    <?php else: ?>
        <div style="padding:16px; display:flex; align-items:center; gap:8px;">
            <?php foreach ($queueStepLabels as $i => $label): ?>
                <?php
                $stepNo = $i + 1;
                if ($queueFailed && $stepNo === 3) {
                    $stepClass = 'stop';
                } elseif ($stepNo <= $queueStep) {
                    $stepClass = 'done';
                } else {
                    $stepClass = 'hold';
                }
                ?>
                <span class="badge <?= $stepClass ?>"><?= $stepNo ?>. <?= htmlspecialchars($label, ENT_QUOTES) ?></span>
                <?php if ($stepNo < 3): ?><span style="color:#6B7A88;">›</span><?php endif; ?>
            <?php endforeach; ?>
        </div>
        <table class="form-table">
            <tbody>
            <tr><th>큐 적재일시</th><td><?= htmlspecialchars((string) ($row['queue_sent_at'] ?? ''), ENT_QUOTES) ?: '<span style="color:#6B7A88;">-</span>' ?></td></tr>
            <tr><th>완료/실패일시</th><td><?= htmlspecialchars((string) ($row['queue_completed_at'] ?? ''), ENT_QUOTES) ?: '<span style="color:#6B7A88;">-</span>' ?></td></tr>
            <?php if ($queueFailed): ?>
            <tr><th>실패사유</th><td><?= !empty($row['queue_fail_reason']) ? nl2br(htmlspecialchars($row['queue_fail_reason'], ENT_QUOTES)) : '<span style="color:#6B7A88;">-</span>' ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="form-actions">
    <a href="<?= Admin_SITE ?>/Service/modify/<?= (int) $row['apply_no'] ?>"><button type="button" class="primary">수정</button></a>
    <a href="<?= Admin_SITE ?>/Service"><button type="button">리스트</button></a>
</div>
