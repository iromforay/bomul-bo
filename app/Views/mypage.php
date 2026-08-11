<?php
$statusLabels = [
    'REQUEST'    => '설치 준비중',
    'ACTIVE'     => '정상 운영중',
    'SUSPENDED'  => '일시중단(유예)',
    'TERMINATED' => '이용 종료',
];
$payStatusLabels = [
    'READY'     => '결제대기',
    'PAID'      => '결제완료',
    'FAILED'    => '결제실패',
    'CANCELLED' => '결제취소',
    'REFUNDED'  => '환불',
];
$payMethodLabels = ['CARD' => '카드', 'VBANK' => '가상계좌', 'TRANSFER' => '무통장입금', 'NONE' => '무료'];
// N=대기, Y=검증완료, W=보류, F=검증실패 (CustomerService::VERIFY_STATUSES와 동일)
$verifyLabels = ['N' => '검증대기', 'W' => '보류', 'Y' => '검증완료', 'F' => '검증실패'];

$service = $service ?? null;
$suspendGraceDeadline = null;
if ($service && $service['status'] === 'SUSPENDED' && !empty($service['suspended_at'])) {
    $suspendGraceDeadline = date('Y-m-d', strtotime($service['suspended_at'] . ' +15 days'));
}
?>
<div class="signup-wrap">
    <div class="signup-card">
        <h1 class="signup-title">마이페이지</h1>
        <p class="signup-desc"><?= htmlspecialchars($customer['company_name'] ?? '', ENT_QUOTES) ?> 님의 서비스 현황입니다</p>
    </div>

    <?php if (!empty($notice)): ?>
        <div class="signup-card" style="border:1px solid #FFD166; background:#FFFBEB;">
            <p><?= htmlspecialchars($notice, ENT_QUOTES) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="signup-card" style="border:1px solid #B3261E; background:#FDECEA;">
            <p><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
        </div>
    <?php endif; ?>

    <div class="signup-card">
        <h2 class="section__title" style="font-size:18px;">필수 서류 제출</h2>
        <p class="signup-desc">사업자등록증과 중개사무소등록증을 제출해주세요. 검증 완료까지 영업일 기준 1~2일 소요됩니다.</p>

        <form method="post" action="/mypage/documents">
            <table style="width:100%; border-collapse:collapse;">
                <tbody>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88; vertical-align:top;">사업자등록번호</th>
                    <td style="padding:8px 12px;">
                        <input type="text" name="biz_reg_no" value="<?= htmlspecialchars($customer['biz_reg_no'] ?? '', ENT_QUOTES) ?>" required>
                        <span style="margin-left:8px; color:#6B7A88;">(<?= htmlspecialchars($verifyLabels[$customer['biz_reg_verified_yn'] ?? 'N'] ?? '검증대기', ENT_QUOTES) ?>)</span>
                    </td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88; vertical-align:top;">사업자등록증</th>
                    <td style="padding:8px 12px;">
                        <?php
                        $bizFile = $customer['biz_reg_file_name'] ?? '';
                        $bizUrl  = $bizFile ? SITE_PDS . $customer['biz_reg_file_dir'] . $bizFile : '';
                        $bizIsImg = $bizFile && preg_match('/\.(png|jpe?g|gif|webp)$/i', $bizFile);
                        $bizVerified = ($customer['biz_reg_verified_yn'] ?? 'N') === 'Y';
                        ?>
                        <div class="upload-drop<?= $bizFile ? ' has-file' : '' ?>" data-upload-url="/mypage/uploadTemp" data-file-type="biz"
                             data-path-target="#biz_reg_file_path" data-name-target="#biz_reg_file_name" data-del-target="#del_biz_reg_img">
                            <input type="file" accept=".png,.jpg,.jpeg,.pdf">
                            <div class="upload-hint">클릭하거나 파일을 끌어다 놓으세요 (PNG/JPG/PDF)</div>
                            <div class="upload-preview">
                                <?php if ($bizIsImg): ?><img src="<?= htmlspecialchars($bizUrl, ENT_QUOTES) ?>" class="upload-thumb"><?php endif; ?>
                                <?php if ($bizFile): ?><span><?= htmlspecialchars($bizFile, ENT_QUOTES) ?></span><?php endif; ?>
                            </div>
                            <button type="button" class="upload-remove" <?= ($bizFile && !$bizVerified) ? '' : 'hidden' ?>>삭제</button>
                        </div>
                        <?php if ($bizVerified): ?>
                            <p style="color:#6B7A88; font-size:12px; margin-top:4px;">검증완료된 서류는 삭제할 수 없습니다. 재제출(교체)은 가능합니다.</p>
                        <?php endif; ?>
                        <input type="hidden" id="biz_reg_file_path" name="biz_reg_file_path" value="">
                        <input type="hidden" id="biz_reg_file_name" name="biz_reg_file_name" value="">
                        <input type="hidden" id="del_biz_reg_img" name="del_img[]" value="biz" disabled>
                    </td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88; vertical-align:top;">중개사무소등록번호</th>
                    <td style="padding:8px 12px;">
                        <input type="text" name="broker_reg_no" value="<?= htmlspecialchars($customer['broker_reg_no'] ?? '', ENT_QUOTES) ?>" required>
                        <span style="margin-left:8px; color:#6B7A88;">(<?= htmlspecialchars($verifyLabels[$customer['broker_reg_verified_yn'] ?? 'N'] ?? '검증대기', ENT_QUOTES) ?>)</span>
                    </td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88; vertical-align:top;">중개사무소등록증</th>
                    <td style="padding:8px 12px;">
                        <?php
                        $brokerFile = $customer['broker_reg_file_name'] ?? '';
                        $brokerUrl  = $brokerFile ? SITE_PDS . $customer['broker_reg_file_dir'] . $brokerFile : '';
                        $brokerIsImg = $brokerFile && preg_match('/\.(png|jpe?g|gif|webp)$/i', $brokerFile);
                        $brokerVerified = ($customer['broker_reg_verified_yn'] ?? 'N') === 'Y';
                        ?>
                        <div class="upload-drop<?= $brokerFile ? ' has-file' : '' ?>" data-upload-url="/mypage/uploadTemp" data-file-type="broker"
                             data-path-target="#broker_reg_file_path" data-name-target="#broker_reg_file_name" data-del-target="#del_broker_reg_img">
                            <input type="file" accept=".png,.jpg,.jpeg,.pdf">
                            <div class="upload-hint">클릭하거나 파일을 끌어다 놓으세요 (PNG/JPG/PDF)</div>
                            <div class="upload-preview">
                                <?php if ($brokerIsImg): ?><img src="<?= htmlspecialchars($brokerUrl, ENT_QUOTES) ?>" class="upload-thumb"><?php endif; ?>
                                <?php if ($brokerFile): ?><span><?= htmlspecialchars($brokerFile, ENT_QUOTES) ?></span><?php endif; ?>
                            </div>
                            <button type="button" class="upload-remove" <?= ($brokerFile && !$brokerVerified) ? '' : 'hidden' ?>>삭제</button>
                        </div>
                        <?php if ($brokerVerified): ?>
                            <p style="color:#6B7A88; font-size:12px; margin-top:4px;">검증완료된 서류는 삭제할 수 없습니다. 재제출(교체)은 가능합니다.</p>
                        <?php endif; ?>
                        <input type="hidden" id="broker_reg_file_path" name="broker_reg_file_path" value="">
                        <input type="hidden" id="broker_reg_file_name" name="broker_reg_file_name" value="">
                        <input type="hidden" id="del_broker_reg_img" name="del_img[]" value="broker" disabled>
                    </td>
                </tr>
                </tbody>
            </table>

            <button type="submit" class="btn btn--primary" style="margin-top:12px;">서류 제출</button>
        </form>
    </div>

    <div class="signup-card">
        <h2 class="section__title" style="font-size:18px;">내 서비스 현황</h2>

        <?php if (!$service): ?>
            <p>아직 이용중인 서비스가 없습니다.</p>
            <a href="/service-apply" class="btn btn--primary">서비스 신청하기</a>
        <?php else: ?>
            <table style="width:100%; border-collapse:collapse;">
                <tbody>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">상품</th>
                    <td style="padding:8px 12px;"><?= htmlspecialchars($service['product_name'] ?? '', ENT_QUOTES) ?: '-' ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">도메인</th>
                    <td style="padding:8px 12px;"><?= htmlspecialchars($service['sub_domain'] ?? '', ENT_QUOTES) ?: '-' ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">이용상태</th>
                    <td style="padding:8px 12px;"><?= htmlspecialchars($statusLabels[$service['status']] ?? $service['status'], ENT_QUOTES) ?></td>
                </tr>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">이용만료일</th>
                    <td style="padding:8px 12px;"><b><?= htmlspecialchars((string) $service['expire_date'], ENT_QUOTES) ?></b></td>
                </tr>
                <?php if ($suspendGraceDeadline): ?>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">완전종료 예정일</th>
                    <td style="padding:8px 12px; color:#B3261E;">
                        <?= htmlspecialchars($suspendGraceDeadline, ENT_QUOTES) ?>
                        (그 전까지는 관리자 로그인만 가능, 방문객 페이지는 차단된 상태입니다)
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top:16px; display:flex; gap:8px; flex-wrap:wrap;">
                <a href="/mypage/extend" class="btn btn--primary">서비스 연장 신청</a>
                <a href="/mypage/subscribe" class="btn btn--outline">정기결제 신청하기</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="signup-card">
        <h2 class="section__title" style="font-size:18px;">내 결제 리스트</h2>

        <?php if (empty($payments)): ?>
            <p>결제 이력이 없습니다.</p>
        <?php else: ?>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                <tr>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">일시</th>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">상품</th>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">결제수단</th>
                    <th style="text-align:right; padding:8px 12px; color:#6B7A88;">금액</th>
                    <th style="text-align:left; padding:8px 12px; color:#6B7A88;">상태</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($payments as $pay): ?>
                    <tr>
                        <td style="padding:8px 12px;"><?= htmlspecialchars((string) ($pay['paid_at'] ?? $pay['created_at'] ?? ''), ENT_QUOTES) ?></td>
                        <td style="padding:8px 12px;"><?= htmlspecialchars($pay['product_name'] ?? '', ENT_QUOTES) ?: '-' ?></td>
                        <td style="padding:8px 12px;"><?= htmlspecialchars($payMethodLabels[$pay['pay_method']] ?? $pay['pay_method'], ENT_QUOTES) ?></td>
                        <td style="padding:8px 12px; text-align:right;"><?= (int) $pay['pay_amount'] > 0 ? number_format((int) $pay['pay_amount']) . '원' : '무료' ?></td>
                        <td style="padding:8px 12px;"><?= htmlspecialchars($payStatusLabels[$pay['pay_status']] ?? $pay['pay_status'], ENT_QUOTES) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="<?= RS_JS ?>/common/upload.js?var=<?= FRESH_TIME ?>"></script>
