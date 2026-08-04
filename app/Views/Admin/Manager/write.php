<div class="crumb">HOME &nbsp;›&nbsp; 관리자 &nbsp;›&nbsp; <b>관리자 등록</b></div>

<div class="page-head">
    <h1>관리자 등록</h1>
</div>

<div class="panel">
    <div class="panel-head"><h2>관리자 정보</h2></div>
    <form method="post" action="<?= Admin_SITE ?>/Manager/write" style="padding:16px;">
        <table class="form-table">
            <tbody>
            <tr>
                <th>아이디</th>
                <td><input type="text" name="manager_id" required></td>
            </tr>
            <tr>
                <th>비밀번호</th>
                <td><input type="password" name="manager_pw" required></td>
            </tr>
            <tr>
                <th>이름</th>
                <td><input type="text" name="manager_name" required></td>
            </tr>
            <tr>
                <th>사용여부</th>
                <td>
                    <select name="use_yn">
                        <option value="Y" selected>사용</option>
                        <option value="N">미사용</option>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="form-actions">
            <button type="submit" class="primary">등록</button>
            <a href="<?= Admin_SITE ?>/Manager"><button type="button">취소</button></a>
        </div>
    </form>
</div>
