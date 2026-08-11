<?php
$msg = session()->getFlashdata('msg');
$err = session()->getFlashdata('err');

$statusLabels = [
    'REQUEST'    => ['요청', 'wait'],
    'ACTIVE'     => ['정상', 'done'],
    'SUSPENDED'  => ['유예', 'wait'],
    'TERMINATED' => ['종료', 'stop'],
];
$installLabels = [
    'PENDING'    => ['대기', 'wait'],
    'INSTALLING' => ['설치중', 'wait'],
    'DONE'       => ['완료', 'done'],
    'FAILED'     => ['실패', 'stop'],
];
$statusInfo = $statusLabels[$row['status']] ?? [$row['status'], 'wait'];
$installStatusInfo = $installLabels[$row['install_status']] ?? [$row['install_status'], 'wait'];
$installed  = ($row['install_status'] ?? null) === 'DONE';
$homepageUrl = !empty($row['fid']) ? 'https://' . $row['fid'] . '.bomul.ai.kr' : '';

// 유예(SUSPENDED)면 15일 유예기간 중 며칠 남았는지 계산 - app/batch/service_expire_check.php의
// SUSPEND_GRACE_DAYS(=ServiceApplyService::SUSPEND_GRACE_DAYS)와 동일한 15일 기준.
$suspendGraceDeadline = null;
$suspendDaysLeft = null;
if ($row['status'] === 'SUSPENDED' && !empty($row['suspended_at'])) {
    $suspendGraceDeadline = date('Y-m-d', strtotime($row['suspended_at'] . ' +15 days'));
    $suspendDaysLeft = (int) ceil((strtotime($suspendGraceDeadline) - strtotime(date('Y-m-d'))) / 86400);
}
?>
<div class="crumb">HOME &nbsp;›&nbsp; 고객관리 &nbsp;›&nbsp; <b>서비스 라이브 상세</b></div>

<div class="page-head">
    <h1>서비스 라이브 상세</h1>
    <div class="actions">
        <a href="<?= Admin_SITE ?>/ServiceLive"><button type="button">목록으로</button></a>
    </div>
</div>

<?= view('common/admin_alert', ['msg' => $msg, 'err' => $err]) ?>

