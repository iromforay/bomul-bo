<div class="crumb">HOME &nbsp;›&nbsp; 고객관리 &nbsp;›&nbsp; <b>서비스 신청 수정</b></div>

<div class="page-head">
    <h1>서비스 신청 수정</h1>
</div>

<form method="post" action="<?= Admin_SITE ?>/Service/modify/<?= (int) $row['apply_no'] ?>">

<div class="panel">
    <div class="panel-head"><h2>사업자 정보</h2></div>
    <table class="form-table">
        <tbody>
        <tr>
            <th>상호명</th>
            <td><input type="text" name="company_name" value="<?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?>" required></td>
        </tr>
        <tr>
            <th>대표자명</th>
            <td><input type="text" name="ceo_name" value="<?= htmlspecialchars($row['ceo_name'], ENT_QUOTES) ?>" required></td>
        </tr>
        <tr>
            <th>사업자등록번호</th>
            <td><input type="text" name="biz_no" value="<?= htmlspecialchars($row['biz_no'] ?? '', ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <th>중개등록번호</th>
            <td><input type="text" name="license_no" value="<?= htmlspecialchars($row['license_no'] ?? '', ENT_QUOTES) ?>"></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>담당자 정보</h2></div>
    <table class="form-table">
        <tbody>
        <tr>
            <th>담당자명</th>
            <td><input type="text" name="manager_name" value="<?= htmlspecialchars($row['manager_name'] ?? '', ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <th>연락처</th>
            <td><input type="text" name="manager_phone" value="<?= htmlspecialchars($row['manager_phone'], ENT_QUOTES) ?>" required></td>
        </tr>
        <tr>
            <th>이메일</th>
            <td><input type="text" name="manager_email" value="<?= htmlspecialchars($row['manager_email'], ENT_QUOTES) ?>" required></td>
        </tr>
        <tr>
            <th>전화</th>
            <td><input type="text" name="tel" value="<?= htmlspecialchars($row['tel'] ?? '', ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <th>팩스</th>
            <td><input type="text" name="fax" value="<?= htmlspecialchars($row['fax'] ?? '', ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <th>우편번호</th>
            <td><input type="text" name="zipcode" value="<?= htmlspecialchars($row['zipcode'] ?? '', ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <th>주소</th>
            <td>
                <input type="text" name="addr1" value="<?= htmlspecialchars($row['addr1'] ?? '', ENT_QUOTES) ?>" placeholder="기본주소">
                <input type="text" name="addr2" value="<?= htmlspecialchars($row['addr2'] ?? '', ENT_QUOTES) ?>" placeholder="상세주소" style="margin-top:6px;">
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>도메인 / 메모</h2></div>
    <table class="form-table">
        <tbody>
        <tr>
            <th>희망도메인</th>
            <td><input type="text" name="sub_domain" value="<?= htmlspecialchars($row['sub_domain'] ?? '', ENT_QUOTES) ?>" required></td>
        </tr>
        <tr>
            <th>커스텀도메인</th>
            <td><input type="text" name="custom_domain" value="<?= htmlspecialchars($row['custom_domain'] ?? '', ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <th>홈페이지명</th>
            <td><input type="text" name="homepage_name" value="<?= htmlspecialchars($row['homepage_name'] ?? '', ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <th>메모</th>
            <td><textarea name="memo" rows="4"><?= htmlspecialchars($row['memo'] ?? '', ENT_QUOTES) ?></textarea></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="form-actions">
    <button type="submit" class="primary">저장</button>
    <a href="<?= Admin_SITE ?>/Service/view/<?= (int) $row['apply_no'] ?>"><button type="button">취소</button></a>
</div>

</form>
