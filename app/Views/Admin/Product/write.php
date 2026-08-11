<div class="crumb">HOME &nbsp;›&nbsp; 상품관리 &nbsp;›&nbsp; <b>상품 등록</b></div>

<div class="page-head">
    <h1>상품 등록</h1>
</div>

<div class="panel">
    <div class="panel-head"><h2>상품 정보</h2></div>
    <form method="post" action="<?= Admin_SITE ?>/Product/write" style="padding:16px;">
        <table class="form-table">
            <tbody>
            <tr>
                <th>상품코드</th>
                <td><input type="text" name="product_code" required></td>
            </tr>
            <tr>
                <th>상품명</th>
                <td><input type="text" name="product_name" required></td>
            </tr>
            <tr>
                <th>가격(원)</th>
                <td><input type="number" name="price" min="0" value="0" required></td>
            </tr>
            <tr>
                <th>결제방식</th>
                <td>
                    <select name="billing_type">
                        <option value="MONTHLY">월구독</option>
                        <option value="ONCE">1회결제</option>
                        <option value="NONE">미결제(이벤트/무통장)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>이용기간(일)</th>
                <td><input type="number" name="period_day" min="1" value="30" required></td>
            </tr>
            <tr>
                <th>이벤트 상품</th>
                <td>
                    <select name="is_event">
                        <option value="0">일반</option>
                        <option value="1">이벤트(고객당 1회만 신청 가능)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>상태</th>
                <td>
                    <select name="status">
                        <option value="USE" selected>사용</option>
                        <option value="STOP">중지</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>정렬순서</th>
                <td><input type="number" name="sort" value="0"></td>
            </tr>
            <tr>
                <th>설명</th>
                <td><textarea name="description" rows="4" style="width:100%;"></textarea></td>
            </tr>
            </tbody>
        </table>

        <div class="form-actions">
            <button type="submit" class="primary">등록</button>
            <a href="<?= Admin_SITE ?>/Product"><button type="button">취소</button></a>
        </div>
    </form>
</div>
