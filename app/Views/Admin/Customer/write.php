<div class="crumb">HOME &nbsp;›&nbsp; 고객관리 &nbsp;›&nbsp; <b>고객 등록</b></div>

<div class="page-head">
    <h1>고객 등록</h1>
</div>

<div class="panel">
    <div class="panel-head"><h2>고객 정보</h2></div>
    <form method="post" action="<?= Admin_SITE ?>/Customer/write" style="padding:16px;">
        <table class="form-table">
            <tbody>
            <tr>
                <th>아이디</th>
                <td><input type="text" name="customer_id" required></td>
            </tr>
            <tr>
                <th>비밀번호</th>
                <td><input type="password" name="customer_pw" required></td>
            </tr>
            <tr>
                <th>담당자명</th>
                <td><input type="text" name="customer_name" required></td>
            </tr>
            <tr>
                <th>중개사무소명</th>
                <td><input type="text" name="company_name" required></td>
            </tr>
            <tr>
                <th>사업자등록번호</th>
                <td><input type="text" name="biz_reg_no" required></td>
            </tr>
            <tr>
                <th>중개사무소등록번호</th>
                <td><input type="text" name="broker_reg_no" required></td>
            </tr>
            <tr>
                <th>휴대폰번호</th>
                <td><input type="text" name="phone" required></td>
            </tr>
            <tr>
                <th>이메일</th>
                <td><input type="text" name="email" required></td>
            </tr>
            </tbody>
        </table>

        <div class="form-actions">
            <button type="submit" class="primary">등록</button>
            <a href="<?= Admin_SITE ?>/Customer"><button type="button">취소</button></a>
        </div>
    </form>
</div>
