<?php
// N=대기, Y=검증완료, W=보류, F=검증실패 (CustomerService::VERIFY_STATUSES와 동일)
$verifyOptions = ['N' => '검증대기', 'W' => '보류', 'Y' => '검증완료', 'F' => '검증실패'];
?>
<div class="crumb">HOME &nbsp;›&nbsp; 고객관리 &nbsp;›&nbsp; <b>고객 수정</b></div>

<div class="page-head">
    <h1>고객 수정</h1>
</div>

<form method="post" action="<?= Admin_SITE ?>/Customer/modify/<?= (int) $row['customer_idx'] ?>">

<div class="panel">
    <div class="panel-head"><h2>기본 정보</h2></div>
    <table class="form-table">
        <tbody>
        <tr>
            <th>아이디</th>
            <td><input type="text" name="customer_id" value="<?= htmlspecialchars($row['customer_id'], ENT_QUOTES) ?>" required></td>
        </tr>
        <tr>
            <th>비밀번호</th>
            <td><input type="password" name="customer_pw" placeholder="변경할 때만 입력하세요"></td>
        </tr>
        <tr>
            <th>담당자명</th>
            <td><input type="text" name="customer_name" value="<?= htmlspecialchars($row['customer_name'], ENT_QUOTES) ?>" required></td>
        </tr>
        <tr>
            <th>중개사무소명</th>
            <td><input type="text" name="company_name" value="<?= htmlspecialchars($row['company_name'], ENT_QUOTES) ?>" required></td>
        </tr>
        <tr>
            <th>휴대폰번호</th>
            <td><input type="text" name="phone" value="<?= htmlspecialchars($row['phone'], ENT_QUOTES) ?>" required></td>
        </tr>
        <tr>
            <th>이메일</th>
            <td><input type="text" name="email" value="<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>" required></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>등록번호 / 서류 / 검증상태</h2></div>
    <table class="form-table">
        <tbody>
        <tr>
            <th>사업자등록번호</th>
            <td>
                <input type="text" name="biz_reg_no" value="<?= htmlspecialchars($row['biz_reg_no'], ENT_QUOTES) ?>" required>
                <select name="biz_reg_verified_yn" style="width:auto; margin-left:8px;">
                    <?php foreach ($verifyOptions as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($row['biz_reg_verified_yn'] ?? 'N') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>사업자등록증</th>
            <td>
                <?php
                $bizFile = $row['biz_reg_file_name'] ?? '';
                $bizUrl  = $bizFile ? SITE_PDS . $row['biz_reg_file_dir'] . $bizFile : '';
                $bizIsImg = $bizFile && preg_match('/\.(png|jpe?g|gif|webp)$/i', $bizFile);
                ?>
                <div class="upload-drop<?= $bizFile ? ' has-file' : '' ?>" data-upload-url="<?= Admin_SITE ?>/Customer/uploadTemp" data-file-type="biz"
                     data-path-target="#biz_reg_file_path" data-name-target="#biz_reg_file_name" data-del-target="#del_biz_reg_img">
                    <input type="file" accept=".png,.jpg,.jpeg,.pdf">
                    <div class="upload-hint">클릭하거나 파일을 끌어다 놓으세요 (PNG/JPG/PDF)</div>
                    <div class="upload-preview">
                        <?php if ($bizIsImg): ?><img src="<?= htmlspecialchars($bizUrl, ENT_QUOTES) ?>" class="upload-thumb"><?php endif; ?>
                        <?php if ($bizFile): ?><span><?= htmlspecialchars($bizFile, ENT_QUOTES) ?></span><?php endif; ?>
                    </div>
                    <button type="button" class="upload-remove" <?= $bizFile ? '' : 'hidden' ?>>삭제</button>
                </div>
                <input type="hidden" id="biz_reg_file_path" name="biz_reg_file_path" value="">
                <input type="hidden" id="biz_reg_file_name" name="biz_reg_file_name" value="">
                <input type="hidden" id="del_biz_reg_img" name="del_img[]" value="biz" disabled>
            </td>
        </tr>
        <tr>
            <th>중개사무소등록번호</th>
            <td>
                <input type="text" name="broker_reg_no" value="<?= htmlspecialchars($row['broker_reg_no'], ENT_QUOTES) ?>" required>
                <select name="broker_reg_verified_yn" style="width:auto; margin-left:8px;">
                    <?php foreach ($verifyOptions as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($row['broker_reg_verified_yn'] ?? 'N') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>중개사무소등록증</th>
            <td>
                <?php
                $brokerFile = $row['broker_reg_file_name'] ?? '';
                $brokerUrl  = $brokerFile ? SITE_PDS . $row['broker_reg_file_dir'] . $brokerFile : '';
                $brokerIsImg = $brokerFile && preg_match('/\.(png|jpe?g|gif|webp)$/i', $brokerFile);
                ?>
                <div class="upload-drop<?= $brokerFile ? ' has-file' : '' ?>" data-upload-url="<?= Admin_SITE ?>/Customer/uploadTemp" data-file-type="broker"
                     data-path-target="#broker_reg_file_path" data-name-target="#broker_reg_file_name" data-del-target="#del_broker_reg_img">
                    <input type="file" accept=".png,.jpg,.jpeg,.pdf">
                    <div class="upload-hint">클릭하거나 파일을 끌어다 놓으세요 (PNG/JPG/PDF)</div>
                    <div class="upload-preview">
                        <?php if ($brokerIsImg): ?><img src="<?= htmlspecialchars($brokerUrl, ENT_QUOTES) ?>" class="upload-thumb"><?php endif; ?>
                        <?php if ($brokerFile): ?><span><?= htmlspecialchars($brokerFile, ENT_QUOTES) ?></span><?php endif; ?>
                    </div>
                    <button type="button" class="upload-remove" <?= $brokerFile ? '' : 'hidden' ?>>삭제</button>
                </div>
                <input type="hidden" id="broker_reg_file_path" name="broker_reg_file_path" value="">
                <input type="hidden" id="broker_reg_file_name" name="broker_reg_file_name" value="">
                <input type="hidden" id="del_broker_reg_img" name="del_img[]" value="broker" disabled>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="panel">
    <div class="panel-head"><h2>계정 / 서비스 상태</h2></div>
    <table class="form-table">
        <tbody>
        <tr>
            <th>계정 사용여부</th>
            <td>
                <select name="use_yn">
                    <option value="Y" <?= ($row['use_yn'] ?? 'Y') === 'Y' ? 'selected' : '' ?>>사용</option>
                    <option value="N" <?= ($row['use_yn'] ?? 'Y') === 'N' ? 'selected' : '' ?>>미사용(정지)</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>서비스 사용여부</th>
            <td>
                <select name="service_yn">
                    <option value="N" <?= ($row['service_yn'] ?? 'N') === 'N' ? 'selected' : '' ?>>미사용</option>
                    <option value="Y" <?= ($row['service_yn'] ?? 'N') === 'Y' ? 'selected' : '' ?>>사용중</option>
                </select>
            </td>
        </tr>
        <tr>
            <th>서비스 만료일</th>
            <td><input type="date" name="service_expire_date" value="<?= htmlspecialchars((string) ($row['service_expire_date'] ?? ''), ENT_QUOTES) ?>"></td>
        </tr>
        <tr>
            <th>fid(2차도메인)</th>
            <td><input type="text" name="fid" value="<?= htmlspecialchars((string) ($row['fid'] ?? ''), ENT_QUOTES) ?>" placeholder="예: happy2025"></td>
        </tr>
        </tbody>
    </table>
</div>

<div class="form-actions">
    <button type="submit" class="primary">저장</button>
    <a href="<?= Admin_SITE ?>/Customer/view/<?= (int) $row['customer_idx'] ?>"><button type="button">취소</button></a>
</div>

</form>

<script src="<?= RS_JS ?>/common/upload.js?var=<?= FRESH_TIME ?>"></script>
