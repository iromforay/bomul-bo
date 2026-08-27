<?php
// 최근 7일 날짜 배열(0건인 날짜도 그래프에 빈 막대로 나오게 0으로 채워둠) - $periodFrom~$periodTo는
// Home::getindex()에서 이미 "어제까지"로 넘어옴(오늘은 하루가 안 끝나서 그래프가 들쭉날쭉해 보임).
$visitByDate = [];
foreach ($visitDaily as $row) {
    $visitByDate[$row['stat_date']] = $row;
}

$weekDates = [];
for ($d = strtotime($periodFrom); $d <= strtotime($periodTo); $d += 86400) {
    $weekDates[] = date('Y-m-d', $d);
}

$maxVisit = 1;
foreach ($weekDates as $d) {
    $maxVisit = max($maxVisit, (int) ($visitByDate[$d]['visit_total'] ?? 0));
}

$weekVisitTotal = 0;
$weekSignupTotal = 0;
foreach ($weekDates as $d) {
    $weekVisitTotal += (int) ($visitByDate[$d]['visit_total'] ?? 0);
    $weekSignupTotal += (int) ($visitByDate[$d]['signup_total'] ?? 0);
}

// 가입사용자 추이 - 14일치, 마찬가지로 0건 날짜 채움
$signupByDate = [];
foreach ($signupDaily as $row) {
    $signupByDate[$row['signup_date']] = (int) $row['signup_count'];
}
$twoWeekDates = [];
for ($d = strtotime($periodFrom) - 7 * 86400; $d <= strtotime($periodTo); $d += 86400) {
    $twoWeekDates[] = date('Y-m-d', $d);
}
$maxSignup = 1;
foreach ($twoWeekDates as $d) {
    $maxSignup = max($maxSignup, $signupByDate[$d] ?? 0);
}
?>
<div class="crumb">HOME &nbsp;›&nbsp; <b>대시보드</b></div>

<div class="page-head">
    <h1>대시보드</h1>
</div>

<div class="kpi-strip">
    <div class="cell">
        <div class="label">최근 7일 방문자(랜딩 기준)</div>
        <div class="val"><?= number_format($weekVisitTotal) ?></div>
    </div>
    <div class="cell">
        <div class="label">최근 7일 가입전환</div>
        <div class="val"><?= number_format($weekSignupTotal) ?></div>
    </div>
    <div class="cell">
        <div class="label">이번 주 1위 검색어</div>
        <div class="val" style="font-size:15px;"><?= !empty($topKeywords) ? htmlspecialchars($topKeywords[0]['search_keyword'], ENT_QUOTES) : '-' ?></div>
    </div>
</div>

<div class="cols-2">
    <div>
        <div class="panel">
            <div class="panel-head"><h2>주간 방문자 추이 (<?= htmlspecialchars($periodFrom, ENT_QUOTES) ?> ~ <?= htmlspecialchars($periodTo, ENT_QUOTES) ?>)</h2></div>
            <div class="legend"><span><span class="sw visit"></span>방문자</span></div>
            <div class="bar-chart">
                <?php foreach ($weekDates as $d): $v = (int) ($visitByDate[$d]['visit_total'] ?? 0); ?>
                    <div class="col">
                        <div class="val"><?= $v > 0 ? number_format($v) : '' ?></div>
                        <div class="bar" style="height:<?= max(2, round($v / $maxVisit * 100)) ?>%;"></div>
                        <div class="lbl"><?= date('m/d', strtotime($d)) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>가입사용자 추이 (최근 14일)</h2></div>
            <div class="legend"><span><span class="sw signup"></span>신규가입</span></div>
            <div class="bar-chart">
                <?php foreach ($twoWeekDates as $d): $v = $signupByDate[$d] ?? 0; ?>
                    <div class="col">
                        <div class="val"><?= $v > 0 ? $v : '' ?></div>
                        <div class="bar secondary" style="height:<?= max(2, round($v / $maxSignup * 100)) ?>%;"></div>
                        <div class="lbl"><?= date('m/d', strtotime($d)) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>주간 인기 검색어</h2></div>
        <table>
            <thead>
            <tr><th>검색어</th><th>유입경로</th><th style="text-align:right;">방문자</th><th style="text-align:right;">가입전환</th></tr>
            </thead>
            <tbody>
            <?php if (empty($topKeywords)): ?>
                <tr><td colspan="4" style="text-align:center; color:var(--muted); padding:16px;">이번 주 검색 유입 데이터가 없습니다.</td></tr>
            <?php else: ?>
                <?php foreach ($topKeywords as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['search_keyword'], ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($row['referrer_host'], ENT_QUOTES) ?: '-' ?></td>
                        <td style="text-align:right;"><?= number_format((int) $row['visit_total']) ?></td>
                        <td style="text-align:right;"><?= number_format((int) $row['signup_total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
