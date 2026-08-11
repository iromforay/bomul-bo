<div class="crumb">HOME &nbsp;›&nbsp; 서버관리 &nbsp;›&nbsp; <b>서버 수정</b></div>

<div class="page-head">
    <h1>서버 수정</h1>
</div>

<div class="panel">
    <div class="panel-head"><h2>서버 정보</h2></div>
    <form method="post" action="<?= Admin_SITE ?>/ServerList/modify/<?= (int) $row['server_no'] ?>" style="padding:16px;">
        <table class="form-table">
            <tbody>
            <tr>
                <th>서버코드</th>
                <td><input type="text" name="server_code" value="<?= htmlspecialchars($row['server_code'], ENT_QUOTES) ?>" required></td>
            </tr>
            <tr>
                <th>서버명</th>
                <td><input type="text" name="server_name" value="<?= htmlspecialchars($row['server_name'], ENT_QUOTES) ?>" required></td>
            </tr>
            <tr>
                <th>receiver URL</th>
                <td><input type="text" name="receiver_url" value="<?= htmlspecialchars($row['receiver_url'], ENT_QUOTES) ?>" style="width:100%;" required></td>
            </tr>
            <tr>
                <th>API 키</th>
                <td><input type="text" name="api_key" value="<?= htmlspecialchars($row['api_key'], ENT_QUOTES) ?>" style="width:100%;" required></td>
            </tr>
            <tr>
                <th>DB host</th>
                <td><input type="text" name="db_host" value="<?= htmlspecialchars($row['db_host'] ?? '', ENT_QUOTES) ?>"></td>
            </tr>
            <tr>
                <th>DB port</th>
                <td><input type="number" name="db_port" min="1" max="65535" value="<?= htmlspecialchars((string) ($row['db_port'] ?? ''), ENT_QUOTES) ?>"></td>
            </tr>
            <tr>
                <th>배정 테넌트수</th>
                <td><?= (int) $row['real_tenant_count'] ?> <span style="color:#6B7A88; font-size:12px;">(종료(TERMINATED) 아닌 service_live 실시간 집계 - 직접 수정 불가)</span></td>
            </tr>
            <tr>
                <th>설치가능 여부</th>
                <td>
                    <select name="installable_yn">
                        <option value="Y" <?= ($row['installable_yn'] ?? 'Y') === 'Y' ? 'selected' : '' ?>>가능</option>
                        <option value="N" <?= ($row['installable_yn'] ?? 'Y') === 'N' ? 'selected' : '' ?>>불가</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>상태</th>
                <td>
                    <select name="status">
                        <option value="USE" <?= $row['status'] === 'USE' ? 'selected' : '' ?>>정상</option>
                        <option value="STOP" <?= $row['status'] === 'STOP' ? 'selected' : '' ?>>중지</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>메모</th>
                <td><textarea name="memo" rows="3" style="width:100%;"><?= htmlspecialchars($row['memo'] ?? '', ENT_QUOTES) ?></textarea></td>
            </tr>
            </tbody>
        </table>

        <div class="form-actions">
            <button type="submit" class="primary">저장</button>
            <a href="<?= Admin_SITE ?>/ServerList"><button type="button">취소</button></a>
        </div>
    </form>
</div>