<div class="panel">
    <div class="panel-head"><h2>서비스 정보</h2></div>
    <table class="form-table">
        <tbody>
        <tr><th>라이브번호</th><td><?= (int) $row['live_no'] ?></td></tr>
        <tr><th>상호명</th><td><?= htmlspecialchars($row['company_name'] ?? '', ENT_QUOTES) ?></td></tr>
        <tr><th>담당자</th><td><?= htmlspecialchars($row['manager_name'] ?? '', ENT_QUOTES) ?> (<?= htmlspecialchars($row['manager_email'] ?? '', ENT_QUOTES) ?>)</td></tr>
        <tr><th>도메인(fid)</th><td><?= htmlspecialchars($row['fid'], ENT_QUOTES) ?></td></tr>
        <tr><th>상태</th><td><span class="badge <?= $statusInfo[1] ?>"><?= htmlspecialchars($statusInfo[0], ENT_QUOTES) ?></span></td></tr>
        <?php if ($suspendGraceDeadline !== null): ?>
        <tr><th>유예 마감</th><td>
            <b><?= htmlspecialchars($suspendGraceDeadline, ENT_QUOTES) ?></b>
            (<?= $suspendDaysLeft >= 0 ? "{$suspendDaysLeft}일 남음" : '유예기간 경과 - 다음 배치 때 종료 처리 예정' ?>)
        </td></tr>
        <?php endif; ?>
        <tr><th>시작일</th><td><?= htmlspecialchars((string) $row['start_date'], ENT_QUOTES) ?></td></tr>
        <tr><th>만료일</th><td><b><?= htmlspecialchars((string) $row['expire_date'], ENT_QUOTES) ?></b></td></tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>세팅된 서버 정보</h2></div>
    <table class="form-table">
        <tbody>
        <tr><th>서버명</th><td><?= htmlspecialchars($row['server_code'] ?? '', ENT_QUOTES) ?> <?= !empty($row['server_name']) ? '(' . htmlspecialchars($row['server_name'], ENT_QUOTES) . ')' : '' ?></td></tr>
        <tr><th>서버 host</th><td><?= htmlspecialchars($row['receiver_url'] ?? '', ENT_QUOTES) ?: '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        <tr><th>설치된 홈페이지</th><td>
            <?php if ($homepageUrl): ?>
                <a href="<?= htmlspecialchars($homepageUrl, ENT_QUOTES) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($homepageUrl, ENT_QUOTES) ?></a>
            <?php else: ?>
                <span style="color:#6B7A88;">-</span>
            <?php endif; ?>
        </td></tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>세팅 정보</h2></div>
    <table class="form-table">
        <tbody>
        <tr><th>설치진행상태</th><td><span class="badge <?= $installStatusInfo[1] ?>"><?= htmlspecialchars($installStatusInfo[0], ENT_QUOTES) ?></span></td></tr>
        <tr><th>설치요청일시</th><td><?= htmlspecialchars((string) ($row['install_requested_at'] ?? ''), ENT_QUOTES) ?: '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        <tr><th>설치완료일시</th><td><?= htmlspecialchars((string) ($row['installed_at'] ?? ''), ENT_QUOTES) ?: '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        <tr><th>설치오류</th><td><?= !empty($row['install_error']) ? nl2br(htmlspecialchars($row['install_error'], ENT_QUOTES)) : '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>상태 변경</h2></div>
    <table class="form-table">
        <tbody>
        <tr>
            <th>서비스 상태</th>
            <td>
                <form method="post" action="<?= Admin_SITE ?>/ServiceLive/setStatus/<?= (int) $row['apply_no'] ?>" style="display:flex; gap:8px; align-items:center;" onsubmit="return confirm('서비스 상태를 변경하시겠습니까?');">
                    <select name="status">
                        <?php foreach ($statusLabels as $code => $label): ?>
                            <option value="<?= $code ?>" <?= $row['status'] === $code ? 'selected' : '' ?>><?= htmlspecialchars($label[0], ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">변경</button>
                </form>
                <p style="color:#6B7A88; font-size:12px; margin-top:6px;">
                    '요청'으로 바꾸면 다음 세팅 수집 배치 때 큐에 자동 적재됩니다. 그 외 상태는 즉시 큐로 운영서버에 통보됩니다(설치완료 건만 가능).
                    '유예'로 직접 바꾸면 그 시점부터 15일 유예 타이머가 새로 시작됩니다.
                </p>
            </td>
        </tr>
        <tr>
            <th>설치진행상태</th>
            <td>
                <form method="post" action="<?= Admin_SITE ?>/ServiceLive/setInstallStatus/<?= (int) $row['apply_no'] ?>" style="display:flex; gap:8px; align-items:center;" onsubmit="return confirm('설치진행상태를 변경하시겠습니까?');">
                    <select name="install_status">
                        <?php foreach ($installLabels as $code => $label): ?>
                            <option value="<?= $code ?>" <?= $row['install_status'] === $code ? 'selected' : '' ?>><?= htmlspecialchars($label[0], ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">변경</button>
                </form>
                <p style="color:#6B7A88; font-size:12px; margin-top:6px;">
                    운영서버 콜백이 유실됐거나 실제 상태와 어긋난 경우에만 수동으로 보정하세요.
                </p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>기간 연장 / 만료일 지정</h2></div>
    <?php if (!$installed): ?>
        <p style="padding:16px; color:#6B7A88;">설치가 완료되지 않아 기간을 변경할 수 없습니다.</p>
    <?php else: ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th>빠른 연장</th>
                <td>
                    <form method="post" action="<?= Admin_SITE ?>/ServiceLive/extend/<?= (int) $row['apply_no'] ?>" style="display:inline-block;" onsubmit="return confirm('30일 연장하시겠습니까?');">
                        <input type="hidden" name="days" value="30">
                        <button type="submit" class="primary">30일 연장</button>
                    </form>
                    <form method="post" action="<?= Admin_SITE ?>/ServiceLive/extend/<?= (int) $row['apply_no'] ?>" style="display:inline-block;" onsubmit="return confirm('60일 연장하시겠습니까?');">
                        <input type="hidden" name="days" value="60">
                        <button type="submit" class="primary">60일 연장</button>
                    </form>
                    <p style="color:#6B7A88; font-size:12px; margin-top:6px;">
                        오늘이 만료일 이전이면 만료일 기준으로, 이미 만료된 상태면 오늘 기준으로 연장됩니다.
                        연장 후 만료일이 오늘 이후면 상태는 자동으로 '정상'이 됩니다.
                    </p>
                    <p style="color:#6B7A88; font-size:12px; margin-top:2px;">
                        상태는 만료일 기준 3단계로 자동 결정됩니다 — 오늘&lt;만료일: 정상 / 만료일 당일~+15일: 유예(사용자웹만 차단, 관리자는 로그인 가능) / +15일 이후: 종료(전체 차단).
                    </p>
                </td>
            </tr>
            <tr>
                <th>만료일 직접 지정</th>
                <td>
                    <form method="post" action="<?= Admin_SITE ?>/ServiceLive/setExpire/<?= (int) $row['apply_no'] ?>" style="display:flex; gap:8px; align-items:center;" onsubmit="return confirm('만료일을 변경하시겠습니까?');">
                        <input type="date" name="expire_date" value="<?= htmlspecialchars((string) $row['expire_date'], ENT_QUOTES) ?>" min="<?= htmlspecialchars((string) $row['start_date'], ENT_QUOTES) ?>" required>
                        <button type="submit">만료일 변경</button>
                    </form>
                    <p style="color:#6B7A88; font-size:12px; margin-top:6px;">
                        지정한 날짜 기준으로 상태가 정상/유예/종료 3단계 중 하나로 자동 반영됩니다(위 "빠른 연장" 설명과 동일 규칙).
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
    <?php endif; ?>
</div>
