<div class="crumb">HOME &nbsp;›&nbsp; 서버관리 &nbsp;›&nbsp; <b>서버 등록</b></div>

<div class="page-head">
    <h1>서버 등록</h1>
</div>

<div class="panel">
    <div class="panel-head"><h2>서버 정보</h2></div>
    <form method="post" action="<?= Admin_SITE ?>/ServerList/write" style="padding:16px;">
        <table class="form-table">
            <tbody>
            <tr>
                <th>서버코드</th>
                <td><input type="text" name="server_code" required></td>
            </tr>
            <tr>
                <th>서버명</th>
                <td><input type="text" name="server_name" required></td>
            </tr>
            <tr>
                <th>receiver URL</th>
                <td><input type="text" name="receiver_url" placeholder="https://demo1.bomul.ai.kr/receiver" style="width:100%;" required></td>
            </tr>
            <tr>
                <th>API 키</th>
                <td><input type="text" name="api_key" placeholder="비워두면 자동 발급" style="width:100%;"></td>
            </tr>
            <tr>
                <th>DB host</th>
                <td><input type="text" name="db_host"></td>
            </tr>
            <tr>
                <th>DB port</th>
                <td><input type="number" name="db_port" min="1" max="65535"></td>
            </tr>
            <tr>
                <th>설치가능 여부</th>
                <td>
                    <select name="installable_yn">
                        <option value="Y" selected>가능</option>
                        <option value="N">불가</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>상태</th>
                <td>
                    <select name="status">
                        <option value="USE" selected>정상</option>
                        <option value="STOP">중지</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>메모</th>
                <td><textarea name="memo" rows="3" style="width:100%;"></textarea></td>
            </tr>
            </tbody>
        </table>

        <div class="form-actions">
            <button type="submit" class="primary">등록</button>
            <a href="<?= Admin_SITE ?>/ServerList"><button type="button">취소</button></a>
        </div>
    </form>
</div>
