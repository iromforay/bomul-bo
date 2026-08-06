<div class="crumb">HOME &nbsp;›&nbsp; 고객관리 &nbsp;›&nbsp; <b>고객 상세</b></div>

<div class="page-head">
    <h1>고객 상세</h1>
    <div class="actions">
        <a href="<?= Admin_SITE ?>/Customer"><button type="button">목록으로</button></a>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>기본 정보</h2></div>
    <table class="form-table">
        <tbody>
        <tr><th>고객번호</th><td><?= (int) $row['customer_idx'] ?></td></tr>
        <tr><th>아이디</th><td><?= htmlspecialchars($row['customer_id'], ENT_QUOTES) ?></td></tr>
        <tr><th>담당자명</th><td><?= htmlspecialchars($row['customer_name'] ?? '', ENT_QUOTES) ?: '<span style="color:#6B7A88;">미입력 - 서비스 신청 시 결정</span>' ?></td></tr>
        <tr><th>중개사무소명</th><td><?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?></td></tr>
        <tr><th>휴대폰번호</th><td><?= htmlspecialchars($row['phone'], ENT_QUOTES) ?></td></tr>
        <tr><th>이메일</th><td><?= htmlspecialchars($row['email'] ?? '', ENT_QUOTES) ?: '<span style="color:#6B7A88;">미입력</span>' ?></td></tr>
        <tr>
            <th>계정 사용여부</th>
            <td>
                <?php if (($row['use_yn'] ?? 'Y') === 'Y'): ?>
                    <span class="badge done">사용</span>
                <?php else: ?>
                    <span class="badge stop">미사용</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr><th>가입일</th><td><?= htmlspecialchars((string) $row['reg_date'], ENT_QUOTES) ?></td></tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>등록번호 / 검증상태</h2></div>
    <table class="form-table">
        <tbody>
        <tr>
            <th>사업자등록번호</th>
            <td>
                <?= htmlspecialchars($row['biz_reg_no'] ?? '', ENT_QUOTES) ?: '<span style="color:#6B7A88;">미입력</span>' ?>
                <?= verify_status_badge($row['biz_reg_verified_yn'] ?? null) ?>
            </td>
        </tr>
        <tr>
            <th>중개사무소등록번호</th>
            <td>
                <?= htmlspecialchars($row['broker_reg_no'] ?? '', ENT_QUOTES) ?: '<span style="color:#6B7A88;">미입력</span>' ?>
                <?= verify_status_badge($row['broker_reg_verified_yn'] ?? null) ?>
            </td>
        </tr>
        <tr>
            <th>사업자등록증</th>
            <td>
                <?php if (!empty($row['biz_reg_file_name'])): ?>
                    <a href="<?= SITE_PDS . $row['biz_reg_file_dir'] . $row['biz_reg_file_name'] ?>" target="_blank">첨부파일 보기</a>
                <?php else: ?>
                    <span style="color:#6B7A88;">미제출</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>중개사무소등록증</th>
            <td>
                <?php if (!empty($row['broker_reg_file_name'])): ?>
                    <a href="<?= SITE_PDS . $row['broker_reg_file_dir'] . $row['broker_reg_file_name'] ?>" target="_blank">첨부파일 보기</a>
                <?php else: ?>
                    <span style="color:#6B7A88;">미제출</span>
                <?php endif; ?>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>서비스 진행상태</h2></div>
    <table class="form-table">
        <tbody>
        <tr>
            <th>서비스 사용여부</th>
            <td>
                <?php if (($row['service_yn'] ?? 'N') === 'Y'): ?>
                    <span class="badge done">사용중</span>
                <?php else: ?>
                    <span class="badge stop">미사용</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr><th>서비스 만료일</th><td><?= !empty($row['service_expire_date']) ? htmlspecialchars((string) $row['service_expire_date'], ENT_QUOTES) : '<span style="color:#6B7A88;">-</span>' ?></td></tr>
        <tr><th>fid(2차도메인)</th><td><?= !empty($row['fid']) ? htmlspecialchars($row['fid'], ENT_QUOTES) : '<span style="color:#6B7A88;">미배정 - 서비스 신청 시 결정</span>' ?></td></tr>
        <tr>
            <th>중앙회원 이관</th>
            <td>
                <?php if (($row['migrated_yn'] ?? 'N') === 'Y'): ?>
                    <span class="badge done">이관완료</span> (member_no: <?= (int) $row['member_no'] ?>, <?= htmlspecialchars((string) $row['migrated_date'], ENT_QUOTES) ?>)
                <?php else: ?>
                    <span class="badge wait">미이관</span>
                <?php endif; ?>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="form-actions">
    <a href="<?= Admin_SITE ?>/Customer/modify/<?= (int) $row['customer_idx'] ?>"><button type="button" class="primary">수정</button></a>
    <a href="<?= Admin_SITE ?>/Customer"><button type="button">리스트</button></a>
</div>
