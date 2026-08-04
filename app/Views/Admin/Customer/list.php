<?php
$msg = session()->getFlashdata('msg');
$err = session()->getFlashdata('err');
?>
<div class="crumb">HOME &nbsp;›&nbsp; 고객관리 &nbsp;›&nbsp; <b>고객 목록</b></div>

<div class="page-head">
    <h1>고객 목록(중개사)</h1>
    <div class="actions">
        <a href="<?= Admin_SITE ?>/Customer/write"><button class="primary">+ 고객 등록</button></a>
    </div>
</div>

<?php if ($msg): ?>
    <div class="panel" style="border-color:#2E7D32;"><div style="padding:10px 14px; color:#2E7D32; font-size:12.5px;"><?= htmlspecialchars($msg, ENT_QUOTES) ?></div></div>
<?php endif; ?>
<?php if ($err): ?>
    <div class="panel" style="border-color:#B3261E;"><div style="padding:10px 14px; color:#B3261E; font-size:12.5px;"><?= htmlspecialchars($err, ENT_QUOTES) ?></div></div>
<?php endif; ?>

<div class="panel">
    <div class="panel-head">
        <h2>전체 고객</h2>
    </div>
    <table>
        <thead>
        <tr>
            <th>번호</th><th>아이디</th><th>담당자</th><th>중개사무소명</th>
            <th>사업자등록번호</th><th>중개사무소등록번호</th><th>휴대폰</th><th>이메일</th>
            <th>사업자검증</th><th>중개사검증</th><th>사용여부</th><th>가입일</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="12" style="text-align:center; color:#6B7A88;">등록된 고객이 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($list as $row): ?>
                <tr>
                    <td><?= (int) $row['customer_no'] ?></td>
                    <td><?= htmlspecialchars($row['customer_id'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['customer_name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['biz_reg_no'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['broker_reg_no'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['phone'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['email'], ENT_QUOTES) ?></td>
                    <td>
                        <?php if (($row['biz_reg_verified_yn'] ?? 'N') === 'Y'): ?>
                            <span class="badge done">검증됨</span>
                        <?php else: ?>
                            <span class="badge wait">대기</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (($row['broker_reg_verified_yn'] ?? 'N') === 'Y'): ?>
                            <span class="badge done">검증됨</span>
                        <?php else: ?>
                            <span class="badge wait">대기</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (($row['use_yn'] ?? 'Y') === 'Y'): ?>
                            <span class="badge done">사용</span>
                        <?php else: ?>
                            <span class="badge stop">미사용</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars((string) $row['reg_date'], ENT_QUOTES) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
